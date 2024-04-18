<?php

namespace App\Lib\ApplicationBusinessRules\OutputData;

/**
 * 様式のサービス合計の出力データクラス。
 */
class GetFormTotalOutputData
{
    private array $data;

    /**
     * コンストラクタ
     * @param ?int $benefitRate
     * @param ?int $insuranceBenefit
     * @param ?int $partPayment
     * @param ?int $serviceUnitAmount
     * @param ?int $unitPrice
     * @param ?int $publicBenefitRate
     * @param ?int $publicExpenditureUnit
     * @param ?int $publicPayment
     * @param ?int $publicSpendingAmount
     */
    public function __construct(
        ?int $benefitRate,
        ?int $insuranceBenefit,
        ?int $partPayment,
        ?int $serviceUnitAmount,
        ?int $unitPrice,
        ?int $publicBenefitRate,
        ?int $publicExpenditureUnit,
        ?int $publicPayment,
        ?int $publicSpendingAmount
    ) {
        // 請求集計欄(保険分、公費分)
        $this->data = [
            'benefit_rate' => $benefitRate,
            'insurance_benefit' => $insuranceBenefit,
            'part_payment' => $partPayment,
            'service_unit_amount' => $serviceUnitAmount,
            'unit_price' => $unitPrice,
            'public_benefit_rate' => $publicBenefitRate,
            'public_expenditure_unit' => $publicExpenditureUnit,
            'public_payment' => $publicPayment,
            'public_spending_amount' => $publicSpendingAmount,
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
