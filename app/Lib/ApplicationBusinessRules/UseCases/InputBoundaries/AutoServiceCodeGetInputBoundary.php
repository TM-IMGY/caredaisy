<?php

namespace App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries;

use App\Lib\ApplicationBusinessRules\OutputData\AutoServiceCodeOutputData;

/**
 * 自動サービスコードのユースケースのインターフェース。
 */
interface AutoServiceCodeGetInputBoundary
{
    /**
     * 施設利用者に対象年月に提供したと考えられるサービスコードを返す。
     */
    public function handle(int $facilityId, int $facilityUserId, int $year, int $month): AutoServiceCodeOutputData;
}
