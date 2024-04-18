<?php

namespace App\Lib\DomainService;

use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\ServiceItemCode;
use Carbon\Carbon;

/**
 * 予定フラグの仕様のクラス。
 */
class ScheduleFlagSpecification
{
    /**
     * サービスコードの予定フラグを返す。
     * 実績フラグと違い予定フラグは31文字丁度にならない仕様になっている。
     */
    public static function getByTargetYm(
        FacilityUser $facilityUser,
        ServiceItemCode $serviceItemCode,
        int $year,
        int $month
    ): string {
        $startDate = new Carbon($facilityUser->getStartDate());
        $targetYmStartDate = new Carbon("${year}-${month}-1");
        $targetYmLastDay = $targetYmStartDate->daysInMonth;

        // 予定の日割対象日を作成する。
        $flag = str_repeat('1', $targetYmLastDay);
        if ($serviceItemCode->isScheduledPerMonth()) {
            $flag = '1' . str_repeat('0', $targetYmLastDay - 1);
        } elseif ($serviceItemCode->isUnScheduled()) {
            $flag = str_repeat('0', $targetYmLastDay);
        // 看取りと入院時費用だけケアデイジーの仕様として予定フラグを表示しないようになっている。
        } elseif ($serviceItemCode->isEndOfLifeCare() || $serviceItemCode->isHospitalization()) {
            $flag = str_repeat('0', $targetYmLastDay);
        } elseif ($startDate->isSameMonth($targetYmStartDate, true)) {
            $flag = substr_replace(
                $flag,
                str_repeat('0', $startDate->day - 1),
                0,
                $startDate->day - 1
            );
        }

        return $flag;
    }
}
