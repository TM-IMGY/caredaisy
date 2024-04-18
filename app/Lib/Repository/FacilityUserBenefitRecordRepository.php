<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserBenefitRecordRepositoryInterface;
use App\Lib\Entity\FacilityUserBenefit;
use App\Lib\Entity\FacilityUserBenefitRecord;
use Carbon\CarbonImmutable;
use DB;

/**
 * 施設利用者の給付率のレコードのリポジトリクラス。
 */
class FacilityUserBenefitRecordRepository implements FacilityUserBenefitRecordRepositoryInterface
{
    /**
     * 施設利用者の給付率を返す。
     * @param int $facilityUserId 施設利用者のID。
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserBenefitRecord
     */
    public function find(int $facilityUserId, int $year, int $month): FacilityUserBenefitRecord
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $records = DB::table('i_user_benefit_informations')
            ->where('facility_user_id', $facilityUserId)
            ->whereDate('effective_start_date', '<=', $targetMonthEndDate)
            ->whereDate('expiry_date', '>=', $targetMonthStartDate)
            ->orderBy('effective_start_date', 'asc')
            ->get();

        // 施設利用者の給付率を全て取得する。
        $facilityUserBenefits = [];
        foreach ($records as $record) {
            $facilityUserBenefit = new FacilityUserBenefit(
                $record->benefit_information_id,
                $record->benefit_rate,
                $record->benefit_type,
                $record->effective_start_date,
                $record->expiry_date,
                $record->facility_user_id
            );
            $facilityUserBenefits[] = $facilityUserBenefit;
        }

        $facilityUserBenefitRecord = new FacilityUserBenefitRecord($facilityUserId, $facilityUserBenefits);

        return $facilityUserBenefitRecord;
    }
}
