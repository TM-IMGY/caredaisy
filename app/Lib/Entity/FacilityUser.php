<?php

namespace App\Lib\Entity;

/**
 * 施設利用者。
 */
class FacilityUser
{
    private ?AfterOutStatus $afterOutStatus;
    private BeforeInstatus $beforeInStatus;
    private string $birthDay;
    private ?int $bloodType;
    private ?string $cellPhoneNumber;
    private ?string $consentDate;
    private ?string $consenter;
    private ?string $consenterPhoneNumber;
    private ?string $deathDate;
    private ?string $deathReason;
    private ?string $diagnosisDate;
    private ?string $diagnostician;
    private ?string $endDate;
    private int $facilityUserId;
    private string $firstName;
    private string $firstNameKana;
    /**
     * @var int 性別。 genderとなっているが生物学的な性別を扱う。
     */
    private int $gender;

    private InsuredNo $insuredNo;

    /**
     * @var string 保険者番号
     */
    private string $insurerNo;
    private int $invalidFlag;
    private string $lastName;
    private string $lastNameKana;
    private ?string $location1;
    private ?string $location2;
    private ?string $phoneNumber;
    private ?string $postalCode;
    private ?string $remarks;
    private ?int $rhType;
    private int $spacialAddressFlag;

    /**
     * @var string 入居日
     */
    private string $startDate;

    public function __construct(
        ?AfterOutStatus $afterOutStatus,
        BeforeInStatus $beforeInStatus,
        string $birthDay,
        ?int $bloodType,
        ?string $cellPhoneNumber,
        ?string $consentDate,
        ?string $consenter,
        ?string $consenterPhoneNumber,
        ?string $deathDate,
        ?string $deathReason,
        ?string $diagnosisDate,
        ?string $diagnostician,
        ?string $endDate,
        int $facilityUserId,
        string $firstName,
        string $firstNameKana,
        int $gender,
        InsuredNo $insuredNo,
        string $insurerNo,
        int $invalidFlag,
        string $lastName,
        string $lastNameKana,
        ?string $location1,
        ?string $location2,
        ?string $phoneNumber,
        ?string $postalCode,
        ?string $remarks,
        ?int $rhType,
        int $spacialAddressFlag,
        string $startDate
    ) {
        $this->afterOutStatus = $afterOutStatus;
        $this->beforeInStatus = $beforeInStatus;
        $this->birthDay = $birthDay;
        $this->bloodType = $bloodType;
        $this->cellPhoneNumber = $cellPhoneNumber;
        $this->consentDate = $consentDate;
        $this->consenter = $consenter;
        $this->consenterPhoneNumber = $consenterPhoneNumber;
        $this->deathDate = $deathDate;
        $this->deathReason = $deathReason;
        $this->diagnosisDate = $diagnosisDate;
        $this->diagnostician = $diagnostician;
        $this->endDate = $endDate;
        $this->facilityUserId = $facilityUserId;
        $this->firstName = $firstName;
        $this->firstNameKana = $firstNameKana;
        $this->gender = $gender;
        $this->insuredNo = $insuredNo;
        $this->insurerNo = $insurerNo;
        $this->invalidFlag = $invalidFlag;
        $this->lastName = $lastName;
        $this->lastNameKana = $lastNameKana;
        $this->location1 = $location1;
        $this->location2 = $location2;
        $this->phoneNumber = $phoneNumber;
        $this->postalCode = $postalCode;
        $this->remarks = $remarks;
        $this->rhType = $rhType;
        $this->spacialAddressFlag = $spacialAddressFlag;
        $this->startDate = $startDate;
    }

    /**
     * 退去後の状況を返す。
     */
    public function getAfterOutStatus(): ?AfterOutStatus
    {
        return $this->afterOutStatus;
    }

    /**
     * 入居前の状況を返す。
     * @return int
     */
    public function getBeforeInStatus(): int
    {
        return $this->beforeInStatus->getBeforeInStatus();
    }

