<?php

namespace App\Lib\Entity;

/**
 * 施設利用者の給付率の記録クラス。
 * 施設利用者は対象年月中に給付率を一つではなく複数持ちうるためニーズが発生した。
 */
class FacilityUserBenefitRecord
{
    private int $facilityUserId;

    /**
     * @var FacilityUserBenefit[] 対象年月の施設利用者の介護情報全て。
     */
    private array $facilityUserBenefits;

    /**
     * コンストラクタ
     * @param int $facilityUserId 施設利用者ID
     * @param FacilityUserBenefit[] $facilityUserBenefits 対象年月の施設利用者の給付率全て。
     */
    public function __construct(
        int $facilityUserId,
        array $facilityUserBenefits
    ) {
        $this->facilityUserId = $facilityUserId;
        $this->facilityUserBenefits = $facilityUserBenefits;
    }

    /**
     * 施設利用者IDを返す。
     * @return int
     */
    public function getFacilityUserId(): int
    {
        return $this->facilityUserId;
    }

    /**
     * 最新の給付率を返す。
     */
    public function getLatest(): FacilityUserBenefit
    {
        $latest = null;
        foreach ($this->facilityUserBenefits as $facilityUserBenefit) {
            // 初回。
            if ($latest === null) {
                $latest = $facilityUserBenefit;
                continue;
            }
            
            // 開始日が後のものを採用する。
            $latestStartDate = new Carbon($latest->getEffectiveStartDate());
            $startDate = new Carbon($facilityUserBenefit->getEffectiveStartDate());
            if ($latestStartDate->timestamp < $startDate->timestamp) {
                $latest = $facilityUserBenefit;
            }
        }
        return $latest;
    }

    /**
     * 履歴を持つかを返す。
     * @return bool
     */
    public function hasRecord(): bool
    {
        return count($this->facilityUserBenefits) > 0;
    }
}
