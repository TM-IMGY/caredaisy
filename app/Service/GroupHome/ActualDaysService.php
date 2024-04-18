<?php

namespace App\Service\GroupHome;

use App\Models\StayOutManagement;
use App\Service\GroupHome\StayOutService;
use Carbon\CarbonImmutable;
use Carbon\Carbon;

class ActualDaysService
{
    /**
     * 入所（院）実日数を返す
     * @param array params
     * @param string params['death_date'] => 'yyyymmdd'
     * @param string params['end_date'] => 'yyyymmdd'
     * @param string params['facility_user_id']
     * @param string params['start_date'] => 'yyyymmdd'
     * @param string params['target_ym'] => 'yyyymm'
     */
    public function get($params) : array
    {
        $targetDate = new CarbonImmutable($params['target_ym'].'01');
        $targetDaysInMonth = $targetDate->daysInMonth;

        // 看取り
        $nursingDays = [];
        if (array_key_exists('death_date', $params) && $params['death_date']) {
            $deathDate = new CarbonImmutable($params['death_date']);
            if ($targetDate->year == $deathDate->year && $targetDate->month == $deathDate->month) {
                $deathDay = $deathDate->day;
                // 死亡日翌日以降が対象になるので1足す
                for ($i = $deathDay + 1; $i <= $targetDaysInMonth; $i++) {
                    $nursingDays[] = $i;
                }
            }
        }

        // 退去日以降の日々
        $endDays = [];
        if (array_key_exists('end_date', $params) && $params['end_date']) {
            $endDate = new CarbonImmutable($params['end_date']);
            if ($targetDate->year == $endDate->year && $targetDate->month == $endDate->month) {
                $endDay = $endDate->day;
                // 退去日翌日以降が対象になるので1足す
                for ($i = $endDay + 1; $i <= $targetDaysInMonth; $i++) {
                    $endDays[] = $i;
                }
            }
        }

        // 外泊
        $stayOutDays = [];
        if (array_key_exists('facility_user_id', $params)) {
            $stayoOutService = new StayOutService();
            $stayOutParams = [
                'facility_user_id' => $params['facility_user_id'],
                'first_of_month' => $targetDate->format('Y-m-d H:i:s'),
                'last_of_month' => $targetDate->endOfMonth()->format('Y-m-d H:i:s')
            ];
            $stayOutDays = $stayoOutService->getStayoutDays($stayOutParams);
        }

        // 月途中入居
        $firstOfMonth = new CarbonImmutable($targetDate->format('Y-m-d'));
        $startDate = new CarbonImmutable($params['start_date']);

        $middleOfMonth = 0;
        if ($firstOfMonth->isSameMonth($startDate)) {
            $middleOfMonth = $firstOfMonth->diffInDays($startDate);
        }

        // 看取り、退去日以降の日々、外泊などで除外される日々
        $omitDays = array_unique(array_merge($nursingDays, $endDays, $stayOutDays));
        $omitDayCnt = count($omitDays);
        $actualDayCnt = $targetDaysInMonth - $omitDayCnt - $middleOfMonth;

        return [
            'nursing_days' => $nursingDays,
            'end_days' => $endDays,
            'stay_out_days' => $stayOutDays,
            'actual_day_cnt' => $actualDayCnt,
        ];
    }
}
