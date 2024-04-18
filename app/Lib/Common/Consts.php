<?php

namespace App\Lib\Common;

class Consts
{
    /**
     * フラグ系の項目で 0 or 1 で判定するような場合に利用する
     */
    public const VALID   = 1;
    public const INVALID = 0;

    /**
     * 実績情報の対象年月下限
     * 請求一覧等の帳票関連でも使用するらしいので定数化
     * 03/01の場合に正しく月差分が取れないバグがあるため'UTC'を設定するようにしている
     */
    public const MIN_YEAR_MONTH = null;

    public function __construct()
    {
        $this->MIN_YEAR_MONTH = \Carbon\Carbon::parse('2021-04-01', 'UTC')->startOfMonth();
    }
}
