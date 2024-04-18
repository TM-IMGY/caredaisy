<?php

namespace App\Lib\DomainService;

use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\FacilityUserCareRecord;
use App\Lib\Entity\FacilityUserIndependence;
use App\Lib\Entity\FacilityUserServiceRecord;
use App\Lib\Entity\ServiceCodeConditionalBranch;
use App\Lib\Entity\FacilityUser\StayOutRecord;

/**
 * サービスコード取得の仕様のクラス。
 */
class ServiceCodeGetSpecification
{
    /**
     * 施設利用者が対象年月に取得できるサービス項目コードを全て返す。
     * @return string[] サービス項目コード
     */
    public static function getServiceItemCodes(
        CareRewardHistory $careRewardHistory,
        FacilityUser $facilityUser,
        FacilityUserCareRecord $facilityUserCareRecord,
        ?FacilityUserIndependence $facilityUserIndependence,
        FacilityUserServiceRecord $facilityUserServiceRecord,
        ServiceCodeConditionalBranch $conditionalBranch,
        StayOutRecord $stayOutRecord,
        int $year,
        int $month
    ): array {
        $latestService = $facilityUserServiceRecord->getLatest();
        $latestServiceTypeCode = $latestService->getServiceTypeCode()->getServiceTypeCode();

        $comparisonData = [
            'dementia_level' => $facilityUserIndependence === null ? null : $facilityUserIndependence->getDementiaLevel(),
            'service_type_code' => $latestService->getServiceTypeCode()->getServiceTypeCode(),
            'year' => $year,
            'month' => $month
        ];
        $comparisonData = array_merge($comparisonData, $careRewardHistory->getData());
        $comparisonData = array_merge($comparisonData, $facilityUser->getData());

        // 条件分岐表の各ブロックから条件に合う項目コードを確保する。
        $blocks = $conditionalBranch->getBlocks();
        $itemCodes = [];
        foreach ($blocks as $block) {
            foreach ($block as $record) {
                // 条件分岐表のレコードと比較データが一致した数を記録する変数。
                $matchCount = 0;
                foreach ($record as $key => $value) {
                    // 条件分岐表に含まれるサービス種類コードと項目コードを判定から除外する。
                    if (in_array($key, ['service_type_code', 'service_item_code'])) {
                        continue;
                    }

                    // 条件分岐表に介護度についてのものがある場合、介護度は対象年月中で動的な情報なので別途判定する。
                    if ($key === 'care_level') {
                        $matchCount += in_array($value, $facilityUserCareRecord->getCareLevels()) ? 1 : 0;
                        continue;
                    }

                    if ($value === strval($comparisonData[$key])) {
                        $matchCount++;
                    }
                }

                // 条件分岐表のレコードと比較データが一致する場合サービスコードを確保する。
                // 2 を引いているのは条件分岐表に含まれるサービス種類コードと項目コードを除外するため。
                if ($matchCount === count($record) - 2) {
                    $itemCodes[] = $record['service_item_code'];
                }
            }
        }

        // ケア加算
        $careAdditionSpecification = new CareAdditionSpecification();
        if ($careAdditionSpecification->isAvailable($careRewardHistory, $facilityUserIndependence)) {
            $itemCodes[] = $careAdditionSpecification->getServiceCode($careRewardHistory, $latestServiceTypeCode);
        }

        // 認知症対応型初期加算
        $dementiaInitialAdditionSpecification = new DementiaInitialAdditionSpecification();
        if ($dementiaInitialAdditionSpecification->isAvailable($careRewardHistory, $facilityUser, $stayOutRecord, $year, $month)) {
            $itemCodes[] = $dementiaInitialAdditionSpecification->getServiceCode($latestServiceTypeCode);
        }

        // 看取り介護加算
        if (EndOfLifeCareAdditionSpecification::isAvailable($careRewardHistory, $facilityUser, $year, $month)) {
            $endOfLifeCareItemCodes = EndOfLifeCareAdditionSpecification::getServiceItemCodes(
                $careRewardHistory,
                $facilityUser,
                $latestServiceTypeCode
            );
            $itemCodes = array_merge($itemCodes, $endOfLifeCareItemCodes);
        }

        // 退居時相談援助加算が利用可能な場合。
        if (MovingOutConsultationSpecification::isAvailable($careRewardHistory, $facilityUser, $year, $month)) {
            // サービス項目コードを取得する。
            $movingOutConsultationItemCode = MovingOutConsultationSpecification::getServiceItemCode();
            $itemCodes[] = $movingOutConsultationItemCode;
        }

        // 入院時費用
        $stayOutSpecification = new StayOutSpecification();
        if ($stayOutSpecification->isAvailable($careRewardHistory, $stayOutRecord, $year, $month)) {
            $itemCodes[] = $stayOutSpecification->getServiceCode();
        }

        // 若年性認知症受入加算
        if (JuvenileDementiaSpecification::isAvailable($careRewardHistory, $facilityUser, $year, $month)) {
            $itemCodes[] = JuvenileDementiaSpecification::getServiceCode($latestServiceTypeCode);
        }

        // 特定施設退院退所時連携加算
        $leavingHospitalSpecification = new LeavingHospitalSpecification();
        if ($leavingHospitalSpecification->isAvailableByTargetym($facilityUserServiceRecord, $careRewardHistory, $facilityUser, $stayOutRecord, $year, $month)) {
            $itemCodes[] = $leavingHospitalSpecification->getServiceItemCode($latestServiceTypeCode);
        }

        return $itemCodes;
    }
}
