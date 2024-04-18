<?php

namespace App\Lib\ApplicationBusinessRules\OutputData;

/**
 * 様式取得の特別診療費出力データクラス。
 */
class GetFormSpecialMedicalOutputData
{
    private array $data;

    /**
     * コンストラクタ
     * @param int $detailId
     * @param int $identificationNum
     * @param string $name
     * @param int $serviceCountDate
     * @param int $specialMedicalCodeId
     * @param string $specialMedicalName
     * @param ?int $serviceUnitAmount
     * @param ?int $unitNumber
     * @param ?int $publicExpenditureUnit
     * @param ?int $publicSpendingCount
     */
    public function __construct(
        int $detailId,
        int $identificationNum,
        string $name,
        int $serviceCountDate,
        int $specialMedicalCodeId,
        string $specialMedicalName,
        ?int $serviceUnitAmount,
        ?int $unitNumber,
        ?int $publicExpenditureUnit,
        ?int $publicSpendingCount
    ) {
        // 特別診療費
        $this->data = [
            'special_medical_code_id' => $specialMedicalCodeId,
            'name' => $name,
            'identification_num' => $identificationNum,
            'special_medical_name' => $specialMedicalName,
            'unit_number' => $unitNumber,
            'service_count_date' =>$serviceCountDate,
            'service_unit_amount' => $serviceUnitAmount,
            'public_spending_count' => $publicSpendingCount,
            'public_expenditure_unit' => $publicExpenditureUnit,
            'detail_id' => $detailId
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
