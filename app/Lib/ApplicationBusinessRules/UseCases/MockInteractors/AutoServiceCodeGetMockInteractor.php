<?php

namespace App\Lib\ApplicationBusinessRules\UseCases\MockInteractors;

use App\Lib\ApplicationBusinessRules\OutputData\AutoServiceCodeOutputData;
use App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries\AutoServiceCodeGetInputBoundary;
use Exception;

/**
 * 自動サービスコードのユースケースのモック実装クラス。
 */
class AutoServiceCodeGetMockInteractor implements AutoServiceCodeGetInputBoundary
{
    public function handle(int $facilityId, int $facilityUserId, int $year, int $month): AutoServiceCodeOutputData
    {
        throw new Exception('自動サービスコードの取得のユースケースの例外(モック)', 1);
    }
}
