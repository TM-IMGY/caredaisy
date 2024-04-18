<?php

namespace App\Lib;

use Carbon\Carbon;
use DB;

/**
 * 施設利用者の外泊の知識を集約したクラス。
 */
class FacilityUserStayOut
{
    /**
     * 外泊情報から施設利用者の対象年月の外泊日を全て返す。
     * 外泊の終了日については秒までを扱わないため一律0秒として処理をする。
     * TODO: 配列ではなくクラスで渡したい。
     * @param array facilityUserStayOuts 複数の施設利用者の外泊情報。全て対象年月中の情報であること。
     * @param string $moveOutDateString 施設利用者の退去日。end_dateだと被るため。
     * @param int $year 対象年
     * @param int $month 対象月
     * @return array
     */
    public static function listTargetYmDays($facilityUserStayOuts, $moveOutDateString, $year, $month): array
    {
        // 施設利用者の対象年月の外泊日を格納するための変数。
        $days = [];
        // 対象年月の開始日と終了日を取得する。
        $targetMonthStartDate = new Carbon("${year}-${month}-1");
        $targetMonthEndDate = $targetMonthStartDate->copy()->endOfMonth();

        for ($i = 0, $cnt = count($facilityUserStayOuts); $i < $cnt; $i++) {
            // 開始日が対象年月の開始日より前の場合に丸める。
            $startDate = new Carbon($facilityUserStayOuts[$i]['start_date']);
            if ($startDate->lt($targetMonthStartDate)) {
                $startDate = $targetMonthStartDate->copy();
            }
            // 開始日に1秒でも施設にいる場合、翌日に置き換える(外泊日として扱わない)。
            elseif ($startDate->gt($startDate->copy()->hour(0)->minute(0)->seconds(0))) {
                $startDate->addDay()->hour(0)->minute(0)->seconds(0);
            }

            // 終了日がnullの場合は対象年月の末日に丸める。
            $endDateString = $facilityUserStayOuts[$i]['end_date'];
            $tmpEndDate = new Carbon($endDateString);
            $endDate = null;
            if ($endDateString === null) {
                $endDate = $targetMonthEndDate->copy()->seconds(0);
            // 終了日が対象年月より後の場合は対象年月の末日に丸める。
            } elseif ($tmpEndDate->gt($targetMonthEndDate)) {
                $endDate = $targetMonthEndDate->copy()->seconds(0);
            // 終了日に1秒でも施設にいる場合、前日に置き換える(外泊日として扱わない)。
            } elseif ($tmpEndDate->lt($tmpEndDate->copy()->hour(23)->minute(59)->seconds(0))) {
                $endDate = $tmpEndDate->copy()->subDay()->hour(23)->minute(59)->seconds(0);
            } else {
                $endDate = $tmpEndDate->copy();
            }

            // 退去日が存在し、対象年月内の場合は取得する。
            $tmpMoveOutDate = new Carbon($moveOutDateString);
            $moveOutDate = null;
            if ($moveOutDateString && $tmpMoveOutDate->isSameMonth($targetMonthStartDate, true)) {
                $moveOutDate = $tmpMoveOutDate->copy()->hour(23)->minute(59)->seconds(0);
            }

            // 対象年月内の退居日が存在し、かつ退去日より終了日が後の場合に退去日に丸める。
            if ($moveOutDate && $moveOutDate->lt($endDate)) {
                $endDate = $moveOutDate->copy();
            }

            // 開始日と終了日の調整によって開始日が終了日を超えてしまった場合はスキップする。
            if ($startDate->gt($endDate)) {
                continue;
            }

            $days = array_merge($days, range($startDate->day, $endDate->day));
        }

        // ユニーク化する(古いデータによっては重複登録を警戒する必要があるため)。
        $days = array_values(array_unique($days));

        return $days;
    }
}
