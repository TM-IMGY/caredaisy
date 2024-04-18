<?php

namespace App\Lib\DomainService;

use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUserIndependence;

/**
 * ケア加算の仕様。
 */
class CareAdditionSpecification
{
    // ケア加算で参照する自立度の閾値。
    public const INDEPENDENCE_LEVEL = 6;

    // 加算の定数。加算なし:1 加算Ⅰ:2 加算Ⅱ:3
    public const NO_ADDITION = 1;
    public const ADDITION_1 = 2;
    public const ADDITION_2 = 3;

    /**
     * サービスコードを返す。
     * @param CareRewardHistory $careRewardHistory
     */
    public function getServiceCode(CareRewardHistory $careRewardHistory): string
    {
        if ($careRewardHistory->getDementiaSpecialty() === self::ADDITION_1) {
            return '6133';
        } elseif ($careRewardHistory->getDementiaSpecialty() === self::ADDITION_2) {
            return '6134';
        }
    }

    /**
     * 取得可能かの判定結果を返す。
     * @param CareRewardHistory $careRewardHistory
     * @param ?FacilityUserIndependence $facilityUserIndependence
     */
    public function isAvailable(
        CareRewardHistory $careRewardHistory,
        ?FacilityUserIndependence $facilityUserIndependence
    ): bool {
        if (!$careRewardHistory->isDementiaSpecialtyAvailable() || $facilityUserIndependence === null) {
            return false;
        }
        return $facilityUserIndependence->getDementiaLevel() >= self::INDEPENDENCE_LEVEL;
    }
}
