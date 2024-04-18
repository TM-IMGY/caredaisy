<?php

namespace App\Lib\ApplicationBusinessRules\OutputData;

/**
 * 様式取得の特別診療費合計の出力データクラス。
 */
class GetFormTotalSpecialMedicalOutputData
{
    private array $data;

    /**
     * コンストラクタ
     * @param ?int $benefitRate
     * @param ?int $insuranceBenefit
     * @param ?int $partPayment
     * @param ?int $serviceUnitAmount
     * @param ?int $publicBenefitRate
     * @param ?int $publicExpenditureUnit
     * @param ?int $publicPayment
     * @param ?int $publicSpendingAmount
     * @param ?int $publicSpendingUnitNumber
     */
    public function __construct(
        ?int $benefitRate,
        ?int $insuranceBenefit,
        ?int $partPayment,
        ?int $serviceUnitAmount,
        ?int $publicBenefitRate,
        ?int $publicExpenditureUnit,
        ?int $publicPayment,
        ?int $publicSpendingAmount,
        ?int $publicSpendingUnitNumber
    ) {
        // 請求集計欄(保険分特定治療・特別診療費、公費分特定治療・特別診療費)
        $this->data = [
            'service_unit_amount' => $serviceUnitAmount,
            'benefit_rate' => $benefitRate,
            'insurance_benefit' => $insuranceBenefit,
            'part_payment' => $partPayment,
            'public_spending_unit_number' => $publicSpendingUnitNumber,
            'public_benefit_rate' => $publicBenefitRate,
            'public_spending_amount' => $publicSpendingAmount,
            'public_payment' => $publicPayment,
            'public_expenditure_unit' => $publicExpenditureUnit,
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
