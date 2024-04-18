<?php

namespace App\Lib\Entity;

/**
 * 施設利用者の給付率クラス。
 */
class FacilityUserBenefit
{
    private int $benefitInformationId;
    private int $benefitRate;
    private int $benefitType;
    private string $effectiveStartDate;
    private ?string $expiryDate;
    private int $facilityUserId;

    /**
     * コンストラクタ。
     * @param int $benefitInformationId;
     * @param int $benefitRate;
     * @param int $benefitType;
     * @param string $effectiveStartDate;
     * @param ?string $expiryDate;
     * @param int $facilityUserId;
     */
    public function __construct(
        int $benefitInformationId,
        int $benefitRate,
        int $benefitType,
        string $effectiveStartDate,
        ?string $expiryDate,
        int $facilityUserId
    ) {
        $this->benefitInformationId = $benefitInformationId;
        $this->benefitRate = $benefitRate;
        $this->benefitType = $benefitType;
        $this->effectiveStartDate = $effectiveStartDate;
        $this->expiryDate = $expiryDate;
        $this->facilityUserId = $facilityUserId;
    }

    /**
     * 給付率を返す。
     * @return string
     */
    public function getBenefitRate(): int
    {
        return $this->benefitRate;
    }

    /**
     * 開始日を返す。
     * @return string
     */
    public function getEffectiveStartDate(): string
    {
        return $this->effectiveStartDate;
    }
}
