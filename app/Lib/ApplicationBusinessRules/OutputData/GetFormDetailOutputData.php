<?php

namespace App\Lib\ApplicationBusinessRules\OutputData;

/**
 * 様式のサービス明細の出力データクラス。
 */
class GetFormDetailOutputData
{
    private array $data;

    /**
     * コンストラクタ
     * @param string $serviceCode
     * @param int $serviceCountDate
     * @param int $serviceItemCodeId
     * @param string $serviceItemName
     * @param ?int $serviceUnitAmount
     * @param ?int $unitNumber
     * @param ?int $publicExpenditureUnit
     * @param ?int $publicSpendingCount
     * @param ?int $publicSpendingUnitNumber
     */
    public function __construct(
        string $serviceCode,
        int $serviceCountDate,
        int $serviceItemCodeId,
        string $serviceItemName,
        ?int $serviceUnitAmount,
        ?int $unitNumber,
        ?int $publicExpenditureUnit,
        ?int $publicSpendingCount,
        ?int $publicSpendingUnitNumber
    ) {
        // 給付費明細欄
        $this->data = [
            'service_code' => $serviceCode,
            'service_count_date' => $serviceCountDate,
            'service_item_code_id' => $serviceItemCodeId,
            'service_item_name' => $serviceItemName,
            'service_unit_amount' => $serviceUnitAmount,
            'unit_number' => $unitNumber,
            'public_expenditure_unit' => $publicExpenditureUnit,
            'public_spending_count' => $publicSpendingCount,
            'public_spending_unit_number' => $publicSpendingUnitNumber
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
