<?php

namespace App\Lib\DomainService;

use App\Lib\DomainService\ServiceItemCodeSpecification;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\ServiceItemCode;
use App\Lib\Factory\ResultFlagFactory;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Carbon\Carbon;

/**
 * 看取り介護加算の仕様。
 */
class EndOfLifeCareAdditionSpecification
{
    // 加算の定数 なし:1 あり:2 加算Ⅰ:3 加算Ⅱ:4
    // TODO: 特定施設看取り介護加算 の場合 なし:1 加算Ⅰ:2 加算Ⅱ:3 になっている。
    // TODO: 地域密着型特定施設入居者生活介護 の場合 なし:1 加算Ⅰ:2 加算Ⅱ:3 になっている。
    public const NO_ADDITION = 1;
    public const ADDITIONAL = 2;
    public const ADDITION_1 = 3;
    public const ADDITION_2 = 4;

    // 加算のあるサービス種類全て。
    public const SERVICE_TYPE_CODES = ['32', '33', '36'];

    /**
     * 実績フラグを返す。
     * @param FacilityUser $facilityUser 施設利用者
     * @param ServiceItemCode $serviceItemCode サービス項目コード
     * @param StayOutRecord $stayOutRecord 外泊の記録 TODO: 使用していないので削除する。
     * @param int $year 対象年
     * @param int $month 対象月
     */
    public static function getResultFlag(
        FacilityUser $facilityUser,
        ServiceItemCode $serviceItemCode,
        StayOutRecord $stayOurRecord,
        int $year,
        int $month
    ): ResultFlag {
        $consentDate = new Carbon($facilityUser->getConsentDate());
        $deathDate = new Carbon($facilityUser->getDeathDate());
        $serviceItemCodeId = $serviceItemCode->getServiceItemCodeId();
        $targetYmStartDate = new Carbon("${year}-${month}-1");
        $oneMonthAgoStartDate = $targetYmStartDate->copy()->subMonthNoOverflow();
        $twoMonthAgoStartDate = $targetYmStartDate->copy()->subMonthsNoOverflow(2);

        // サービス回数と、看取り日からの開始と終了のスパンを作成する(初期値は加算４のもの)。
        $serviceCountDate = 1;
        $startSpan = 0;
        $endSpan = 0;
        if (in_array($serviceItemCodeId, ServiceItemCodeSpecification::END_OF_LIFE_OF_CARE_3_IDS)) {
            $serviceCountDate = 2;
            $startSpan = 2;
            $endSpan = 1;
        } elseif (in_array($serviceItemCodeId, ServiceItemCodeSpecification::END_OF_LIFE_OF_CARE_2_IDS)) {
            $serviceCountDate = 27;
            $startSpan = 29;
            $endSpan = 3;
        } elseif (in_array($serviceItemCodeId, ServiceItemCodeSpecification::END_OF_LIFE_OF_CARE_1_IDS)) {
            $serviceCountDate = 15;
            $startSpan = 44;
            $endSpan = 30;
        }

        // サービスの開始日と終了日を確保する。
        $serviceStartDate = $deathDate->copy()->subDays($startSpan)->hour(0)->minute(0)->seconds(0);
        $serviceEndDate = $deathDate->copy()->subDays($endSpan);

        // サービス開始日より同意日の方が後の場合、同意日をサービス開始日とする。
        if ($serviceStartDate->timestamp < $consentDate->timestamp) {
            $serviceCountDate -= $serviceStartDate->diffInDays($consentDate);
            $serviceStartDate = $consentDate->copy()->hour(0)->minute(0)->seconds(0);
        }

        // サービス開始日から終了日の実績フラグを作成する。
        $resultFlagNoResult = (new ResultFlagFactory())->generateInitial();
        $dateDailyRate = $resultFlagNoResult->getDateDailyRate();
        $dateDailyRateOneMonthAgo = $resultFlagNoResult->getDateDailyRateOneMonthAgo();
        $dateDailyRateTwoMonthAgo = $resultFlagNoResult->getDateDailyRateTwoMonthAgo();
        $targetDate = $serviceStartDate;
        while ($targetDate->lte($serviceEndDate)) {
            if ($targetDate->isSameMonth($targetYmStartDate, true)) {
                $dateDailyRate[$targetDate->day-1] = '1';
            } elseif ($targetDate->isSameMonth($oneMonthAgoStartDate, true)) {
                $dateDailyRateOneMonthAgo[$targetDate->day-1] = '1';
            } elseif ($targetDate->isSameMonth($twoMonthAgoStartDate, true)) {
                $dateDailyRateTwoMonthAgo[$targetDate->day-1] = '1';
            }
            $targetDate->addDay();
        }

        return new ResultFlag($dateDailyRate, $dateDailyRateOneMonthAgo, $dateDailyRateTwoMonthAgo, $serviceCountDate);
    }

