<?php

namespace App\Lib\ApplicationBusinessRules\DataAccessInterface;

use App\Lib\Entity\SpecialMedicalCodes;

/**
 * 特別診療費コードの集まりのリポジトリのインターフェース。
 * 特別診療費コードを単体ではなく、ある程度のまとまりとして扱う想定が増えたため作成した。
 */
interface SpecialMedicalCodesRepositoryInterface
{
    /**
     * 特別診療費コードを返す。
     * @param array $specialMedicalCodeIds 特別診療費コードのID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ?SpecialMedicalCodes
     */
    public function get(array $specialMedicalCodeIds, int $year, int $month): ?SpecialMedicalCodes;
}
