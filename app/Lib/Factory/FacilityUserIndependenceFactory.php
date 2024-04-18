<?php

namespace App\Lib\Factory;

use App\Lib\DomainService\CareAdditionSpecification;
use App\Lib\Entity\FacilityUserIndependence;

/**
 * 施設利用者の自立度のファクトリ。
 */
class FacilityUserIndependenceFactory
{
    /**
     * ケア加算の対象者のテストデータを生成する。
     */
    public function generateCareAddition(): FacilityUserIndependence
    {
        return new FacilityUserIndependence(
            // dementia_level
            CareAdditionSpecification::INDEPENDENCE_LEVEL,
            // facility_user_id
            1,
            // independence_level
            1,
            // judger
            null,
            // judgment_date
            null,
            // user_independence_informations_id
            1
        );
    }

    /**
     * ケア加算の非対象者のを生成する。
     */
    public function generateCareAdditionDenial(): FacilityUserIndependence
    {
        return new FacilityUserIndependence(
            // dementia_level
            CareAdditionSpecification::INDEPENDENCE_LEVEL - 1,
            // facility_user_id
            1,
            // independence_level
            1,
            // judger
            null,
            // judgment_date
            null,
            // user_independence_informations_id
            1
        );
    }
}
