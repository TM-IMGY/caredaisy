<?php

namespace App\Lib\Entity;

/**
 * 施設利用者の介護情報。
 */
class FacilityUserCare
{
    private CareLevel $careLevel;
    private ?string $carePeriodEnd;
    private ?string $carePeriodStart;
    private int $certificationStatus;
    private ?string $dateConfirmationInsuranceCard;
    private ?string $dateQualification;
    private int $facilityUserId;
    private ?string $recognitionDate;
    private int $userCareInfoId;

    /**
     * コンストラクタ
     * @param CareLevel $careLevel
     * @param ?string $carePeriodEnd
     * @param ?string $carePeriodStart
     * @param int $certificationStatus
     * @param ?string $dateConfirmationInsuranceCard
     * @param ?string $dateQualification
     * @param int $facilityUserId
     * @param ?string $recognitionDate
     * @param int $userCareInfoId
     */
    public function __construct(
        CareLevel $careLevel,
        ?string $carePeriodEnd,
        ?string $carePeriodStart,
        int $certificationStatus,
        ?string $dateConfirmationInsuranceCard,
        ?string $dateQualification,
        int $facilityUserId,
        ?string $recognitionDate,
        int $userCareInfoId
    ) {
        $this->careLevel = $careLevel;
        $this->carePeriodEnd = $carePeriodEnd;
        $this->carePeriodStart = $carePeriodStart;
        $this->certificationStatus = $certificationStatus;
        $this->dateConfirmationInsuranceCard = $dateConfirmationInsuranceCard;
        $this->dateQualification = $dateQualification;
        $this->facilityUserId = $facilityUserId;
        $this->recognitionDate = $recognitionDate;
        $this->userCareInfoId = $userCareInfoId;
    }

    /**
     * 介護度を返す。
     * @return CareLevel
     */
    public function getCareLevel(): CareLevel
    {
        return $this->careLevel;
    }

    /**
     * 終了日を返す。
     * @return ?string
     */
    public function getCarePeriodEnd(): ?string
    {
        return $this->carePeriodEnd;
    }

    /**
     * 開始日を返す。
     * @return ?string
     */
    public function getCarePeriodStart(): ?string
    {
        return $this->carePeriodStart;
    }
}
