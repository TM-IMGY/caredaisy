<?php

namespace App\Lib\DomainService;

use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * 認知症対応型初期加算の仕様。
 */
class DementiaInitialAdditionSpecification
{
    // 加算の定数。1: なし 2: あり
    public const ADDITIONAL = 2;

    /**
     * 実績フラグを計算して返す。
     * @param FacilityUser $facilityUser 施設利用者
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ResultFlag
     */
    public function calculateResultFlag(FacilityUser $facilityUser, StayOutRecord $stayOutRecord, int $year, int $month): ResultFlag
    {
        // 入居日を取得する。
        $startDate = new Carbon($facilityUser->getStartDate());
        // 対象年月の開始日と終了日を取得する。
        $targetYmStartDate = new Carbon("${year}-${month}-1");
        $targetYmEndDate = $targetYmStartDate->copy()->endOfMonth();

        // 入居日の29日後を取得する。
        $startDateAfter29 = $startDate->copy()->addDay(29);

        // 入居日が対象年月より後の場合は実績フラグが立たない。
        // 同様に入居日の29日後が対象年月より前の場合は実績フラグが立たない。
        if ($startDate->gt($targetYmEndDate) || $startDateAfter29->lt($targetYmStartDate)) {
            return self::calculateStayOutResultFlag($stayOutRecord, $year, $month);
        }

        // 入居日が対象年月より前の場合は対象年月で丸める。
        if ($startDate->lt($targetYmStartDate)) {
            $startDate->year($year)->month($month)->day(1);
        }
        // 入居日の29日後が対象年月より後の場合は対象年月で丸める。
        if ($startDateAfter29->gt($targetYmEndDate)) {
            $startDateAfter29->year($year)->month($month)->endOfMonth();
        }

        // 加算の期間を取得する。
        $period = range($startDate->day, $startDateAfter29->day);

        // 加算の期間から日割対象日を作成する。
        $dateDailyRate = str_repeat('0', 31);
        $dateDailyRate = substr_replace(
            $dateDailyRate,
            str_repeat('1', count($period)),
            $period[0] - 1,
            count($period)
        );

        // 実績フラグを取得
        $resultFlag = new ResultFlag($dateDailyRate, '0000000000000000000000000000000', '0000000000000000000000000000000', count($period));
        $stayOutResultFlagObj = $this->calculateStayOutResultFlag($stayOutRecord, $year, $month);

        // 実績フラグをマージする
        $mergeCount = 0;
        $mergeDateDailyRate = '';
        for ($i = 0; $i < mb_strlen($resultFlag->getDateDailyRate()); $i++) {
            if (mb_substr($resultFlag->getDateDailyRate(), $i, 1) == '1' || mb_substr($stayOutResultFlagObj->getDateDailyRate(), $i, 1) == '1') {
                $mergeDateDailyRate .= '1';
                $mergeCount++;
            } else {
                $mergeDateDailyRate .= '0';
            }
        }

        return new ResultFlag($mergeDateDailyRate, '0000000000000000000000000000000', '0000000000000000000000000000000', $mergeCount);
    }

