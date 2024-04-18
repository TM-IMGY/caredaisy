<?php

namespace App\Lib\DomainService;

use App\Lib\DementiaInitialAddition;
use App\Lib\DomainService\StayOutSpecification;
use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\FacilityUserCareRecord;
use App\Lib\Entity\FacilityUserServiceRecord;
use App\Lib\Entity\ServiceCodeConditionalBranch;
use App\Lib\Entity\ServiceItemCode;
use App\Lib\Factory\ResultFlagFactory;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use App\Lib\JuvenileDementia;
use Carbon\Carbon;

/**
 * 実績フラグの仕様。
 */
class ResultFlagSpecification
{
    /**
     * 介護情報によって実績を削除して返す。
     */
    public static function deleteByCare(
        ResultFlag $resultFlag,
        FacilityUserCareRecord $facilityUserCareRecord,
        ServiceCodeConditionalBranch $serviceCodeConditionalBranch,
        ServiceItemCode $itemCode,
        int $year,
        int $month
    ): ResultFlag {
        $dateDailyRate = $resultFlag->getDateDailyRate();
        $dateDailyRateOneMonthAgo = $resultFlag->getDateDailyRateOneMonthAgo();
        $dateDailyRateTwoMonthAgo = $resultFlag->getDateDailyRateTwoMonthAgo();
        $serviceCountDate = $resultFlag->getServiceCountDate();
        $serviceCodeCareLevel = $serviceCodeConditionalBranch->findServiceItemCodeCareLevel($itemCode);
        $targetYmStartDate = new Carbon("${year}-${month}-1");
        $targetYmEndDate = $targetYmStartDate->copy()->lastOfMonth();

        // 実績が立つ日を確保する変数を宣言する。
        $days = [];

        // 施設利用者の介護情報を全て参照する。
        $facilityUserCares = $facilityUserCareRecord->getAll();
        foreach ($facilityUserCares as $facilityUserCare) {
            // 介護度が非該当の場合はないものとして扱われる。
            if ($facilityUserCare->getCareLevel()->isNonApplicable()) {
                continue;
            }

            // 介護情報を取得する。
            $careLevel = $facilityUserCare->getCareLevel()->getCareLevel();

            // サービスコードに要求介護度がないか、要求介護度が介護情報と一致しなければ実績が立たない。
            if (!($serviceCodeCareLevel === null || $serviceCodeCareLevel === $careLevel)) {
                continue;
            }

            $periodStartDate = new Carbon($facilityUserCare->getCarePeriodStart());
            $periodEndDate = new Carbon($facilityUserCare->getCarePeriodEnd());

            // 対象年月で丸める。
            if ($periodStartDate->timestamp < $targetYmStartDate->timestamp) {
                $periodStartDate->setDate($year, $month, 1);
            }
            if ($periodEndDate->timestamp > $targetYmEndDate->timestamp) {
                $periodEndDate->setDate($year, $month, 1)->lastOfMonth();
            }

            $range = range($periodStartDate->day, $periodEndDate->day);
            $days = array_merge($days, $range);
        }

        $days = array_values(array_unique($days));

        // サービスコードの要求介護度と合わない実績フラグを削除する。
        for ($day = 1, $daysInMonth = $targetYmStartDate->daysInMonth; $day <= $daysInMonth; $day++) {
            if (!in_array($day, $days)) {
                $dateDailyRate[$day - 1] = '0';
                $serviceCountDate--;
            }
        }

        return new ResultFlag($dateDailyRate, $dateDailyRateOneMonthAgo, $dateDailyRateTwoMonthAgo, $serviceCountDate);
    }

