<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * 公費マスタテーブルの操作に責任を持つクラス。
 * 施設利用者の公費のテーブルではpublic_expenseと表記されているなど揺れがあるので注意する。
 */
class PublicSpending extends Model
{

    /* 法別番号 */
    public const LEGAL_NUMBER_LIFE_ASSISTANCE        = 12; // 生活保護
    public const LEGAL_NUMBER_JAPANESE_LEFT_IN_CHINA = 25; // 中国残留邦人等
    public const LEGAL_NUMBER_ATOMIC_BOMB_SUBSIDIZE  = 81; // 原爆助成

    protected $table = 'm_public_spendings';
    protected $connection = 'mysql';

    /**
     * 引数で渡す年月を範囲に含むか
     * @return Builder
     */
    public function scopeYearMonth($query, $year, $month)
    {
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y-m-d');
        return $query
            ->whereDate('effective_start_date', '<=', "${year}-${month}-1")
            ->whereDate('expiry_date', '>=', $lastDate);
    }
}
