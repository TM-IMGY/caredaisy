<?php

namespace App\Lib\DomainService;

use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\FacilityUserRegister;

/**
 * 個人情報の取り扱いなどセキュリティの仕様。
 */
class SecuritySpecification
{
    /**
     * アクセス可能な施設利用者かを返す。
     * @param int $facilityUserId 施設利用者のID。
     * @param FacilityUserRegister $facilityUserRegister 事業所の利用者の名簿。
     */
    public static function isAccessibleFacilityUer(
        int $facilityUserId,
        FacilityUserRegister $facilityUserRegister
    ): bool {
        return in_array($facilityUserId, $facilityUserRegister->getIds());
    }
}
