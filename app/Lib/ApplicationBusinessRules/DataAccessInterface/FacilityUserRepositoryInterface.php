<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\FacilityUser;

/**
 * 施設利用者のリポジトリのインターフェース。
 */
interface FacilityUserRepositoryInterface
{
    /**
     * 施設利用者を返す。
     * @param int $facilityUserId 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ?FacilityUser
     */
    public function find(int $facilityUserId, int $year, int $month): ?FacilityUser;

    /**
     * 施設利用者を返す。
     * @param array $facilityUserIds 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUser[]
     */
    public function get(array $facilityUserIds, int $year, int $month): ?array;
}
