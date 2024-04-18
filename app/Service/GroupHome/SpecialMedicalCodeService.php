<?php

namespace App\Service\GroupHome;

use App\Models\SpecialMedicalCode;

/**
 * 特別診療コードに関するユースケースに責任を持つクラス。
 */
class SpecialMedicalCodeService
{
    /**
     * 特別診療コードを返す。
     * @param int $facilityId 事業所ID
     * @param string $serviceTypeCode サービス種類コード
     * @param int $year 年
     * @param int $month 月
     * @return array
     */
    public function get(int $facilityId, string $serviceTypeCode, int $year, int $month): array
    {
        $specialMedicalCodes = SpecialMedicalCode::getFacilityTargetYm($facilityId, $serviceTypeCode, $year, $month);
        return $specialMedicalCodes;
    }
}
