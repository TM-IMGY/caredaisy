<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\FacilityUserBenefitRecord;

/**
 * 施設利用者の給付率のレコードのリポジトリのインターフェース。
 */
interface FacilityUserBenefitRecordRepositoryInterface
{
    /**
     * 施設利用者の給付率を返す。
     * @param int $facilityUserId 施設利用者のID。
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserBenefitRecord
     */
    public function find(int $facilityUserId, int $year, int $month): FacilityUserBenefitRecord;
}
