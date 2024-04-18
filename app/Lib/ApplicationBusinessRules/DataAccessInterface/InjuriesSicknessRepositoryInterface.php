<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\InjuriesSickness;
use App\Lib\Entity\InjuriesSicknessDetail;
use App\Lib\Entity\InjuriesSicknessRelation;

/**
 * 傷病のリポジトリのインターフェース。
 */
interface InjuriesSicknessRepositoryInterface
{
    /**
     * 指定の傷病名を返す。
     * @param int $facilityUserId
     * @param int $year
     * @param int $month
     * @return ?InjuriesSickness
     */
    public function find(int $facilityUserId, int $year, int $month): ?InjuriesSickness;

    /**
     * 傷病名を返す。
     * @param array $facilityUserIds
     * @param int $year
     * @param int $month
     * @return InjuriesSickness[]
     */
    public function get(array $facilityUserIds, int $year, int $month): array;
}