    /**
     * 生年月日を返す。
     * @return string
     */
    public function getBirthDay(): string
    {
        return $this->birthDay;
    }

    public function getDeathDate(): ?string
    {
        return $this->deathDate;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    /**
     * 施設利用者IDを返す。
     * @return int
     */
    public function getFacilityUserId(): int
    {
        return $this->facilityUserId;
    }

    /**
     * 性別を返す。
     * @return string
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * 被保険者番号を返す。
     * @return InsuredNo
     */
    public function getInsuredNo(): InsuredNo
    {
        return $this->insuredNo;
    }

    /**
     * 保険者番号を返す。
     * @return string
     */
    public function getInsurerNo(): string
    {
        return $this->insurerNo;
    }

    /**
     * TODO: JsonSerializable インターフェース利用の方がいいか?
     */
    public function getData(): array
    {
        $afterOutStatus = $this->afterOutStatus === null ? null : $this->afterOutStatus->getAfterOutStatus();
        $beforeInStatus = $this->beforeInStatus === null ? null : $this->beforeInStatus->getBeforeInStatus();
        return [
            'after_out_status' => $afterOutStatus,
            'before_in_status' => $beforeInStatus,
            'birth_day' => $this->birthDay,
            'blood_type' => $this->bloodType,
            'cell_phone_number' => $this->cellPhoneNumber,
            'consent_date' => $this->consentDate,
            'consenter' => $this->consenter,
            'consenter_phone_number' => $this->consenterPhoneNumber,
            'death_date' => $this->deathDate,
            'death_reason' => $this->deathReason,
            'diagnosis_date' => $this->diagnosisDate,
            'diagnostician' => $this->diagnostician,
            'end_date' => $this->endDate,
            'facility_user_id' => $this->facilityUserId,
            'first_name' => $this->firstName,
            'first_name_kana' => $this->firstNameKana,
            'gender' => $this->gender,
            'insured_no' => $this->insuredNo->getValue(),
            'insurer_no' => $this->insurerNo,
            'invalid_flag' => $this->invalidFlag,
            'last_name' => $this->lastName,
            'last_name_kana' => $this->lastNameKana,
            'location1' => $this->location1,
            'location2' => $this->location2,
            'phone_number' => $this->phoneNumber,
            'postal_code' => $this->postalCode,
            'remarks' => $this->remarks,
            'rh_type' => $this->rhType,
            'spacial_address_flag' => $this->spacialAddressFlag,
            'start_date' => $this->startDate
        ];
    }

    /**
     * @return ?string
     */
    public function getConsentDate(): ?string
    {
        return $this->consentDate;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @return bool
     */
    public function hasConsentDate(): bool
    {
        return $this->consentDate !== null;
    }

    public function hasDeathDate(): bool
    {
        return $this->deathDate !== null;
    }

    public function hasEndDate(): bool
    {
        return $this->endDate !== null;
    }

    /**
     * 入居前の状況が介護老人保健施設かを返す。
     * @return bool
     */
    public function isCareHospital(): bool
    {
        return ($this->getBeforeInStatus() === 4);
    }

    /**
     * 入居前の状況が介護医療院かを返す。
     * @return bool
     */
    public function isHealthCareFacilities(): bool
    {
        return ($this->getBeforeInStatus() === 9);
    }

    /**
     * 入居前の状況が医療機関かを返す。
     * @return bool
     */
    public function isHospital(): bool
    {
        return ($this->getBeforeInStatus() === 2);
    }

    /**
     * TODO: 保険適用外というニュアンスで使われることが多いが65歳未満 = 保険適用外なのかの定義が一時的なものなので注意する。
     * @return bool
     */
    public function isUnder65(): bool
    {
        // 65歳未満は被保険者番号の先頭1文字がH。
        // TODO: 生年月日から判断した方が適切。
        return mb_substr($this->insuredNo->getValue(), 0, 1) === 'H';
    }
}
