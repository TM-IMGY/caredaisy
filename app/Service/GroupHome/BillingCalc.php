<?php

namespace App\Service\GroupHome;

class BillingCalc
{
    /**
     * 請求対象年月を返す
     *
     * @param CarbonImmutable $systemDatetime
     */
    public static function getBillingTargetYM($systemDatetime): string
    {
        // 請求処理実行が月の10日以下であるか
        $isLessThan10Days = $systemDatetime->day <= 10;

        return $isLessThan10Days ? $systemDatetime->format('Ym') : $systemDatetime->addMonthNoOverflow()->format('Ym');
    }
}
