<?php

namespace App\Lib\DomainService;

use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\Entity\FacilityUser;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use App\Lib\Entity\FacilityUserServiceRecord;
use Carbon\CarbonImmutable;
use Carbon\Carbon;

/**
 * 退院退所時相談加算の仕様のクラス。
 */
class LeavingHospitalSpecification
{
    // 加算の定数。1: なし 2: あり
    public const NO_ADDITION = 1;
    public const ADDITIONAL = 2;

    /**
     * 対象年月日に取得可能かの判定結果を返す。
     * @param FacilityUserServiceRecord $facilityUserServiceRecord 施設利用者のサービス
     * @param CareRewardHistory $careRewardHistory 介護報酬履歴
     * @param FacilityUser $facilityUser 施設利用者
     * @param StayOutRecord $stayOutRecord 施設利用者の外泊の記録
     * @param string $date
     * @return bool
     */
    public function isAvailable(
        FacilityUserServiceRecord $facilityUserServiceRecord,
        CareRewardHistory $careRewardHistory,
        FacilityUser $facilityUser,
        StayOutRecord $stayOutRecord,
        string $date
    ): bool {
        // 施設利用者のサービスの記録から、対象日のサービス種別に33か36があるかを確認する。
        $isEligibleNum = false;
        $targetFacilityUserService = $facilityUserServiceRecord->getTargetDate($date);
        if (!is_null($targetFacilityUserService)) {
            $isEligibleNum = $targetFacilityUserService->isSpecialFacility() ||
            $targetFacilityUserService->isCommunityBasedFacility();
        }

        // 特定施設退院退所がありかを確認する。
        $isDischargeCooperation = $careRewardHistory->isLeavingHospitalAvailable();

        // 入居前の状況が医療機関、介護老人保健施設、介護医療院。
        $isTargetBeforeStart = $facilityUser->isHospital() || $facilityUser->isCareHospital() ||
        $facilityUser->isHealthCareFacilities();

        $startDate = new CarbonImmutable($facilityUser->getStartDate());
        $targetYmDate = new CarbonImmutable($date);

        // 対象年月中に入居日から30日以内の日付がある。
        $startDate30 = $startDate->addDays(29);
        $is30daysDate = $startDate30->timestamp >= $targetYmDate->timestamp &&
            $targetYmDate->timestamp >= $startDate->timestamp;

        // 対象年月中に入居日から31日以降の日付がある。
        $startDate31 = $startDate->addDays(30);
        $is31daysDate = $startDate31->timestamp <= $targetYmDate->timestamp;

        // 施設利用者の外泊日の期間が30日以上でかつ、対象年月中に退所・退院日から30日以内の日付がある。
        $is30daysStayOut = false;
        $stayOutRecords = $stayOutRecord->getAll();
        foreach ($stayOutRecords as $value) {
            if (($value->isHospitalization() || $value->isFacility()) && $value->getEndDate()) {
                // 外泊終了日を取得する(時間は考慮しない)。
                $endDateStayOut = new Carbon($value->getEndDate());
                $endDateStayOut->hour(0)->minute(0)->seconds(0);
                // 外泊開始日を取得する(時間は考慮しない)。
                $startDateStayOut = new Carbon($value->getStartDate());
                $startDateStayOut->hour(0)->minute(0)->seconds(0);
                // 外泊日数を取得する。
                $stayOutDateDiff = $endDateStayOut->diffInDays($startDateStayOut) + 1;
                // 外泊終了日に30日を足した日付が対象の日付以上で、外泊終了日が対象の日付以下かを確認する。
                $endDateStayOut30 = $endDateStayOut->copy()->addDays(29);
                $is30daysStayOutResult = $endDateStayOut30->timestamp >= $targetYmDate->timestamp &&
                    $endDateStayOut->timestamp <= $targetYmDate->timestamp;
                // 外泊日数が30日以上、かつ上記の条件が正しければtrueを返す。
                if ($stayOutDateDiff >= 31 && $is30daysStayOutResult) {
                    $is30daysStayOut = true;
                    break;
                }
            };
        }

        // パターン1の条件判定。
        $isPattern1 = $isEligibleNum && $isDischargeCooperation && $isTargetBeforeStart && $is30daysDate;
        // パターン2の条件判定。
        $isPattern2 = $isEligibleNum && $isDischargeCooperation && $is31daysDate && $is30daysStayOut;

        return $isPattern1 || $isPattern2;
    }

    /**
     * 対象年月に一日でも取得可能かの判定結果を返す。
     * @param FacilityUserServiceRecord $facilityUserServiceRecord 施設利用者のサービス
     * @param CareRewardHistory $careRewardHistory 介護報酬履歴
     * @param FacilityUser $facilityUser 施設利用者
     * @param StayOutRecord $stayOutRecord 施設利用者の外泊の記録
     * @param int $year
     * @param int $month
     * @return bool
     */
    public function isAvailableByTargetym(
        FacilityUserServiceRecord $facilityUserServiceRecord,
        CareRewardHistory $careRewardHistory,
        FacilityUser $facilityUser,
        StayOutRecord $stayOutRecord,
        int $year,
        int $month
    ): bool {
        $isResult = false;
        $targetYm = new CarbonImmutable("${year}-${month}");
        for ($day = 1; $day <= $targetYm->lastOfMonth()->day; $day++) {
            $date = ("${year}-${month}-${day}");
            if ($this->isAvailable(
                $facilityUserServiceRecord,
                $careRewardHistory,
                $facilityUser,
                $stayOutRecord,
                $date
            )
            ) {
                $isResult = true;
            }
        };
        return $isResult;
    }

    /**
     * 対象年月の実績フラグを計算して返す。
     * @param FacilityUserServiceRecord $facilityUserServiceRecord 施設利用者のサービス
     * @param CareRewardHistory $careRewardHistory 介護報酬履歴
     * @param FacilityUser $facilityUser 施設利用者
     * @param StayOutRecord $stayOutRecord 施設利用者の外泊の記録
     * @param int $year
     * @param int $month
     * @return ResultFlag
     */
    public function calculateLeavingHospitalResultFlag(
        FacilityUserServiceRecord $facilityUserServiceRecord,
        CareRewardHistory $careRewardHistory,
        FacilityUser $facilityUser,
        StayOutRecord $stayOutRecord,
        int $year,
        int $month
    ): ResultFlag {
        $dateDailyRate = str_repeat('0', 31);
        $dateDailyRateSum = 0;
        $targetYm = new CarbonImmutable("${year}-${month}");
        for ($day = 1; $day <= $targetYm->lastOfMonth()->day; $day++) {
            $date = ("${year}-${month}-${day}");
            if ($this->isAvailable(
                $facilityUserServiceRecord,
                $careRewardHistory,
                $facilityUser,
                $stayOutRecord,
                $date
            )
            ) {
                $dateDailyRate[$day - 1] = '1';
                $dateDailyRateSum++;
            }
        };
        $resultFlag = new ResultFlag($dateDailyRate, str_repeat('0', 31), str_repeat('0', 31), $dateDailyRateSum);

        return $resultFlag;
    }

    /**
     * 対象のサービス種別の場合にサービス種類コードのIDを返す。
     * @param string $latestServiceTypeCode 施設利用者の対象年月の最新のサービス種類コード
     * @return ?string
     */
    public function getServiceItemCode($latestServiceTypeCode): ?string
    {
        if ($latestServiceTypeCode == '33') {
            return '6330';
        } elseif ($latestServiceTypeCode == '36') {
            return '6330';
        } else {
            return null;
        }
    }
}
