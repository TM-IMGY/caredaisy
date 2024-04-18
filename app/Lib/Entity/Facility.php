<?php

namespace App\Lib\Entity;

/**
 * 事業所。
 */
class Facility
{
    private ?string $abbreviation;
    /**
     * @var int 伝送対象か。秘匿情報なので注意する。
     */
    private int $allowTransmission;
    private int $area;
    private int $facilityId;
    private ?string $facilityManager;
    private string $facilityNameKana;
    /**
     * @var string 事業所名(漢字)。できればKanjiは止めたいがテーブルのカラム名と統一することを優先する。
     */
    private string $facilityNameKanji;
    private string $facilityNumber;
    private ?string $faxNumber;
    private string $insurerNo;
    private int $invalidFlag;
    private int $institutionId;
    private string $location;
    private string $phoneNumber;
    private string $postalCode;
    private ?string $remarks;
    /**
     * @var FacilityService[] 事業所が提供しているサービス。
     */
    private array $services;

    /**
     * @param ?string $abbreviation
     * @param int $allowTransmission
     * @param int $area
     * @param int $facilityId
     * @param ?string $facilityManager
     * @param string $facilityNameKana
     * @param string $facilityNameKanji
     * @param string $facilityNumber
     * @param ?string $faxNumber
     * @param string $insurerNo
     * @param int $invalidFlag
     * @param int $institutionId
     * @param string $location
     * @param string $phoneNumber
     * @param string $postalCode
     * @param ?string $remarks
     * @param FacilityService[] $services 事業所が提供するサービス。
     */
    public function __construct(
        ?string $abbreviation,
        int $allowTransmission,
        int $area,
        int $facilityId,
        ?string $facilityManager,
        string $facilityNameKana,
        string $facilityNameKanji,
        string $facilityNumber,
        ?string $faxNumber,
        string $insurerNo,
        int $invalidFlag,
        int $institutionId,
        string $location,
        string $phoneNumber,
        string $postalCode,
        ?string $remarks,
        array $services
    ) {
        $this->abbreviation = $abbreviation;
        $this->allowTransmission = $allowTransmission;
        $this->area = $area;
        $this->facilityId = $facilityId;
        $this->facilityManager = $facilityManager;
        $this->facilityNameKana = $facilityNameKana;
        $this->facilityNameKanji = $facilityNameKanji;
        $this->facilityNumber = $facilityNumber;
        $this->faxNumber = $faxNumber;
        $this->insurerNo = $insurerNo;
        $this->invalidFlag = $invalidFlag;
        $this->institutionId = $institutionId;
        $this->location = $location;
        $this->phoneNumber = $phoneNumber;
        $this->postalCode = $postalCode;
        $this->remarks = $remarks;
        $this->services = $services;
    }

    /**
     * サービスを返す。
     * @return FacilityService
     */
    public function findService(int $serviceId): FacilityService
    {
        $find = null;
        foreach ($this->services as $service) {
            if ($service->getServiceId() === $serviceId) {
                $find = $service;
                break;
            }
        }
        return $find;
    }

    /**
     * 事業所IDを返す。
     * @return int
     */
    public function getFacilityId(): int
    {
        return $this->facilityId;
    }

    /**
     * 事業所名(漢字)を返す。
     * @return string
     */
    public function getFacilityNameKanji(): string
    {
        return $this->facilityNameKanji;
    }

    /**
     * 事業所番号を返す。
     * @return string
     */
    public function getFacilityNumber(): string
    {
        return $this->facilityNumber;
    }

    /**
     * サービス種類コードIDを全て返す。
     * @return int[]
     */
    public function getServiceTypeCodeIds(): array
    {
        $serviceTypeCodeIds = array_map(
            function ($service) {
                return $service->getServiceTypeCodeId();
            },
            $this->services
        );

        return $serviceTypeCodeIds;
    }

    /**
     * @param string $serviceTypeCode サービス種類コード。
     * @return bool
     */
    public function hasServiceTypeCode(string $serviceTypeCode): bool
    {
        $serviceTypeCodes = array_map(
            function ($service) {
                return $service->getServiceTypeCode();
            },
            $this->services
        );

        return in_array($serviceTypeCode, $serviceTypeCodes);
    }
}
