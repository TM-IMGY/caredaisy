<?php

namespace App\Lib\ApplicationBusinessRules\UseCases\Interactors;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\NationalHealthBillingRepositoryInterface;

/**
 * 国保連請求の承認状態の更新のユースケースの実装クラス。
 */
class NationalHealthBillingUpdateApprovalInteractor
{
    private NationalHealthBillingRepositoryInterface $nationalHealthBillingRepository;

    /**
     * コンストラクタ
     * @param NationalHealthBillingRepositoryInterface $nationalHealthBillingRepository
     */
    public function __construct(
        NationalHealthBillingRepositoryInterface $nationalHealthBillingRepository
    ) {
        $this->nationalHealthBillingRepository = $nationalHealthBillingRepository;
    }

    /**
     * @param int $facilityUserId 施設利用者ID
     * @param int $flag 承認フラグ
     * @param int $year 対象年
     * @param int $month 対象月
     * @return void 更新した数。
     */
    public function handle(int $facilityUserId, int $flag, int $year, int $month): void
    {
        $this->nationalHealthBillingRepository->updateApproval($facilityUserId, $flag, $year, $month);
    }
}
