<?php

namespace Tests\Unit\Invoice;

use App\Service\GroupHome\BillingCalc;
use PHPUnit\Framework\TestCase;

use Carbon\CarbonImmutable;

class BillingCalcTest extends TestCase
{

    /**
     * 伝送請求の処理対象年月取得処理のテスト
     *
     * @dataProvider dataProvider
     */
    public function testGetBillingTargetYM($processDate, $resultDate): void
    {
        $judgeDate = CarbonImmutable::parse($resultDate);

        $billingTargetDate = BillingCalc::getBillingTargetYM(CarbonImmutable::parse($processDate));
        $targetDate = CarbonImmutable::create(mb_substr($billingTargetDate, 0, 4), mb_substr($billingTargetDate, 4))->startOfMonth();

        $this->assertEquals($targetDate, $judgeDate);
    }

    public function dataProvider(): array
    {
        // 実行日が2022年10月10日の場合に処理対象年月が2022年10月になることを確認
        $data['実行日が2022年10月10日']['process_date'] = '2022-10-10';
        $data['実行日が2022年10月10日']['result_date'] = '2022-10-01';

        // 実行日が2022年10月11日の場合に処理対象年月が2022年11月になることを確認
        $data['実行日が2022年10月11日']['process_date'] = '2022-10-11';
        $data['実行日が2022年10月11日']['result_date'] = '2022-11-01';

        // 実行日が2022年10月30日の場合に処理対象年月が2022年11月になることを確認
        $data['実行日が2022年10月30日']['process_date'] = '2022-10-30';
        $data['実行日が2022年10月30日']['result_date'] = '2022-11-01';

        // 実行日が2022年10月31日の場合に処理対象年月が2022年11月になることを確認
        $data['実行日が2022年10月31日']['process_date'] = '2022-10-31';
        $data['実行日が2022年10月31日']['result_date'] = '2022-11-01';

        // 実行日が2022年11月1日の場合に処理対象年月が2022年11月になることを確認
        $data['実行日が2022年11月1日']['process_date'] = '2022-11-01';
        $data['実行日が2022年11月1日']['result_date'] = '2022-11-01';

        // 実行日が2024年(うるう年)1月28日の場合に処理対象年月が2024年2月になることを確認
        $data['実行日が2024年1月28日']['process_date'] = '2024-01-28';
        $data['実行日が2024年1月28日']['result_date'] = '2024-02-01';

        // 実行日が2024年(うるう年)1月29日の場合に処理対象年月が2024年2月になることを確認
        $data['実行日が2024年1月29日']['process_date'] = '2024-01-29';
        $data['実行日が2024年1月29日']['result_date'] = '2024-02-01';

        // 実行日が2024年(うるう年)1月30日の場合に処理対象年月が2024年2月になることを確認
        $data['実行日が2024年1月30日']['process_date'] = '2024-01-30';
        $data['実行日が2024年1月30日']['result_date'] = '2024-02-01';

        return $data;
    }
}
