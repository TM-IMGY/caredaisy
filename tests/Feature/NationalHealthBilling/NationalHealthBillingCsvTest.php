<?php

namespace Tests\Feature\NationalHealthBilling;

use App\User;
use App\Service\GroupHome\BillingCalc;
use Carbon\CarbonImmutable;
use Exception;
use SplFileObject;
use Tests\TestCase;

/**
 * 国保連請求CSVの出力のテスト。
 */
class NationalHealthBillingCsvTest extends TestCase
{
    public const FACILITY_ID = 1;
    public const FACILITY_USER_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];
    public const YEAR = 2021;
    public const MONTH = 9;

    // /**
    //  * データプロバイダ。
    //  * 一部の正解データについては内容が膨大だったため外部にcsvファイルとして切り離している。
    //  * (database/test_result_correct_data/national_health_billing.csv)
    //  * @return array
    //  */
    // public function billingDataProvider(): array
    // {
    // }

    /**
     * 請求のテスト。
     * ここでいう請求は下記として定義する。
     * 施設利用者の取得可能なサービスコードを取得し、それをサービス実績情報として登録し、それを承認し、国保連請求csvを出力するまでの一連の流れ。
     * そのため給付費明細と給付額請求の内容確認も含んでいる。
     * TODO: テスト対象の各処理の中でトランザクション処理をはっているために、このテストの終了時にロールバックすることができなくなっている。
     */
    public function testBilling()
    {
        // テストユーザーが認証する。
        $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();
        $this->actingAs($user);

        // 請求の対象事業所を取得する。
        $facilityId = self::FACILITY_ID;
        // 請求の対象施設利用者を全て取得する。
        $facilityUserIds = self::FACILITY_USER_IDS;
        // 請求の対象年月を取得する。
        $year = self::YEAR;
        $month = self::MONTH;

        // 対象年月の対象施設利用者全員のサービス実績情報を登録する。
        for ($i = 0, $cnt = count($facilityUserIds); $i < $cnt; $i++) {
            $facilityUserId = $facilityUserIds[$i];

            // 対象施設利用者が取得可能なサービスコードを全て取得する。
            $urlAutoServiceCode = '/group_home/service/auto_service_code/get?'
                .http_build_query([
                    'facility_id' => $facilityId,
                    'facility_user_id' => $facilityUserId,
                    'year' => $year,
                    'month' => $month
                ]);
            $responseAutoServiceCode = $this->get($urlAutoServiceCode);
            $responseAutoServiceCode->assertStatus(200);
            $serviceCodes = $responseAutoServiceCode->original;

            // 取得したサービスコードからサービス実績情報を登録する。
            $urlServiceResultSave = '/group_home/service/service_result/save';
            $responseServiceResultSave = $this->post($urlServiceResultSave, [
                'facility_id' => $facilityId,
                'facility_user_id' => $facilityUserId,
                'month' => $month,
                'year' => $year,
                'service_results' => $serviceCodes
            ]);
            $responseServiceResultSave->assertStatus(200);

            // サービス実績情報を承認する。
            $urlNationalHealthAgreement = '/group_home/service/national_health_billing/agreement/update';
            $this->post($urlNationalHealthAgreement, [
                    'facility_user_id' => $facilityUserId,
                    'flag' => 1,
                    'year' => $year,
                    'month' => $month
            ]);
        }

        // 国保請求csvファイルの出力をリクエストする。
        $url = '/group_home/service/national_health/download_csv/facility_users?'
            .http_build_query([
                'facility_user_ids' => $facilityUserIds,
                'year' => $year,
                'month' => $month
            ]);
        $response = $this->get($url);

        // リクエストした結果のレスポンスを確認する。
        $monthZeroPadding = sprintf('%02d', $month);
        $correctFileName = "CD${year}${monthZeroPadding}.csv";
        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename="' . $correctFileName . '"');

        // レスポンスの中身(csvデータ)を取得する。
        $csv = $response->original;
        $csvRecords = explode("\r\n", $csv);

        // レスポンスの中身のcsvに期待する正解データを取得する。
        $correctCsvfile = new SplFileObject('database/test_result_correct_data/national_health_billing.csv');
        // csvとして解釈すると二重引用符が自動で削除されてしまうので無効にした。
        // $correctCsvfile->setFlags(SplFileObject::READ_CSV);
        $csvRecordsCorrect = [];
        foreach ($correctCsvfile as $line) {
            // 行の改行コードを削除する。
            $fixLine = preg_replace("/\n|\r|\r\n/", "", $line);
            $csvRecordsCorrect[] = explode(',', $fixLine);
        }

        // csvを照合する。
        // 行の数が一致するかを確認する。
        $recordCnt = count($csvRecords);
        $recordCntCorrect = count($csvRecordsCorrect);
        $this->assertTrue($recordCnt === $recordCntCorrect);
        $billingTargetYm = BillingCalc::getBillingTargetYM(CarbonImmutable::parse('now'));
        for ($recordIndex = 0; $recordIndex < $recordCnt; $recordIndex++) {
            $values = explode(',', $csvRecords[$recordIndex]);
            $valuesCorrect = $csvRecordsCorrect[$recordIndex];

            // 列の数が一致するかを確認する。
            $valueCnt = count($values);
            $valueCntCorrect = count($valuesCorrect);
            $this->assertTrue($valueCnt === $valueCntCorrect);

            for ($valueIndex = 0; $valueIndex < $valueCnt; $valueIndex++) {
                $rowNumber = $recordIndex + 1;
                $columnNumber = $valueIndex + 1;

                // 処理対象年月はシステム時刻が基準になるので正解が動的に変化する。
                if ($rowNumber === 1 && $columnNumber === 11) {
                    $valuesCorrect[$valueIndex] = '"'.$billingTargetYm.'"';
                }

                if ($values[$valueIndex] !== $valuesCorrect[$valueIndex]) {
                    $incorrectValue = $values[$valueIndex] == '' ? 'blank' : $values[$valueIndex];
                    $correctValue = $valuesCorrect[$valueIndex] == '' ? 'blank' : $valuesCorrect[$valueIndex];
                    $message = "行${rowNumber}、列${columnNumber}が誤りです。誤りは${incorrectValue}で、正解は${correctValue}です。";
                    // TODO: アサーションではテスト失敗の場所が分かりにくかったのでエラーを投げた。かえって都合悪ければ修正する。
                    throw new Exception($message);
                }
            }
        }
    }
}
