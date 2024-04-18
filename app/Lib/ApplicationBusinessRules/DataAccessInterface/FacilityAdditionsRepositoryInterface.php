<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\FacilityAdditions;

/**
 * 事業所加算の集まりのリポジトリのインターフェース。
 * 事業所加算を単体ではなく、ある程度のまとまりとして扱う想定が増えたため作成した。
 */
interface FacilityAdditionsRepositoryInterface
{
    /**
     * 事業所加算の集まりを返す。
     * @param int $facilityId 事業所ID
     * @param int $serviceTypeCodeId サービス種類コードID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityAdditions
     */
    public function getByFacilityId(int $facilityId, int $serviceTypeCodeId, int $year, int $month): FacilityAdditions;
}
