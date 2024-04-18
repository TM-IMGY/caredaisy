<?php

namespace App\Lib\DomainService;

use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Carbon\Carbon;

/**
 * 外泊の仕様。
 */
class StayOutSpecification
{
    public const HOSPITALIZATION_NO_COST = 1;

    public const HOSPITALIZATION_COST = 2;
    // 外出
    public const REASON_FOR_STAY_OUT_GO_OUT = 1;
    // 外泊
    public const REASON_FOR_STAY_OUT_OVERNIGHT_STAY = 2;
    // 入院
    public const REASON_FOR_STAY_OUT_HOSPITALIZATION = 3;
    // 入所
    public const REASON_FOR_STAY_OUT_FACILITY = 5;
    // その他
    public const REASON_FOR_STAY_OUT_OTHERS = 4;

    /**
     * 外泊期間のフラグを削除して返す。
     */
    public function deleteByStayOut(
        ?string $facilityUserEndDate,
        ResultFlag $resultFlag,
        StayOutRecord $stayOutRecord,
        int $year,
        int $month
    ): ResultFlag {
        $dateDailyRate = $resultFlag->getDateDailyRate();
        $dateDailyRateOneMonthAgo = $resultFlag->getDateDailyRateOneMonthAgo();
        $dateDailyRateTwoMonthAgo = $resultFlag->getDateDailyRateTwoMonthAgo();
        $serviceCountDate = $resultFlag->getServiceCountDate();
        $targetYmStartDate = new Carbon("${year}-${month}-1");
        $targetYmEndDate = (new Carbon("${year}-${month}-1"))->endOfMonth();
        $oneMonthAgoStartDate = $targetYmStartDate->copy()->subMonthNoOverflow();
        $twoMonthAgoStartDate = $targetYmStartDate->copy()->subMonthsNoOverflow(2);

        // 施設利用者の外泊期間のフラグを削除する。
        $stayOuts = $stayOutRecord->getAll();
        foreach ($stayOuts as $stayOut) {
            $startDate = new Carbon($stayOut->getStartDate());

            // 外泊の終了日がない場合は対象年月末として扱う。
            $endDate = null;
            if ($stayOut->hasEndDate()) {
                $endDate = new Carbon($stayOut->getEndDate());
            } else {
                // ケアデイジーは外泊で秒を参照しない。
                $endDate = $targetYmEndDate->copy()->seconds(0);
            }

            // 外泊の終了日を施設利用者の退去日で丸める。
            // 施設利用者の退去日時は23:59:00として扱う。
            $facilityUserEndDateTime = null;
            if ($facilityUserEndDate !== null) {
                $facilityUserEndDateTime = (new Carbon($facilityUserEndDate))->hour(23)->minute(59)->seconds(0);
            }
            if ($facilityUserEndDateTime !== null && $endDate->gt($facilityUserEndDateTime)) {
                $endDate = $facilityUserEndDateTime;
            }

            // 1秒でも施設にいれば外泊期間から除外する。
            // 判定対象は開始日と終了日のみ。ケアデイジーは外泊で秒を参照しないので0秒として扱う。
            if (!($startDate->hour === 0 && $startDate->minute === 0 && $startDate->second === 0)) {
                $startDate->addDay()->hour(0)->minute(0)->seconds(0);
            }
            if (!($endDate->hour === 23 && $endDate->minute === 59 && $endDate->second === 0)) {
                $endDate->subDay()->hour(23)->minute(59)->seconds(0);
            }

            // 開始日が終了日を超えた場合は終了する。
            if ($startDate->gt($endDate)) {
                continue;
            }

            // 対象年月から前々月中の、外泊期間のそれぞれの日についてフラグを削除する。
            $targetDate = $twoMonthAgoStartDate->copy();
            while ($targetDate->lte($targetYmEndDate)) {
                if (!($targetDate->between($startDate, $endDate))) {
                    $targetDate->addDay();
                    continue;
                }

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
        }

        return new ResultFlag($dateDailyRate, $dateDailyRateOneMonthAgo, $dateDailyRateTwoMonthAgo, $serviceCountDate);
    }

    /**
     * 施設利用者が対象月に入院時費用の利用が可能かを判定する。
     * @param CareRewardHistory $careRewardHistory 施設利用者が利用している事業所の介護報酬履歴
     * @param StayOutRecord $stayOutRecord 施設利用者の外泊の記録
     * @param int $year 対象年
     * @param int $month 対象月
     * @return bool
     */
    public function isAvailable(CareRewardHistory $careRewardHistory, StayOutRecord $stayOutRecord, int $year, int $month): bool
    {
        // 対象利用者の入院実績フラグを取得する。
        $resultFlag = $this->calculateHospitalizationResultFlag($stayOutRecord, $year, $month);
        // 入院時費用のサービスコード利用が可能かを返す。
        return $resultFlag->isOfferdService() && $careRewardHistory->isHospitalizationCost();
    }

    /**
     * 入院の実績フラグを計算して返す。
     * 外泊の特殊例であり共通部分もあるが違いも大きいので注意する。
     * 通常例との差別化点の一つは時間まで参照する必要がないこと。
     * 二つは実績フラグを新規に作成すること。
     * また対象年月を参照するが施設利用者の外泊の記録は全期間で必要になる(対象年月以前の実績フラグを再計算する必要があるため)。
     */
    public function calculateHospitalizationResultFlag(StayOutRecord $stayOutRecord, int $year, int $month): ResultFlag
    {
        // 対象年月の開始日と終了日を取得する。
        $targetYmStartDate = new Carbon("${year}-${month}-1");
        $targetYmEndDate = (new Carbon("${year}-${month}-1"))->endOfMonth();
        // 対象年月の前月の開始日を取得する。
        $previousMonthStartDate = $targetYmStartDate->copy()->subMonthNoOverflow()->day(1);

        // 施設利用者の入院期間それぞれについて実績フラグを計算する。
        $stayOuts = $stayOutRecord->getAll();
        // 対象年月の日割対象日を確保するための変数。
        $dateDailyRate = str_repeat('0', 31);
        // 対象年月の日割対象日の合計を確保するための変数。
        $dateDailyRateSum = 0;
        // 対象年月の前月の日割対象日の合計を確保するための変数。
        $totalOfPreviousMonth = 0;
        foreach ($stayOuts as $stayOut) {
            // 入院でない場合はスキップする。
            if (!$stayOut->isHospitalization()) {
                continue;
            }

            // 入院の開始日を取得する。
            $startDate = new Carbon($stayOut->getStartDate());

            // 入院の終了日を取得する。
            // nullの場合は対象年月の翌月初日として扱う。
            // 対象月末としないのは終了日が月末となって除外されてしまうため。
            $endDate = null;
            if ($stayOut->hasEndDate()) {
                $endDate = new Carbon($stayOut->getEndDate());
            } else {
                $endDate = (new Carbon("${year}-${month}-1"))->endOfMonth()->addDay();
            }

            // 国保連請求のサービス実績の文脈では入院は開始日と終了日を除外する。
            // したがって開始日と終了日が一致する場合は実績フラグが立たないのでスキップする。(事例としては日帰り入院など)。
            // 同様の理由で入院期間が開始日と終了日しかない場合もスキップする。
            if ($startDate->isSameDay($endDate) || $startDate->diffInDays($endDate, true) === 1) {
                continue;
            }
            $startDate = $startDate->addDay()->hour(0)->minute(0)->seconds(0);
            $endDate = $endDate->subDay()->hour(23)->minute(59)->seconds(0);

            // 入院期間のそれぞれの日について1のフラグが立つか判定する。
            $date = $startDate->copy();
            // 前月から対象年月にかけて連続で実績フラグが立つかのフラグを確保する変数。
            $isSerialFlag = false;
            while ($date->between($startDate, $endDate)) {
                // 対象年月の前月でかつフラグを立てられる場合は連続加算可能かを判定する。
                if ($date->isSameMonth($previousMonthStartDate, true) && $totalOfPreviousMonth < 6) {
                    $isSerialFlag = $date->day === $date->daysInMonth;
                    $totalOfPreviousMonth++;
                    $date->addDay();
                    continue;
                // それ以外の対象年月外の場合はフラグを立てる必要がないのでスキップする。
                } elseif (!($date->between($targetYmStartDate, $targetYmEndDate))) {
                    $date->addDay();
                    continue;
                }

                // 入院期間の実績フラグの合計が6以上の場合、または月連続加算フラグが立っていない場合は終了する。
                if (!($totalOfPreviousMonth < 6 || $isSerialFlag)) {
                    break;
                }

                // 日割り対象日にフラグとして1を立てる。
                $dateDailyRate = substr_replace($dateDailyRate, '1', $date->day - 1, 1);

                $dateDailyRateSum++;

                // 日割対象日のカウントが6になったら終了する。
                if ($dateDailyRateSum >= 6) {
                    break;
                }

                $date->addDay();
            }
            $totalOfPreviousMonth = 0;
        }

        $resultFlag = new ResultFlag($dateDailyRate, str_repeat('0', 31), str_repeat('0', 31), $dateDailyRateSum);

        return $resultFlag;
    }

    /**
     *  入院時費用のサービス項目コードを返す。
     */
    public function getServiceCode(): string
    {
        return ServiceItemCodeSpecification::HOSPITALIZATION_CODE;
    }
}
