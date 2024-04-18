<?php

namespace App\Lib\Entity\FacilityUser;

use App\Lib\DomainService\StayOutSpecification;

/**
 * 施設利用者の外泊のクラス。
 */
class StayOut
{
    private ?string $endDate;
    private int $facilityUserId;
    private ?int $id;
    private ?int $mealOfTheDayEndDinner;
    private ?int $mealOfTheDayEndLunch;
    private ?int $mealOfTheDayEndMorning;
    private ?int $mealOfTheDayEndSnack;
    private ?int $mealOfTheDayStartDinner;
    private ?int $mealOfTheDayStartLunch;
    private ?int $mealOfTheDayStartMorning;
    private ?int $mealOfTheDayStartSnack;
    private string $startDate;
    private int $reasonForStayOut;
    private string $remarks;
    private string $remarksReasonForStayOut;

    public function __construct(
        ?string $endDate,
        int $facilityUserId,
        ?int $id,
        ?int $mealOfTheDayEndDinner,
        ?int $mealOfTheDayEndLunch,
        ?int $mealOfTheDayEndMorning,
        ?int $mealOfTheDayEndSnack,
        ?int $mealOfTheDayStartDinner,
        ?int $mealOfTheDayStartLunch,
        ?int $mealOfTheDayStartMorning,
        ?int $mealOfTheDayStartSnack,
        string $startDate,
        int $reasonForStayOut,
        string $remarks,
        string $remarksReasonForStayOut
    ) {
        $this->endDate = $endDate;
        $this->facilityUserId = $facilityUserId;
        $this->id = $id;
        // TODO: meal**系はデフォルトは0。
        $this->mealOfTheDayEndDinner = $mealOfTheDayEndDinner;
        $this->mealOfTheDayEndLunch = $mealOfTheDayEndLunch;
        $this->mealOfTheDayEndMorning = $mealOfTheDayEndMorning;
        $this->mealOfTheDayEndSnack = $mealOfTheDayEndSnack;
        $this->mealOfTheDayStartDinner = $mealOfTheDayStartDinner;
        $this->mealOfTheDayStartLunch = $mealOfTheDayStartLunch;
        $this->mealOfTheDayStartMorning = $mealOfTheDayStartMorning;
        $this->mealOfTheDayStartSnack = $mealOfTheDayStartSnack;
        $this->startDate = $startDate;
        $this->reasonForStayOut = $reasonForStayOut;
        $this->remarks = $remarks;
        $this->remarksReasonForStayOut = $remarksReasonForStayOut;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function getFacilityUserId(): int
    {
        return $this->facilityUserId;
    }

    public function getReasonForStayOut(): int
    {
        return $this->reasonForStayOut;
    }

    public function getRemarks(): string
    {
        return $this->remarks;
    }

    public function getRemarksReasonForStayOut(): string
    {
        return $this->remarksReasonForStayOut;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function hasEndDate(): bool
    {
        return $this->endDate !== null;
    }

    public function isFacility(): bool
    {
        return $this->reasonForStayOut === StayOutSpecification::REASON_FOR_STAY_OUT_FACILITY;
    }

    public function isGoOut(): bool
    {
        return $this->reasonForStayOut === StayOutSpecification::REASON_FOR_STAY_OUT_GO_OUT;
    }

    public function isHospitalization(): bool
    {
        return $this->reasonForStayOut === StayOutSpecification::REASON_FOR_STAY_OUT_HOSPITALIZATION;
    }

    public function isOthers(): bool
    {
        return $this->reasonForStayOut === StayOutSpecification::REASON_FOR_STAY_OUT_OTHERS;
    }

    public function isOvernightStay(): bool
    {
        return $this->reasonForStayOut === StayOutSpecification::REASON_FOR_STAY_OUT_OVERNIGHT_STAY;
    }
}
