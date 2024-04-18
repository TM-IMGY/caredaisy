<?php

namespace App\Lib\ApplicationBusinessRules\OutputData;

/**
 * 公費の次回分の取得の出力データ。
 */
class PublicExpenseNextOutputData
{
    private array $data;

    /**
     * コンストラクタ
     * @param int $amountBornePerson 本人支払い額
     * @param string $bearerNumber 負担者番号
     * @param ?string $confirmationMedicalInsuranceDate 公費情報確認日
     * @param string $effectiveStartDate 有効開始日
     * @param ?string $expiryDate 有効終了日
     * @param string $legalName 公費略称
     * @param string $recipientNumber 受給者番号
     */
    public function __construct(
        int $amountBornePerson,
        string $bearerNumber,
        ?string $confirmationMedicalInsuranceDate,
        string $effectiveStartDate,
        ?string $expiryDate,
        string $legalName,
        string $recipientNumber
    ) {
        $this->data = [
            'amount_borne_person' => $amountBornePerson,
            'bearer_number' => $bearerNumber,
            'confirmation_medical_insurance_date' => $confirmationMedicalInsuranceDate,
            'effective_start_date' => $effectiveStartDate,
            'expiry_date' => $expiryDate,
            'legal_name' => $legalName,
            'recipient_number' => $recipientNumber
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
