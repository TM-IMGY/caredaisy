<?php

namespace App\Lib\Entity;

/**
 * 施設利用者のサービスクラス。
 */
class FacilityUserService
{
    private ServiceTypeCode $serviceTypeCode;

    private int $facilityId;
    private int $facilityUserId;
    private int $serviceId;
    private int $usageSituation;
    private ?string $useEnd;
    private int $userFacilityServiceInformationId;
    private string $useStart;

    /**
      * @param ?int $areaUnitPrice1
      * @param ?int $areaUnitPrice2
      * @param ?int $areaUnitPrice3
      * @param ?int $areaUnitPrice4
      * @param ?int $areaUnitPrice5
      * @param ?int $areaUnitPrice6
      * @param ?int $areaUnitPrice7
      * @param ?int $areaUnitPrice8
      * @param ?int $areaUnitPrice9
      * @param ?int $areaUnitPrice10
      * @param int $facilityId
      * @param int $facilityUserId
      * @param ?string $serviceEndDate
      * @param int $serviceId
      * @param string $serviceStartDate
      * @param string $serviceTypeCode
      * @param int $serviceTypeCodeId
      * @param string $serviceTypeName
      * @param int $usageSituation
      * @param ?string $useEnd
      * @param int $userFacilityServiceInformationId
      * @param string $useStart
     */
    public function __construct(
        ?int $areaUnitPrice1,
        ?int $areaUnitPrice2,
        ?int $areaUnitPrice3,
        ?int $areaUnitPrice4,
        ?int $areaUnitPrice5,
        ?int $areaUnitPrice6,
        ?int $areaUnitPrice7,
        ?int $areaUnitPrice8,
        ?int $areaUnitPrice9,
        ?int $areaUnitPrice10,
        int $facilityId,
        int $facilityUserId,
        ?string $serviceEndDate,
        int $serviceId,
        string $serviceStartDate,
        string $serviceTypeCode,
        int $serviceTypeCodeId,
        string $serviceTypeName,
        int $usageSituation,
        ?string $useEnd,
        int $userFacilityServiceInformationId,
        string $useStart
    ) {
        $this->facilityId = $facilityId;
        $this->facilityUserId = $facilityUserId;
        $this->serviceId = $serviceId;
        $this->usageSituation = $usageSituation;
        $this->useEnd = $useEnd;
        $this->userFacilityServiceInformationId = $userFacilityServiceInformationId;
        $this->useStart = $useStart;

        $this->serviceTypeCode = new ServiceTypeCode(
            $areaUnitPrice1,
            $areaUnitPrice2,
            $areaUnitPrice3,
            $areaUnitPrice4,
            $areaUnitPrice5,
            $areaUnitPrice6,
            $areaUnitPrice7,
            $areaUnitPrice8,
            $areaUnitPrice9,
            $areaUnitPrice10,
            $serviceEndDate,
            $serviceStartDate,
            $serviceTypeCode,
            $serviceTypeCodeId,
            $serviceTypeName
        );
    }

    /**
     * サービスIDを返す。
     * @return string
     */
    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    /**
     * サービス種類コードを返す。
     * @return ServiceTypeCode
     */
    public function getServiceTypeCode(): ServiceTypeCode
    {
        return $this->serviceTypeCode;
    }

    /**
     * 終了日を返す。
     * @return string
     */
    public function getUseEnd(): ?string
    {
        return $this->useEnd;
    }

    /**
     * 開始日を返す。
     * @return string
     */
    public function getUseStart(): string
    {
        return $this->useStart;
    }

    /**
     * 利用中かを返す。
     * @return bool
     */
    public function isInUse(): bool
    {
        return $this->usageSituation === 1;
    }

    /**
     * 介護医療院サービス(種類55)であるかを返す。
     * @return bool
     */
    public function isHospital(): bool
    {
        return $this->serviceTypeCode->getServiceTypeCode() === '55';
    }

    /**
     * 特定施設入居者生活介護(種類33)であるかを返す。
     * @return bool
     */
    public function isSpecialFacility(): bool
    {
        return $this->serviceTypeCode->getServiceTypeCode() === '33';
    }

    /**
     * 地域密着型特定施設入居者生活介護(種類36)であるかを返す。
     * @return bool
     */
    public function isCommunityBasedFacility(): bool
    {
        return $this->serviceTypeCode->getServiceTypeCode() === '36';
    }
}
