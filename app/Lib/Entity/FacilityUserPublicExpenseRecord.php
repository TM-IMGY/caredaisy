<?php

namespace App\Lib\Entity;

use Carbon\Carbon;

/**
 * 施設利用者の公費の記録。
 * 施設利用者は対象年月中に公費を一つではなく複数持ちうるためニーズが発生した。
 */
class FacilityUserPublicExpenseRecord
{
    private int $facilityUserId;

    /**
     * @var FacilityUserPublicExpense[] 対象年月の施設利用者の公費全て。
     */
    private array $facilityUserPublicExpenses;

    /**
     * コンストラクタ。
     * @param int $facilityUserId 施設利用者ID
     * @param FacilityUserPublicExpense[] $facilityUserPublicExpenses 対象年月の施設利用者の公費全て。
     */
    public function __construct(
        int $facilityUserId,
        array $facilityUserPublicExpenses
    ) {
        $this->facilityUserId = $facilityUserId;
        $this->facilityUserPublicExpenses = $facilityUserPublicExpenses;
    }

    /**
     * 適用される公費を返す。
     */
    public function getApplicablePublicExpense(): ?FacilityUserPublicExpense
    {
        $applicable = null;
        foreach ($this->facilityUserPublicExpenses as $publicExpense) {
            // 初回。
            if ($applicable === null) {
                $applicable = $publicExpense;
                continue;
            }

            // 優先度は数値が小さい方を採用する。
            if ($applicable->getPriority() > $publicExpense->getPriority()) {
                $applicable = $publicExpense;
                continue;
            }

            $isSameLegalNumber = $applicable->getLegalNumber() === $publicExpense->getLegalNumber();
            $applicableStartDate = new Carbon($applicable->getEffectiveStartDate());
            $startDate = new Carbon($publicExpense->getEffectiveStartDate());

            // 法別番号が同じ(=同じ公費)の場合は開始日が早い方を採用する。
            if ($isSameLegalNumber && $applicableStartDate->timestamp > $startDate->timestamp) {
                $applicable = $publicExpense;
            }
        }
        return $applicable;
    }

    /**
     * @return int
     */
    public function getFacilityUserId(): int
    {
        return $this->facilityUserId;
    }

    /**
     * 履歴を持つかを返す。
     * @return bool
     */
    public function hasRecord(): bool
    {
        return count($this->facilityUserPublicExpenses) > 0;
    }
}
