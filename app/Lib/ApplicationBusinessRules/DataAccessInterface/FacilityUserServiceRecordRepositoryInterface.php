<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\FacilityUserServiceRecord;

/**
 * 施設利用者のサービス種類のリポジトリのインターフェース。
 */
interface FacilityUserServiceRecordRepositoryInterface
{
    /**
     * 施設利用者のサービスを返す。
     * @param int $facilityUserId 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserServiceRecord
     */
    public function find(int $facilityUserId, int $year, int $month): FacilityUserServiceRecord;

    /**
     * 施設利用者の対象年月の最新かつ利用中のサービスを返す。
     * @param array $facilityUserIds 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserServiceRecord[]
     */
    public function get(array $facilityUserIds, int $year, int $month): array;
}
