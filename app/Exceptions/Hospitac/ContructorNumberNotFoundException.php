<?php

namespace App\Exceptions\Hospitac;

use Exception;

/**
 * 医療機関コードに紐づく患者番号が設定されていない場合の例外
 */
class ContructorNumberNotFoundException extends Exception
{
    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        logger()->error('医療機関コードに紐づく患者番号が設定されていません');
        $unconverted = json_decode($this->getMessage());
        logger()->info('医療機関コード: ' . $unconverted->medical_institution_code . ', 患者番号: ' . $unconverted->patient_number . ', 事業所ID: ' . $unconverted->linkage_setting->facility_id);
    }
}
