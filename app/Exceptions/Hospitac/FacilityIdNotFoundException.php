<?php

namespace App\Exceptions\Hospitac;

use Exception;

/**
 * 医療機関コードに紐づく事業所IDが設定されていない場合の例外
 */
class FacilityIdNotFoundException extends Exception
{
    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        logger()->error('医療機関コードに紐づく事業所IDが設定されていません');
        logger()->info('医療機関コード: ' . $this->getMessage());
    }
}
