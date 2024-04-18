<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserIndependenceRepositoryInterface;
use App\Lib\Entity\FacilityUserIndependence;
use Carbon\CarbonImmutable;
use DB;

/**
 * 施設利用者の自立度のリポジトリクラス。
 */
class FacilityUserIndependenceRepository implements FacilityUserIndependenceRepositoryInterface
{
    /**
     * 施設利用者の対象年月の自立度を返す。
     * 対象年月に複数の自立度が存在する場合は最新を返す。
     * @param int $facilityUserId 施設利用者ID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ?FacilityUserIndependence
     */
    public function find(int $facilityUserId, int $year, int $month): ?FacilityUserIndependence
    {
        $targetYmStartDate = "${year}-${month}-1";
        $targetYmEndDate = (new CarbonImmutable($targetYmStartDate))->endOfMonth()->format('Y-m-d');

        $record = DB::table('i_user_independence_informations')
            ->where('facility_user_id', $facilityUserId)
            ->whereDate('judgment_date', '<=', $targetYmEndDate)
            ->orderBy('judgment_date', 'desc')
            ->first();
        
        if ($record === null) {
            return null;
        }

        $facilityUserIndependence = new FacilityUserIndependence(
            $record->dementia_level,
            $record->facility_user_id,
            $record->independence_level,
            $record->judger,
            $record->judgment_date,
            $record->user_independence_informations_id,
        );

        return $facilityUserIndependence;
    }
}