    public static function get(
        CareRewardHistory $careRewardHistory,
        FacilityUser $facilityUser,
        FacilityUserCareRecord $facilityUserCareRecord,
        FacilityUserServiceRecord $facilityUserServiceRecord,
        ServiceCodeConditionalBranch $serviceCodeConditionalBranch,
        ServiceItemCode $itemCode,
        StayOutRecord $stayOutRecord,
        int $year,
        int $month
    ): ResultFlag {
        $startDate = new Carbon($facilityUser->getStartDate());
        $endDate = $facilityUser->hasEndDate() ? new Carbon($facilityUser->getEndDate()) : null;
        $targetYmStartDate = new Carbon("${year}-${month}-1");
        $targetYmLastDate = $targetYmStartDate->copy()->endOfMonth();
        $targetYmLastDay = $targetYmStartDate->daysInMonth;
        $oneMonthAgoStartDate = $targetYmStartDate->copy()->subMonthNoOverflow();
        $twoMonthAgoStartDate = $targetYmStartDate->copy()->subMonthsNoOverflow(2);

        // 実績フラグを作成する。
        // 実績フラグは大きく下記で分類する。
        // 実績が立たないもの、月ごとのもの、入居日や外泊の影響を受けない固定のもの(入院)。
        // それ以外(入居日や退去日の影響を受ける)。
        $resultFlag = null;
        $resultFlagFactory = new ResultFlagFactory();
        if ($itemCode->isNoResult()) {
            $resultFlag = $resultFlagFactory->generateInitial();
        } elseif ($itemCode->isPerMonth()) {
            $resultFlag = $resultFlagFactory->generatePerMonth();
        } elseif ($itemCode->isHospitalization()) {
            $stayoutSpecification = new StayOutSpecification();
            $resultFlag = $stayoutSpecification->calculateHospitalizationResultFlag($stayOutRecord, $year, $month);
        } else {
            if ($itemCode->isDementitaInitialAddition()) {
                $dementiaInitialAdditionSpecification = new DementiaInitialAdditionSpecification();
                $resultFlag = $dementiaInitialAdditionSpecification->calculateResultFlag($facilityUser, $stayOutRecord, $year, $month);
            } elseif ($itemCode->isEndOfLifeCare()) {
                $resultFlag = EndOfLifeCareAdditionSpecification::getResultFlag(
                    $facilityUser,
                    $itemCode,
                    $stayOutRecord,
                    $year,
                    $month
                );
            } elseif ($itemCode->isJuvenileDementia()) {
                $resultFlag = JuvenileDementiaSpecification::getResultFlag($facilityUser, $year, $month);
            } elseif ($itemCode->isLeavingHospital()) {
                $leavingHospitalSpecification = new LeavingHospitalSpecification();
                $resultFlag = $leavingHospitalSpecification->calculateLeavingHospitalResultFlag($facilityUserServiceRecord, $careRewardHistory, $facilityUser, $stayOutRecord, $year, $month);
            } else {
                $resultFlag = $resultFlagFactory->generateTargetYm(
                    str_repeat('1', $targetYmLastDay).str_repeat('0', 31 - $targetYmLastDay),
                    $targetYmLastDay
                );
            }

            // 実績フラグを介護情報によって削除する。
            $resultFlag = self::deleteByCare(
                $resultFlag,
                $facilityUserCareRecord,
                $serviceCodeConditionalBranch,
                $itemCode,
                $year,
                $month
            );

            // 対象年月の前々月初日より後に入居がある場合は、入居日より前の実績フラグを削除する。
            if ($twoMonthAgoStartDate->lt($startDate)) {
                $dateDailyRate = $resultFlag->getDateDailyRate();
                $dateDailyRateOneMonthAgo = $resultFlag->getDateDailyRateOneMonthAgo();
                $dateDailyRateTwoMonthAgo = $resultFlag->getDateDailyRateTwoMonthAgo();
                $serviceCountDate = $resultFlag->getServiceCountDate();
                $targetDate = $twoMonthAgoStartDate->copy();
                while ($targetDate->lt($startDate)) {
                    if ($targetDate->isSameMonth($targetYmStartDate, true) && $dateDailyRate[$targetDate->day-1] !== '0') {
                        $dateDailyRate[$targetDate->day-1] = '0';
                        $serviceCountDate--;
                    } elseif ($targetDate->isSameMonth($oneMonthAgoStartDate, true) && $dateDailyRateOneMonthAgo[$targetDate->day-1] !== '0') {
                        $dateDailyRateOneMonthAgo[$targetDate->day-1] = '0';
                        $serviceCountDate--;
                    } elseif ($targetDate->isSameMonth($twoMonthAgoStartDate, true) && $dateDailyRateTwoMonthAgo[$targetDate->day-1] !== '0') {
                        $dateDailyRateTwoMonthAgo[$targetDate->day-1] = '0';
                        $serviceCountDate--;
                    }
                    $targetDate->addDay();
                }

                $resultFlag = new ResultFlag($dateDailyRate, $dateDailyRateOneMonthAgo, $dateDailyRateTwoMonthAgo, $serviceCountDate);
            }

            // 対象年月に退去日がある場合は、それより後の実績フラグを削除する(退去日自体は算定の対象となる)。
            if ($endDate !== null && $endDate->isSameMonth($targetYmStartDate, true)) {
                $dateDailyRate = $resultFlag->getDateDailyRate();
                $dateDailyRateOneMonthAgo = $resultFlag->getDateDailyRateOneMonthAgo();
                $dateDailyRateTwoMonthAgo = $resultFlag->getDateDailyRateTwoMonthAgo();
                $serviceCountDate = $resultFlag->getServiceCountDate();
                for ($i = $endDate->day; $i < $targetYmLastDay; $i++) {
                    if ($dateDailyRate[$i] !== '0') {
                        $dateDailyRate[$i] = '0';
                        $serviceCountDate--;
                    }
                }
                $resultFlag = new ResultFlag($dateDailyRate, $dateDailyRateOneMonthAgo, $dateDailyRateTwoMonthAgo, $serviceCountDate);
            }

            // 対象年月に看取り日がある場合は、それ以降の実績フラグを削除する(看取り介護加算以外)。
            $deathDate = $facilityUser->hasDeathDate() ? new Carbon($facilityUser->getDeathDate()) : null;
            if ($deathDate !== null && $deathDate->isSameMonth($targetYmStartDate, true) && !$itemCode->isEndOfLifeCare()) {
                $dateDailyRate = $resultFlag->getDateDailyRate();
                $serviceCountDate = $resultFlag->getServiceCountDate();
                for ($i = $deathDate->day; $i < $targetYmLastDay; $i++) {
                    if ($dateDailyRate[$i] !== '0') {
                        $dateDailyRate[$i] = '0';
                        $serviceCountDate--;
                    }
                }
                $resultFlag = new ResultFlag($dateDailyRate, str_repeat('0', 31), str_repeat('0', 31), $serviceCountDate);
            }

            // 対象年月に外泊がある場合は、外泊期間のフラグを削除する。
            if ($stayOutRecord->hasRecord()) {
                $stayoutSpecification = new StayOutSpecification();
                $resultFlag = $stayoutSpecification->deleteByStayOut(
                    $endDate === null ? $endDate : $endDate->format('Y-m-d'),
                    $resultFlag,
                    $stayOutRecord,
                    $year,
                    $month
                );
            }
        }

        return $resultFlag;
    }
}
