<?php

namespace App\Lib\Entity;

/**
 * 事業所加算クラス。
 */
class FacilityAddition
{
    private ?string $additionEndDate;
    private string $additionStartDate;
    private int $facilityAdditionId;
    private int $facilityId;
    private ServiceItemCode $serviceItemCode;
    private int $serviceTypeCodeId;

    /**
     * @param ?string $additionEndDate
     * @param string $additionStartDate
     * @param int $facilityAdditionId
     * @param int $facilityId
     * @param ServiceItemCode $serviceItemCode
     * @param int $serviceTypeCodeId
     */
    public function __construct(
        ?string $additionEndDate,
        string $additionStartDate,
        int $facilityAdditionId,
        int $facilityId,
        ServiceItemCode $serviceItemCode,
        int $serviceTypeCodeId
    ) {
        $this->additionEndDate = $additionEndDate;
        $this->additionStartDate = $additionStartDate;
        $this->facilityAdditionId = $facilityAdditionId;
        $this->facilityId = $facilityId;
        $this->serviceItemCode = $serviceItemCode;
        $this->serviceTypeCodeId = $serviceTypeCodeId;
    }

    /**
     * @return ServiceItemCode
     */
    public function getServiceItemCode(): ServiceItemCode
    {
        return $this->serviceItemCode;
    }
}
