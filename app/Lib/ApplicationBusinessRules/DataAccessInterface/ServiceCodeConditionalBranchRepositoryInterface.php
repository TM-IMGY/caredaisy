<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\ServiceCodeConditionalBranch;

/**
 * サービスコードの条件分岐表のリポジトリクラス。
 */
interface ServiceCodeConditionalBranchRepositoryInterface
{
    /**
     * サービスコードの条件分岐表を返す。
     * @param string $serviceType サービス種類
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ServiceCodeConditionalBranch
     */
    public function find(string $serviceType, int $year, int $month): ServiceCodeConditionalBranch;
}
