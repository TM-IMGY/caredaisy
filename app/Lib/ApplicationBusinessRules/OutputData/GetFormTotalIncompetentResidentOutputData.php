<?php

namespace App\Lib\ApplicationBusinessRules\OutputData;

/**
 * 様式取得の特定入所者介護サービス費合計の出力データクラス。
 */
class GetFormTotalIncompetentResidentOutputData
{
    private array $data;

    /**
     * コンストラクタ
     * @param ?int $insuranceBenefit
     * @param ?int $partPayment
     * @param ?int $totalCost
     * @param ?int $publicPayment
     * @param ?int $publicSpendingAmount
     */
    public function __construct(
        ?int $insuranceBenefit,
        ?int $partPayment,
        ?int $totalCost,
        ?int $publicPayment,
        ?int $publicSpendingAmount
    ) {
        // 特定入所者介護サービス費(合計)
        $this->data = [
            'total_cost' => $totalCost,
            'public_spending_amount' => $publicSpendingAmount,
            'part_payment' => $partPayment,
            'insurance_benefit' => $insuranceBenefit,
            'public_payment' => $publicPayment,
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
