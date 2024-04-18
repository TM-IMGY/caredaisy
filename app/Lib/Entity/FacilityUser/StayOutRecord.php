<?php

namespace App\Lib\Entity\FacilityUser;

use Carbon\Carbon;

/**
 * 施設利用者の外泊の記録クラス。
 * 施設利用者は対象年月中に外泊情報を記録として持つためニーズが発生した。
 */
class StayOutRecord
{
    /**
     * @var StayOut[]
     */
    private array $stayOuts;

    /**
     * @param StayOut[]
     */
    public function __construct(array $stayOuts)
    {
        // 外泊情報を開始日でソートする。
        usort($stayOuts, function ($a, $b) {
            $aStartDate = new Carbon($a->getStartDate());
            $bStartDate = new Carbon($b->getStartDate());
            return $aStartDate->gt($bStartDate) ? 1 : -1;
        });

        $this->stayOuts = $stayOuts;
    }

    /**
     * @return StayOut[]
     */
    public function getAll(): array
    {
        return $this->stayOuts;
    }

    public function hasRecord(): bool
    {
        return count($this->stayOuts) > 0;
    }

    public function hasHospitalization(): bool
    {
        foreach ($this->stayOuts as $stayOut) {
            if ($stayOut->isHospitalization()) {
                return true;
            }
        }
        return false;
    }

    public function isTargetDate(int $year, int $month, int $day): bool
    {
        $isTargetDate = false;
        $date = new Carbon("${year}-${month}-${day}");
        foreach ($this->stayOuts as $stayOut) {
            $startDate = new Carbon($stayOut->getStartDate());
            $endDate = new Carbon($stayOut->getEndDate());
            if ($date->between($startDate, $endDate)) {
                $isTargetDate = true;
            }
        }
        return $isTargetDate;
    }
}
