<?php

namespace App\Exceptions\Hospitac;

use Exception;

/**
 * 処理B内(33)で取得した保険外費用のレコードが不足している場合の例外
 */
class IncompelteUninsuredItemException extends Exception
{
    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        logger()->error('医療機関コードに紐づく保険外費用（朝食・昼食・夕食）が設定されていません');
        logger()->info($this->getMessage());
    }
}
