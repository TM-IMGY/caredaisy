<?php

namespace App\Lib;

use App\Models\CareLevel;
use Carbon\Carbon;

/**
 * 国保連請求の知識をまとめたクラス。
 * TODO: 移植する。
 */
class NationalHealthBilling
{
    /**
     * 国保連の請求が可能な施設利用者かを返す。
     * @param int careLevelId 介護度ID
     * @return bool
     */
    public static function canBeBilled(int $careLevelId): bool
    {
        // 認定情報が非該当の場合は国保連の請求ができない。
        // 順次条件を追加していく。
        return $careLevelId != CareLevel::NOT_APPLICABLE;
    }
}