    /**
     * サービス項目コードを返す。
     * @return string[]
     */
    public static function getServiceItemCodes(
        CareRewardHistory $careRewardHistory,
        FacilityUser $facilityUser,
        string $typeCode
    ): array {
        $consentDate = new Carbon($facilityUser->getConsentDate());
        $deathDate = new Carbon($facilityUser->getDeathDate());

        $itemCodes = self::getItemCodesByTypeCode($careRewardHistory, $typeCode);

        // 「看取り日 - 同意日」で項目コードの取得の境界を判断する。
        if (count($itemCodes) > 0) {
            $diff = $consentDate->diffInDays($deathDate, true);
            $itemCodeIndex = 0;
            if ($diff >= 30) {
                $itemCodeIndex = 4;
            } elseif ($diff >= 3) {
                $itemCodeIndex = 3;
            } elseif ($diff >= 1) {
                $itemCodeIndex = 2;
            } elseif ($diff >= 0) {
                $itemCodeIndex = 1;
            }
            $itemCodes = array_slice($itemCodes, 0, $itemCodeIndex);
        }

        return $itemCodes;
    }

    /**
     * サービス種類コードに紐づくサービス項目コードを全て返す。
     * 「看取り日 - 同意日」の期間ごとに1から4のサービスコードが合計で4つ存在することが想定される。
     * 同じサービス項目コードでもサービス種類コードが違えば別ものなので注意する。
     * 例えば326140は認知症対応型看取り介護加算"１"だが、336140は特定施設看取り介護加算Ⅱ"４"
     * @return string[]
     */
    public static function getItemCodesByTypeCode(
        CareRewardHistory $careRewardHistory,
        string $serviceTypeCode
    ): array {
        $nursingCare = $careRewardHistory->getNursingCare();

        $serviceItemCodes = [];
        if ($serviceTypeCode === '32') {
            $serviceItemCodes = ['6144', '6143', '6142', '6140'];
        } elseif ($serviceTypeCode == '33' && $nursingCare === self::ADDITIONAL) {
            $serviceItemCodes = ['6127', '6126', '6125', '6120'];
        } elseif ($serviceTypeCode == '33' && $nursingCare == self::ADDITION_1) {
            $serviceItemCodes = ['6140', '6139', '6138', '6137'];
        } elseif ($serviceTypeCode == '36' && $nursingCare == self::ADDITIONAL) {
            $serviceItemCodes = ['6127', '6126', '6125', '6124'];
        } elseif ($serviceTypeCode == '36' && $nursingCare == self::ADDITION_1) {
            $serviceItemCodes = ['6140', '6139', '6138', '6137'];
        } else {
            [];
        }

        return $serviceItemCodes;
    }

    /**
     * 利用可能かを返す。
     */
    public static function isAvailable(
        CareRewardHistory $careRewardHistory,
        FacilityUser $facilityUser,
        int $year,
        int $month
    ): bool {
        // 対象年月に亡くなっているかを判断する。
        $isDeadInTargetYm = false;
        $deathDate = $facilityUser->getDeathDate();
        if ($deathDate !== null) {
            $deathDateTimestamp = new Carbon($deathDate);
            $targetYmStartDate = new Carbon("${year}-${month}-1");
            $isDeadInTargetYm = $deathDateTimestamp->isSameMonth($targetYmStartDate, true);
        }

        // 取得条件は下記の通り。
        // 看取り加算あり 同意日あり 対象年月に亡くなっている
        return $careRewardHistory->isNursingCareAvailable() && $facilityUser->hasConsentDate() && $isDeadInTargetYm;
    }
}
