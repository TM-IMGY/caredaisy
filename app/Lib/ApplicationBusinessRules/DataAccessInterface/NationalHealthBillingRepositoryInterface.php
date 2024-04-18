<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\NationalHealthBilling;

/**
 * 国保連請求のリポジトリのインターフェース。
 */
interface NationalHealthBillingRepositoryInterface
{
    /**
     * 指定の事業所の施設利用者の国保連請求を返す。
     * @param int $facilityId 事業所ID
     * @param int $facilityUserId 施設利用者ID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return NationalHealthBilling
     */
    public function find(int $facilityId, int $facilityUserId, int $year, int $month): NationalHealthBilling;

    /**
     * 事業所と施設利用者の国保連請求を全て返す。
     * @param int $facilityId 事業所ID
     * @param int[] $facilityUserIds 施設利用者ID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return NationalHealthBilling[]
     */
    public function get(int $facilityId, array $facilityUserIds, int $year, int $month): array;

    /**
     * 国保連請求を保存する。
     * @param int $facilityUserId 施設利用者ID
     * @param ServiceResult[] $serviceResults
     * @param int $year 対象年
     * @param int $month 対象月
     * @return void
     */
    public function save(int $facilityUserId, array $serviceResults, int $year, int $month): void;

    /**
     * 指定の施設利用者の国保連請求の承認状態を更新する。
     * @param int $facilityUserId 施設利用者ID
     * @param int $flag 承認フラグ
     * @param int $year 対象年
     * @param int $month 対象月
     * @return void
     */
    public function updateApproval(int $facilityUserId, int $flag, int $year, int $month): void;
}