    /**
     * 外泊(入院)の実績フラグを計算して返す
     * @param StayOutRecord $stayOutRecord 外泊履歴
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ResultFlag
     */
    public function calculateStayOutResultFlag(StayOutRecord $stayOutRecord, int $year, int $month): ResultFlag
    {
        // 外泊(入院)の終了日を取得
        $stayOutEndDate = $this->getStayOutEndDate($stayOutRecord, $year, $month);
        if (empty($stayOutEndDate)) {
            return new ResultFlag('0000000000000000000000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 0);
        }
        $endDate = new Carbon($stayOutEndDate);

        // 対象年月の開始日と終了日を取得する。
        $targetYmStartDate = new Carbon("${year}-${month}-1");
        $targetYmEndDate = $targetYmStartDate->copy()->endOfMonth();

        // 終了日の29日後を取得する。
        $endDateAfter29 = $endDate->copy()->addDay(29);

        // 入居日が対象年月より後の場合は実績フラグが立たない。
        // 同様に入居日の29日後が対象年月より前の場合は実績フラグが立たない。
        if ($endDate->gt($targetYmEndDate) || $endDateAfter29->lt($targetYmStartDate)) {
            return new ResultFlag('0000000000000000000000000000000', '0000000000000000000000000000000', '0000000000000000000000000000000', 0);
        }

        // 入居日が対象年月より前の場合は対象年月で丸める。
        if ($endDate->lt($targetYmStartDate)) {
            $endDate->year($year)->month($month)->day(1);
        }
        // 入居日の29日後が対象年月より後の場合は対象年月で丸める。
        if ($endDateAfter29->gt($targetYmEndDate)) {
            $endDateAfter29->year($year)->month($month)->endOfMonth();
        }

        // 加算の期間を取得する。
        $period = range($endDate->day, $endDateAfter29->day);

        // 加算の期間から日割対象日を作成する。
        $dateDailyRate = str_repeat('0', 31);
        $dateDailyRate = substr_replace(
            $dateDailyRate,
            str_repeat('1', count($period)),
            $period[0] - 1,
            count($period)
        );

        return new ResultFlag($dateDailyRate, '0000000000000000000000000000000', '0000000000000000000000000000000', count($period));
    }

    /**
     * サービスコードを返す。
     * @return string
     */
    public function getServiceCode(): string
    {
        return '1550';
    }

    /**
     * 取得可能かの判定を返す。
     * @param CareRewardHistory $careRewardHistory
     * @param FacilityUser $facilityUser
     * @param StayOutRecord $stayOutRecord
     * @param int year
     * @param int month
     * @return bool
     */
    public function isAvailable(CareRewardHistory $careRewardHistory, FacilityUser $facilityUser, StayOutRecord $stayOutRecord, int $year, int $month): bool
    {
        // 対象年月の開始日を取得する。
        $targetYmStartDate = new CarbonImmutable("${year}-${month}-1");
        // 入居日を取得する。
        $startDate = new CarbonImmutable($facilityUser->getStartDate());
        // 入居日から29日後を取得する。
        $startDateAfter29 = $startDate->addDay(29);

        // 外泊(入院)の終了日を取得
        $targetYmEndDateWork = $this->getStayOutEndDate($stayOutRecord, $year, $month);
        $checkStayOutEndDate = false;
        if (!empty($targetYmEndDateWork)) {
            $targetYmEndDate = new CarbonImmutable($targetYmEndDateWork);
            $endDateAfter29 = $targetYmEndDate->addDay(29);
            $checkStayOutEndDate = $targetYmStartDate->lte($endDateAfter29);
        }

        // 入居日から30日以内である場合に取得できる。
        return $careRewardHistory->isInitialAvailable() && ($targetYmStartDate->lte($startDateAfter29) || $checkStayOutEndDate);
    }

    /**
     * 外泊(入院)の終了日を取得する
     * @param StayOutRecord $stayOutRecord
     * @param int $year
     * @param int $month
     * @return string
     */
    public function getStayOutEndDate(StayOutRecord $stayOutRecord, $year, $month): string
    {
        $endDateTermStart = CarbonImmutable::parse("${year}-${month}-1")->subDay(29);
        $endDateTermEnd = CarbonImmutable::parse("${year}-${month}-1")->addMonthNoOverflow();

        $returnEndDate = '';
        $stayOutRecords = $stayOutRecord->getAll();
        foreach ($stayOutRecords as $stayOut) {
            if ($stayOut->isHospitalization() && $stayOut->hasEndDate()) {
                $stayOutEndDate = new CarbonImmutable($stayOut->getEndDate());
                $stayOutStartDate = new CarbonImmutable($stayOut->getStartDate());
                $stayOutDateDiff = $stayOutEndDate->diffInDays($stayOutStartDate) + 1;

                if ($stayOutEndDate >= $endDateTermStart && $stayOutEndDate < $endDateTermEnd && $stayOutDateDiff >= 31) {
                    $returnEndDate = $stayOut->getEndDate();
                    break;
                }
            }
        }

        return $returnEndDate;
    }
}
