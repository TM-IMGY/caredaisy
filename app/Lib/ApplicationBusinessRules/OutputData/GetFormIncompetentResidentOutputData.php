<?php

namespace App\Lib\ApplicationBusinessRules\OutputData;

/**
 * 様式取得の特定入所者介護サービス費出力データクラス。
 */
class GetFormIncompetentResidentOutputData
{
    private array $data;

    /**
     * コンストラクタ
     * @param ?int $burdenLimit
     * @param ?int $insuranceBenefit
     * @param ?int $partPayment
     * @param int $serviceCode
     * @param int $serviceCountDate
     * @param string $serviceItemName
     * @param ?int $totalCost
     * @param ?int $unitNumber
     * @param ?int $publicSpendingAmount
     * @param ?int $publicSpendingCount
     */
    public function __construct(
        ?int $burdenLimit,
        ?int $insuranceBenefit,
        ?int $partPayment,
        int $serviceCode,
        int $serviceCountDate,
        string $serviceItemName,
        ?int $totalCost,
        ?int $unitNumber,
        ?int $publicSpendingAmount,
        ?int $publicSpendingCount
    ) {
        // 特定入所者介護サービス費
        $this->data = [
            'service_item_name' => $serviceItemName,
            'service_code' => $serviceCode,
            'unit_number' => $unitNumber,
            'burden_limit' => $burdenLimit,
            'service_count_date' => $serviceCountDate,
            'total_cost' => $totalCost,
            'insurance_benefit' => $insuranceBenefit,
            'public_spending_count' => $publicSpendingCount,
            'public_spending_amount' => $publicSpendingAmount,
            'part_payment' => $partPayment,
        ];
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
