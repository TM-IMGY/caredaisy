<?php

namespace App\Lib\DomainService;

use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUser;
use App\Lib\Factory\ResultFlagFactory;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Carbon\Carbon;

/**
 * 若年性認知症受入加算の仕様。
 * 40歳の誕生日の前日から65歳の誕生日の前々日の間に加算できる。
 */
class JuvenileDementiaSpecification
{
    // 加算の定数。1: なし 2: あり。
    public const NO_ADDITION = 1;
    public const ADDITIONAL = 2;

    /**
     * 実績フラグを返す。
     */
    public static function getResultFlag(FacilityUser $facilityUser, int $year, int $month): ResultFlag
    {
        // 生年月日を取得する。
        $birthDay = new Carbon($facilityUser->getBirthDay());
        // 対象年月の開始日と終了日を取得する。
        $targetYmStartDate = new Carbon("${year}-${month}-1");
        $targetYmEndDate = $targetYmStartDate->copy()->lastOfMonth();

        // 40歳の誕生日の前日と、65歳の誕生日の前々日を取得する。
        $birthDay40Sub1Day = $birthDay->copy()->addYearsNoOverflow(40)->subDays(1);
        $birthDay65Sub2Day = $birthDay->copy()->addYearsNoOverflow(65)->subDays(2);

        // 40歳の誕生日の前日と、65歳の誕生日の前々日から、対象年月の開始日と終了日を取得する。
        // 看取りなどと違い対象年月ごとに請求されるため、対象年月の前月、前々月までは求めない。
        // (看取りなどは一度しか請求しないため対象年月中に前月、前々月までを一括で請求する)
        $startDate = $birthDay40Sub1Day->copy();
        $endDate = $birthDay65Sub2Day->copy();
        if ($startDate->timestamp < $targetYmStartDate->timestamp) {
            $startDate = $startDate->setDate($year, $month, 1);
        }
        if ($endDate->timestamp > $targetYmEndDate->timestamp) {
            $endDate = $endDate->setDate($year, $month, 1)->lastOfMonth();
        }

        // 実績フラグを初期状態で生成する。
        $resultFlagObject = (new ResultFlagFactory())->generateInitial();
        // 日割対象日を取得する。
        $dateDailyRate = $resultFlagObject->getDateDailyRate();
        // 回数／日数を取得する。
        $serviceCountDate = $resultFlagObject->getServiceCountDate();
        // 日割対象日の若年性認知症受入加算の期間に実績を立てる。
        $targetDate = $startDate->copy();
        while ($targetDate->timestamp <= $endDate->timestamp) {
            $dateDailyRate[$targetDate->day -1] = '1';
            $serviceCountDate++;
            $targetDate->addDay();
        }

        // 実績フラグを作成する。
        $resultFlagObject = new ResultFlag(
            $dateDailyRate,
            $resultFlagObject->getDateDailyRateOneMonthAgo(),
            $resultFlagObject->getDateDailyRateTwoMonthAgo(),
            $serviceCountDate
        );

        return $resultFlagObject;
    }

    /**
     * サービス項目コードを返す。
     * 実装時点ではサービス種類によって項目コードが異なるパターンは認められない。
     */
    public static function getServiceCode(): string
    {
        return '6109';
    }

    /**
     * 加算可能かの判定を返す。
     */
    public static function isAvailable(
        CareRewardHistory $careRewardHistory,
        FacilityUser $facilityUser,
        int $year,
        int $month
    ): bool {
        // 誕生日を取得する。
        $birthDay = new Carbon($facilityUser->getBirthDay());
        // 40歳の誕生日の一日前を取得する。
        $birthDay40Sub1Day = $birthDay->copy()->addYearsNoOverflow(40)->subDays(1);
        // 65歳の誕生日の二日前を取得する。
        $birthDay65Sub2Day = $birthDay->copy()->addYearsNoOverflow(65)->subDays(2);

        // 対象年月の開始日と終了日を取得する。
        $targetYmStartDate = new Carbon("${year}-${month}-1");
        $targetYmEndDate = $targetYmStartDate->copy()->lastOfMonth();

        // 加算条件
        // 1. 事業所の加算がある。
        // 2. 40歳の誕生日の一日前から65歳の誕生日の二日前までを対象年月が含んでいる。
        return $careRewardHistory->isJuvenileDementiaAvailable()
            && $birthDay40Sub1Day->timestamp <= $targetYmEndDate->timestamp
            && $birthDay65Sub2Day->timestamp >= $targetYmStartDate->timestamp;
    }
}
