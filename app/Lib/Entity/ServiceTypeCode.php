<?php

namespace App\Lib\Entity;

/**
 * サービス種類コードクラス。
 */
class ServiceTypeCode
{
    private ?int $areaUnitPrice1;
    private ?int $areaUnitPrice2;
    private ?int $areaUnitPrice3;
    private ?int $areaUnitPrice4;
    private ?int $areaUnitPrice5;
    private ?int $areaUnitPrice6;
    private ?int $areaUnitPrice7;
    private ?int $areaUnitPrice8;
    private ?int $areaUnitPrice9;
    private ?int $areaUnitPrice10;
    private ?string $serviceEndDate;
    private string $serviceStartDate;
    private string $serviceTypeCode;
    private int $serviceTypeCodeId;
    private string $serviceTypeName;

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
      * @param ?string $serviceEndDate
      * @param string $serviceStartDate
      * @param string $serviceTypeCode
      * @param int $serviceTypeCodeId
      * @param string $serviceTypeName
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
        ?string $serviceEndDate,
        string $serviceStartDate,
        string $serviceTypeCode,
        int $serviceTypeCodeId,
        string $serviceTypeName
    ) {
        $this->areaUnitPrice1 = $areaUnitPrice1;
        $this->areaUnitPrice2 = $areaUnitPrice2;
        $this->areaUnitPrice3 = $areaUnitPrice3;
        $this->areaUnitPrice4 = $areaUnitPrice4;
        $this->areaUnitPrice5 = $areaUnitPrice5;
        $this->areaUnitPrice6 = $areaUnitPrice6;
        $this->areaUnitPrice7 = $areaUnitPrice7;
        $this->areaUnitPrice8 = $areaUnitPrice8;
        $this->areaUnitPrice9 = $areaUnitPrice9;
        $this->areaUnitPrice10 = $areaUnitPrice10;
        $this->serviceEndDate = $serviceEndDate;
        $this->serviceStartDate = $serviceStartDate;
        $this->serviceTypeCode = $serviceTypeCode;
        $this->serviceTypeCodeId = $serviceTypeCodeId;
        $this->serviceTypeName = $serviceTypeName;
    }

    /**
     *
     * @return ?int
     */
    public function getAreaUnitPrice1(): ?int
    {
        return $this->areaUnitPrice1;
    }

    /**
     *
     * @return ?int
     */
    public function getAreaUnitPrice2(): ?int
    {
        return $this->areaUnitPrice2;
    }

    /**
     *
     * @return ?int
     */
    public function getAreaUnitPrice3(): ?int
    {
        return $this->areaUnitPrice3;
    }

    /**
     *
     * @return ?int
     */
    public function getAreaUnitPrice4(): ?int
    {
        return $this->areaUnitPrice4;
    }

    /**
     *
     * @return ?int
     */
    public function getAreaUnitPrice5(): ?int
    {
        return $this->areaUnitPrice5;
    }

    /**
     *
     * @return ?int
     */
    public function getAreaUnitPrice6(): ?int
    {
        return $this->areaUnitPrice6;
    }

    /**
     *
     * @return ?int
     */
    public function getAreaUnitPrice7(): ?int
    {
        return $this->areaUnitPrice7;
    }

    /**
     *
     * @return ?int
     */
    public function getAreaUnitPrice8(): ?int
    {
        return $this->areaUnitPrice8;
    }

    /**
     *
     * @return ?int
     */
    public function getAreaUnitPrice9(): ?int
    {
        return $this->areaUnitPrice9;
    }

    /**
     *
     * @return ?int
     */
    public function getAreaUnitPrice10(): ?int
    {
        return $this->areaUnitPrice10;
    }

    /**
     * サービス種類コードを返す。
     * @return string
     */
    public function getServiceTypeCode(): string
    {
        return $this->serviceTypeCode;
    }

    /**
     * サービス種類コードIDを返す。
     * @return int
     */
    public function getServiceTypeCodeId(): int
    {
        return $this->serviceTypeCodeId;
    }

    /**
     * 認知症対応型共同生活介護かを返す。
     * @return bool
     */
    public function isDementiaCompatibleCommunalLivingCare(): bool
    {
        return $this->serviceTypeCode === '32';
    }

    /**
     * 特定施設入居者生活介護かを返す。
     * @return bool
     */
    public function isCareForSpecifiedFacilityResidents(): bool
    {
        return $this->serviceTypeCode === '33';
    }
}
