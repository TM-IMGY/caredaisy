<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\CareRewardHistory;

/**
 * 介護報酬履歴のリポジトリのインターフェース。
 */
interface CareRewardHistoryRepositoryInterface
{
    /**
     * サービスIDから介護報酬履歴を取得して返す。
     * @param int $serviceId 事業所のサービスのID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return CareRewardHistory
     */
    public function find(int $serviceId, int $year, int $month): CareRewardHistory;
}
