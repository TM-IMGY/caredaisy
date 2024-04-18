<?php

namespace App\Lib\Repository\FacilityUser;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUser\StayOutRecordRepositoryInterface;
use App\Lib\Entity\FacilityUser\StayOut;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use Carbon\CarbonImmutable;
use DB;

/**
 * 施設利用者の外泊の記録のリポジトリクラス。
 */
class StayOutRecordRepository implements StayOutRecordRepositoryInterface
{
    /**
     * 施設利用者の外泊の記録を返す。
     * @param int $facilityUserId 施設利用者のID
     * @return StayOutRecord
     */
    public function find(int $facilityUserId): StayOutRecord
    {
        $query = <<< SQL
SELECT
    `end_date`,
    `facility_user_id`,
    `id`,
    `meal_of_the_day_end_dinner`,
    `meal_of_the_day_end_lunch`,
    `meal_of_the_day_end_morning`,
    `meal_of_the_day_end_snack`,
    `meal_of_the_day_start_dinner`,
    `meal_of_the_day_start_lunch`,
    `meal_of_the_day_start_morning`,
    `meal_of_the_day_start_snack`,
    `start_date`,
    `reason_for_stay_out`,
    `remarks`,
    `remarks_reason_for_stay_out`
FROM
  `i_stay_out_managements`
WHERE
  `facility_user_id` = ?
ORDER BY
  `start_date` ASC
SQL;

        $records = DB::select($query, [$facilityUserId]);

        // 施設利用者の外泊を全て取得する。
        $stayOuts = [];
        foreach ($records as $record) {
            $stayout = new StayOut(
                $record->end_date,
                $record->facility_user_id,
                $record->id,
                $record->meal_of_the_day_end_dinner,
                $record->meal_of_the_day_end_lunch,
                $record->meal_of_the_day_end_morning,
                $record->meal_of_the_day_end_snack,
                $record->meal_of_the_day_start_dinner,
                $record->meal_of_the_day_start_lunch,
                $record->meal_of_the_day_start_morning,
                $record->meal_of_the_day_start_snack,
                $record->start_date,
                $record->reason_for_stay_out,
                $record->remarks,
                $record->remarks_reason_for_stay_out
            );
            $stayOuts[] = $stayout;
        }

        $stayoutRecord = new StayOutRecord($stayOuts);

        return $stayoutRecord;
    }
}
