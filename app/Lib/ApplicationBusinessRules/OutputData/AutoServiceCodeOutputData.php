<?php

namespace App\Lib\ApplicationBusinessRules\OutputData;

/**
 * 自動サービスコードの出力データのクラス。
 */
class AutoServiceCodeOutputData
{
    private array $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * 出力データにサービスコード情報を追加する。
     */
    public function addData(
        string $dateDailyRate,
        string $dateDailyRateOneMonthAgo,
        string $dateDailyRateTwoMonthAgo,
        string $dateDailyRateSchedule,
        string $facilityNameKanji,
        string $facilityNumber,
        int $serviceCountDate,
        string $serviceItemCode,
        int $serviceItemCodeId,
        string $serviceItemName,
        string $serviceTypeCode,
        string $targetDate,
        int $unitNumber
    ): void {
        $this->data[] = [
            'date_daily_rate' => $dateDailyRate,
            'date_daily_rate_one_month_ago' => $dateDailyRateOneMonthAgo,
            'date_daily_rate_two_month_ago' => $dateDailyRateTwoMonthAgo,
            'date_daily_rate_schedule' => $dateDailyRateSchedule,
            'facility_name_kanji' => $facilityNameKanji,
            'facility_number' => $facilityNumber,
            'service_count_date' => $serviceCountDate,
            'service_item_code' => $serviceItemCode,
            'service_item_code_id' => $serviceItemCodeId,
            'service_item_name' => $serviceItemName,
            'service_type_code' => $serviceTypeCode,
            'target_date' => $targetDate,
            'unit_number' => $unitNumber
        ];
    }

    /**
     * データを返す。
     */
    public function getData(): array
    {
        return $this->data;
    }
}
