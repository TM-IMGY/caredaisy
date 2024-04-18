<?php

namespace App\Lib\ApplicationBusinessRules\UseCases\MockInteractors;

use App\Lib\ApplicationBusinessRules\OutputData\GetFormOutputData;
use App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries\GetFormInputBoundary;
use Exception;

/**
 * 国保連請求の様式データの取得のユースケースのモック実装クラス。
 */
class GetFormMockInteractor implements GetFormInputBoundary
{
    /**
     * @param int $facilityId 事業所ID
     * @param int $facilityUserId 施設利用者ID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return GetFormOutputData
     */
    public function handle(int $facilityId, int $facilityUserId, int $year, int $month): GetFormOutputData
    {
        throw new Exception('様式データの取得のユースケースの例外(モック)', 1);
    }
}
