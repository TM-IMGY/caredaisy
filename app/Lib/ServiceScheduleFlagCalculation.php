<?php

namespace App\Lib;

use App\Lib\DomainService\ServiceItemCodeSpecification;
use App\Models\ServiceCode;
use Carbon\CarbonImmutable;

/**
 * サービス予定フラグの計算についての知識を集積したクラス。
 * プロジェクト構造のチームの最終的なアサインが取れていないためApp\Lib直下に配されることになっている。
 */
class ServiceScheduleFlagCalculation
{
    /**
     * 施設利用者の対象年月のサービス予定フラグを計算して返す。
     * @param int $serviceItemCodeId
     * @param string $startDate 入居日
     * @param int $year
     * @param int $month
     * @return string
     */
    public static function getTargetYm(int $serviceItemCodeId, string $startDate, int $year, int $month) : string
    {
        $targetStartDate = new CarbonImmutable("${year}-${month}-1");
        $targetDaysInMonth = $targetStartDate->daysInMonth;

        // 施設利用者が対象年月中に入居していれば確保する。
        $targetMonthStartDay = null;
        $startDatetime = new CarbonImmutable($startDate);
        if ($startDatetime->year == $targetStartDate->year && $startDatetime->month == $targetStartDate->month) {
            $targetMonthStartDay = $startDatetime->day;
        }

        // 予定フラグを作成する。
        $flg = str_repeat('1', $targetDaysInMonth);

        // 月ごとに提供するサービスコードの場合は、月初に予定フラグを立てる。
        if (in_array($serviceItemCodeId, ServiceItemCodeSpecification::SCHEDULED_PER_MONTH_IDS)) {
            $flg = '1'.str_repeat('0', $targetDaysInMonth - 1);
        }
        // 予定を立てないサービスコードの場合
        elseif (in_array($serviceItemCodeId, ServiceItemCodeSpecification::UNSCHEDULED_IDS)) {
            $flg = str_repeat('0', $targetDaysInMonth);
        }
        // 看取りの場合
        elseif (in_array($serviceItemCodeId, ServiceItemCodeSpecification::END_OF_LIFE_CARE_IDS)) {
            $flg = str_repeat('0', $targetDaysInMonth);
        }
        // 認知症対応型入院時費用の場合
        elseif (in_array($serviceItemCodeId, ServiceItemCodeSpecification::HOSPITALIZATION_IDS)) {
            $flg = str_repeat('0', $targetDaysInMonth);
        }
        // それ以外の場合は入居日で予定フラグの削除を行う。
        else {
            // 対象年月中に入居していれば、入居日より前の日付の予定フラグを消す。
            if ($targetMonthStartDay != null) {
                $flg = substr_replace(
                    $flg,
                    str_repeat('0', $targetMonthStartDay - 1),
                    0,
                    $targetMonthStartDay - 1
                );
            }
        }

        return $flg;
    }
}
