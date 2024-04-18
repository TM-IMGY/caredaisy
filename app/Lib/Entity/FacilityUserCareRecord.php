<?php

namespace App\Lib\Entity;

use Carbon\Carbon;

/**
 * 施設利用者の介護情報の記録クラス。
 * 施設利用者は対象年月中に介護情報を一つではなく複数持ちうるためニーズが発生した。
 */
class FacilityUserCareRecord
{
    private int $facilityUserId;

    /**
     * @var FacilityUserCare[] 対象年月の施設利用者の介護情報全て。
     */
    private array $facilityUserCares;

    /**
     * @param int $facilityUserId 施設利用者ID
     * @param FacilityUserCare[] $facilityUserCares 対象年月の施設利用者の介護情報全て。
     */
    public function __construct(
        int $facilityUserId,
        array $facilityUserCares
    ) {
        $this->facilityUserId = $facilityUserId;
        $this->facilityUserCares = $facilityUserCares;
    }

    /**
     * 最大の介護情報を返す。
     * @return FacilityUserCare
     */
    public function findMax(): FacilityUserCare
    {
        $max = null;
        foreach ($this->facilityUserCares as $care) {
            // 初回。
            if ($max === null) {
                $max = $care;
            // 介護度が大きい方を採用する。
            } elseif ($max->getCareLevel()->getCareLevel() < $care->getCareLevel()->getCareLevel()) {
                $max = $care;
            }
        }
        return $max;
    }

    /**
     * 全ての介護情報を返す。
     * @return FacilityUserCare[]
     */
    public function getAll(): array
    {
        return $this->facilityUserCares;
    }

    /**
     * 最新の介護情報を返す。
     */
    public function getCareLatest(): FacilityUserCare
    {
        $latest = null;
        foreach ($this->facilityUserCares as $care) {
            // 初回。
            if ($latest === null) {
                $latest = $care;
                continue;
            }
            
            $latestStart = new Carbon($latest->getCarePeriodStart());
            $careStart = new Carbon($care->getCarePeriodStart());
            // 開始日が後のものを採用する。
            if ($latestStart->timestamp < $careStart->timestamp) {
                $latest = $care;
            }
        }
        return $latest;
    }

    /**
     * 施設利用者の介護度を全て返す。
     * @return int[]
     */
    public function getCareLevels(): array
    {
        $careLevels = null;
        foreach ($this->facilityUserCares as $care) {
            $careLevels[] = $care->getCareLevel()->getCareLevel();
        }
        return array_values(array_unique($careLevels));
    }

    /**
     * 施設利用者IDを返す。
     */
    public function getFacilityUserId(): ?int
    {
        return $this->facilityUserId;
    }

    /**
     * 履歴を持つかを返す。
     * @return bool
     */
    public function hasRecord(): bool
    {
        return count($this->facilityUserCares) > 0;
    }
}
