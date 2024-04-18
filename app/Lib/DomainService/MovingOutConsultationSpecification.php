<?php

namespace App\Lib\DomainService;

use App\Lib\DomainService\LegalPeriodSpecification;
use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUser;
use Carbon\Carbon;

/**
 * 退居時相談援助加算の仕様。
 * 似ている概念に初期加算と退院退所時相談加算があるが区別する。
 * 差別点として当該加算は1か月を30日(または31日)の固定ではなく法定期間で求める。
 */
class MovingOutConsultationSpecification
{
    public const ADDITIONAL = 2;

    /**
     * サービス項目コードを返す。
     * 本質的に種類によって項目は変動するため仕様として切り離している。
     */
    public static function getServiceItemCode(): string
    {
        return '6502';
    }

    /**
     * 利用可能かを返す。
     * @param CareRewardHistory $careRewardHistory 介護報酬履歴
     * @param FacilityUser $facilityUser 施設利用者
     * @param int $year 対象年
     * @param int $month 対象月
     */
    public static function isAvailable(
        CareRewardHistory $careRewardHistory,
        FacilityUser $facilityUser,
        int $year,
        int $month
    ): bool {
        // 施設利用者が対象年月に退去しているかを確保する変数。
        $isTargetYmEnd = false;

        // 1か月を超えて利用しているかを確保する変数。
        $isAfterOneMonth = false;

        // 施設利用者の退去日がある場合。
        if ($facilityUser->getEndDate() !== null) {
            // 施設利用者の入居日を取得する。
            $startDate = new Carbon($facilityUser->getStartDate());

            // 施設利用者の退去日を取得する。
            $endDate = new Carbon($facilityUser->getEndDate());

            // 施設利用者が対象年月に退去しているか。
            $targetDate = new Carbon("${year}-${month}-1");
            $isTargetYmEnd = $endDate->isSameMonth($targetDate, true);

            // 入居日の1か月後を取得する。
            $startDateAfterOneMonth = LegalPeriodSpecification::getAfteNMonth(
                1,
                $startDate->year,
                $startDate->month,
                $startDate->day
            );

            // 1か月を超えて利用しているか(以上ではなく超えるなので注意する)。
            $isAfterOneMonth = (new Carbon($startDateAfterOneMonth))->timestamp <= $endDate->timestamp;
        }

        // 下記の条件を全て満たす場合に取得できる。
        // 介護報酬履歴に退居時相談援助加算がある。
        // 施設利用者が対象年月に退去している。
        // 施設利用者の退去後の状況が居宅である。
        // 施設利用者が1か月を超えて利用している。
        return $careRewardHistory->isMovingOutConsultationAvailable()
            && $isTargetYmEnd
            && $facilityUser->getAfterOutStatus()->isResidence()
            && $isAfterOneMonth;
    }
}
