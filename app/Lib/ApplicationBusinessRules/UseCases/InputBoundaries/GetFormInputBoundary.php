<?php

namespace App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries;

use App\Lib\ApplicationBusinessRules\OutputData\GetFormOutputData;

/**
 * 国保連請求の様式データの取得のユースケースのインターフェース。
 */
interface GetFormInputBoundary
{
    /**
     * @param int $facilityId 事業所ID
     * @param int $facilityUserId 施設利用者ID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return GetFormOutputData
     */
    public function handle(int $facilityId, int $facilityUserId, int $year, int $month): GetFormOutputData;
}
