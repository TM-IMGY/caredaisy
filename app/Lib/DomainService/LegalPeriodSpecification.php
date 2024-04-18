<?php

namespace App\Lib\DomainService;

use Carbon\Carbon;

/**
 * 法定期間の仕様。
 * 民法第138条～第143条を参照のこと。
 */
class LegalPeriodSpecification
{
    /**
     * nか月後の日付を返す。
     * @param int n nか月。
     * @param int $year 対象年
     * @param int $month 対象月
     * @param int $day 対象日
     * @return string yyyy-mm-dd
     */
    public static function getAfteNMonth(
        int $n,
        int $year,
        int $month,
        int $day
    ): string {
        // 対象の日付を作成する。
        $targetDate = new Carbon("${year}-${month}-${day}");

        // 対象の日付のnか月後の月の日数。
        $nMonthsLaterDays = $targetDate->copy()->addMonthsNoOverflow($n)->daysInMonth;

        // 対象の日付のnか月後を確保する変数。
        $nMonthsLater = null;

        // 対象日が月の途中で、かつnか月後に応当日のある場合。
        if ($targetDate->day <= $nMonthsLaterDays) {
            // nか月後の応当日になる。
            $nMonthsLater = $targetDate->copy()->addMonthsNoOverflow($n);
        // 対象日が月の途中で、かつnか月後に応当日のない場合。
        } else {
            // n + 1 か月後の月初になる。
            $nMonthsLater = $targetDate->copy()->addMonthsNoOverflow($n + 1)->startOfMonth();
        }

        return $nMonthsLater;
    }
}
