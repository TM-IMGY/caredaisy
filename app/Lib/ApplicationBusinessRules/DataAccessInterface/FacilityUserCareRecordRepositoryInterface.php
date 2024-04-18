<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\FacilityUserCareRecord;

/**
 * 施設利用者の介護情報のリポジトリのインターフェース。
 */
interface FacilityUserCareRecordRepositoryInterface
{
    /**
     * 施設利用者の介護情報の記録を返す。
     * @param int $facilityUserId 施設利用者のID。
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserCareRecord
     */
    public function find(int $facilityUserId, int $year, int $month): FacilityUserCareRecord;

    /**
     * 施設利用者を返す。
     * @param array $facilityUserIds 施設利用者のID。
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserCareRecord[]
     */
    public function get(array $facilityUserIds, int $year, int $month): array;
}
