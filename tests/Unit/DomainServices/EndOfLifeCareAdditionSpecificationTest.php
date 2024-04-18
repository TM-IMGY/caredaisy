<?php

namespace Tests\Unit\DomainServices;

use App\Lib\DomainService\EndOfLifeCareAdditionSpecification;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\Factory\ResultFlagFactory;
use App\Lib\MockRepository\ServiceItemCodesMockRepository;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Tests\Factory\TestFacilityUserFactory;
use Tests\Factory\TestCareRewardHistoryFactory;

/**
 * 看取りの仕様のテスト。
 */
class EndOfLifeCareAdditionSpecificationTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            // テストの目的: 実際のデータと同じ計算をするか。
            'case_1' => [
                // 同意日
                '2021/08/17',
                // 看取り日
                '2021/09/18',
                // 実績フラグ
                [
                    // 加算Ⅰ: 8月17日から8月19日に実績が立つ。
                    new ResultFlag('0000000000000000000000000000000', '0000000000000000111000000000000', '0000000000000000000000000000000', 3),
                    // 加算Ⅱ: 8月20日から9月15日に実績が立つ。
                    new ResultFlag('1111111111111110000000000000000', '0000000000000000000111111111111', '0000000000000000000000000000000', 27),
                    // 加算Ⅲ: 9月16日から9月17日に実績が立つ。
                    new ResultFlag('0000000000000001100000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 2),
                    // 加算Ⅳ: 9月18日に実績が立つ。
                    new ResultFlag('0000000000000000010000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 1),
                ],
                // 対象年
                2021,
                // 対象月
                9
            ],
            // テストの目的: 加算Ⅰの境界テスト(ギリギリ実績が立つ)。
            'case_2' => [
                '2021/08/19',
                '2021/09/18',
                [
                    // 加算Ⅰ: 8月19日に実績が立つ。
                    new ResultFlag('0000000000000000000000000000000', '0000000000000000001000000000000', '0000000000000000000000000000000', 1),
                    // 加算Ⅱ: 8月20日から9月15日に実績が立つ。
                    new ResultFlag('1111111111111110000000000000000', '0000000000000000000111111111111', '0000000000000000000000000000000', 27),
                    // 加算Ⅲ: 9月16日から9月17日に実績が立つ。
                    new ResultFlag('0000000000000001100000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 2),
                    // 加算Ⅳ: 9月18日に実績が立つ。
                    new ResultFlag('0000000000000000010000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 1),
                ],
                2021,
                9
            ],
            // テストの目的: 加算Ⅰの境界テスト(ギリギリ取得できない)。
            'case_3' => [
                '2021/08/20',
                '2021/09/18',
                [
                    // 加算Ⅱ: 8月20日から9月15日に実績が立つ。
                    new ResultFlag('1111111111111110000000000000000', '0000000000000000000111111111111', '0000000000000000000000000000000', 27),
                    // 加算Ⅲ: 9月16日から9月17日に実績が立つ。
                    new ResultFlag('0000000000000001100000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 2),
                    // 加算Ⅳ: 9月18日に実績が立つ。
                    new ResultFlag('0000000000000000010000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 1),
                ],
                2021,
                9
            ],
            // テストの目的: 加算Ⅱの境界テスト(ギリギリ実績が立つ)。
            'case_4' => [
                '2021/09/15',
                '2021/09/18',
                [
                    // 加算Ⅱ: 9月15日に実績が立つ。
                    new ResultFlag('0000000000000010000000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 1),
                    // 加算Ⅲ: 9月16日から9月17日に実績が立つ。
                    new ResultFlag('0000000000000001100000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 2),
                    // 加算Ⅳ: 9月18日に実績が立つ。
                    new ResultFlag('0000000000000000010000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 1),
                ],
                2021,
                9
            ],
            // テストの目的: 加算Ⅱの境界テスト(ギリギリ取得できない)。
            'case_5' => [
                '2021/09/16',
                '2021/09/18',
                [
                    // 加算Ⅲ: 9月16日から9月17日に実績が立つ。
                    new ResultFlag('0000000000000001100000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 2),
                    // 加算Ⅳ: 9月18日に実績が立つ。
                    new ResultFlag('0000000000000000010000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 1),
                ],
                2021,
                9
            ],
            // テストの目的: 加算Ⅲの境界テスト(ギリギリ実績が立つ)。
            'case_6' => [
                '2021/09/17',
                '2021/09/18',
                [
                    // 加算Ⅲ: 9月17日に実績が立つ。
                    new ResultFlag('0000000000000000100000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 1),
                    // 加算Ⅳ: 9月18日に実績が立つ。
                    new ResultFlag('0000000000000000010000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 1),
                ],
                2021,
                9
            ],
            // テストの目的: 加算Ⅲの境界テスト(ギリギリ取得できない)。
            'case_7' => [
                '2021/09/18',
                '2021/09/18',
                [
                    // 加算Ⅳ: 9月18日に実績が立つ。
                    new ResultFlag('0000000000000000010000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 1),
                ],
                2021,
                9
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $consentDate 同意日
     * @param string $deathDate 看取り日
     * @param ResultFlag[] $resultFlags 実績フラグ
     * @param int $year 対象年
     * @param int $month 対象月
     */
    public function testSpecification(
        string $consentDate,
        string $deathDate,
        array $resultFlags,
        int $year,
        int $month
    ): void {
        // 施設利用者の看取りのテストデータを作成する。
        $facilityUser = (new TestFacilityUserFactory())->generateEndOfLife(
            $consentDate,
            $deathDate,
            (new Carbon("${year}-${month}-1"))->lastOfMonth()->format('Y-m-d'),
            (new Carbon("${year}-${month}-1"))->format('Y-m-d'),
        );

        // 看取り介護加算ありの介護報酬履歴を作成する。
        $careRewardHistory = (new TestCareRewardHistoryFactory())->generateEndOfLife(
            (new Carbon("${year}-${month}-1"))->format('Y-m-d'),
            (new Carbon("${year}-${month}-1"))->lastOfMonth()->format('Y-m-d'),
            EndOfLifeCareAdditionSpecification::ADDITIONAL
        );

        // 利用可能かの判定が正しいかテストする。
        $isAvailable = EndOfLifeCareAdditionSpecification::isAvailable($careRewardHistory, $facilityUser, $year, $month);
        $this->assertEquals(count($resultFlags) > 0, $isAvailable);

        // 看取り介護加算があるサービス種類コード全てを取得する。
        $typeCodes = EndOfLifeCareAdditionSpecification::SERVICE_TYPE_CODES;

        // サービス項目コードリポジトリを取得する。
        $itemCodesRepository = new ServiceItemCodesMockRepository();

        // サービス種類コードごとに処理をする。
        foreach ($typeCodes as $typeCodeIndex => $typeCode) {
            // サービス種類が持つ看取り介護加算のサービス項目コード全てを取得する。
            $itemCodes = $itemCodesRepository->getByServiceItemCodes(
                $typeCode,
                EndOfLifeCareAdditionSpecification::getServiceItemCodes($careRewardHistory, $facilityUser, $typeCode),
                $year,
                $month
            );
            $all = $itemCodes->getAll();

            // サービス項目コードごとに処理をする。
            foreach ($all as $itemCodeIndex => $itemCode) {
                // 実績フラグを取得する。
                $resultFlagObject = $resultFlags[$itemCodeIndex];

                // テスト対象の実績フラグを取得する。
                $targetResultFlagObject = EndOfLifeCareAdditionSpecification::getResultFlag(
                    $facilityUser,
                    $itemCode,
                    new StayOutRecord([]),
                    $year,
                    $month
                );
    
                // 日割対象日 が正しいかテストする。
                $this->assertEquals($resultFlagObject->getDateDailyRate(), $targetResultFlagObject->getDateDailyRate());
                // 日割対象日(前月) が正しいかテストする。
                $this->assertEquals($resultFlagObject->getDateDailyRateOneMonthAgo(), $targetResultFlagObject->getDateDailyRateOneMonthAgo());
                // 日割対象日(前々月) が正しいかテストする。
                $this->assertEquals($resultFlagObject->getDateDailyRateTwoMonthAgo(), $targetResultFlagObject->getDateDailyRateTwoMonthAgo());
                // 回数／日数 が正しいかテストする。
                $this->assertEquals($resultFlagObject->getServiceCountDate(), $targetResultFlagObject->getServiceCountDate());
            }
        }
    }
}
