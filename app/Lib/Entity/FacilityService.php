<?php

namespace App\Lib\Entity;

/**
 * 事業所のサービス。
 */
class FacilityService
{
    private int $area;
    private string $changeDate;
    private int $facilityId;
    private int $firstPlanInput;
    private int $serviceId;
    private ServiceTypeCode $serviceTypeCode;

    /**
      * @param int $area
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
      * @param string $changeDate
      * @param int $facilityId
      * @param int $firstPlanInput
      * @param int $serviceId
      * @param ?string $serviceEndDate
      * @param string $serviceStartDate
      * @param string $serviceTypeCode
      * @param int $serviceTypeCodeId
      * @param string $serviceTypeName
     */
    public function __construct(
        int $area,
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
        string $changeDate,
        int $facilityId,
        int $firstPlanInput,
        int $serviceId,
        ?string $serviceEndDate,
        string $serviceStartDate,
        string $serviceTypeCode,
        int $serviceTypeCodeId,
        string $serviceTypeName
    ) {
        $this->area = $area;
        $this->changeDate = $changeDate;
        $this->facilityId = $facilityId;
        $this->firstPlanInput = $firstPlanInput;
        $this->serviceId = $serviceId;
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
     * @return ?int
     */
    public function getAreaUnitPrice(): ?int
    {
        // NOTE: 定数で持とうか悩んだが却って見にくかった。
        switch ($this->area) {
            case 1:
                return $this->serviceTypeCode->getAreaUnitPrice1();
            case 2:
                return $this->serviceTypeCode->getAreaUnitPrice2();
            case 3:
                return $this->serviceTypeCode->getAreaUnitPrice3();
            case 4:
                return $this->serviceTypeCode->getAreaUnitPrice4();
            case 5:
                return $this->serviceTypeCode->getAreaUnitPrice5();
            case 6:
                return $this->serviceTypeCode->getAreaUnitPrice6();
            case 7:
                return $this->serviceTypeCode->getAreaUnitPrice7();
            case 8:
                return $this->serviceTypeCode->getAreaUnitPrice8();
            case 9:
                return $this->serviceTypeCode->getAreaUnitPrice9();
            case 10:
                return $this->serviceTypeCode->getAreaUnitPrice10();
            default:
                return null;
        }
    }

    /**
     * サービスIDを返す。
     * @return int
     */
    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    /**
     * サービス種類コードを返す。
     * @return string
     */
    public function getServiceTypeCode(): string
    {
        return $this->serviceTypeCode->getServiceTypeCode();
    }

    /**
     * サービス種類コードIDを返す。
     * @return int
     */
    public function getServiceTypeCodeId(): int
    {
        return $this->serviceTypeCode->getServiceTypeCodeId();
    }
}
