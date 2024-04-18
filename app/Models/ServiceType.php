<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    /* サービス種類コード */
    public const SERVICE_TYPE_CODE_DEMENTIA_SUPPORT_TYPE_COMMUNAL_LIVING_CARE                          = 32; // 認知症対応型共同生活介護
    public const SERVICE_TYPE_CODE_SPECIFIC_FACILITY_RESIDENT_CARE                                     = 33; // 特定施設入居者生活介護
    public const SERVICE_TYPE_CODE_LONG_TERM_CARE_PREVENTION_SPECIFIED_FACILITY_RESIDENT_CARE          = 35; // 介護予防特定施設入居者生活介護
    public const SERVICE_TYPE_CODE_COMMUNITY_BASED_SPECIFIC_FACILITY_RESIDENT_LIFE_CARE                = 36; // 地域密着型特定施設入居者生活介護
    public const SERVICE_TYPE_CODE_LONG_TERM_CARE_PREVENTIVE_DEMENTIA_RESPONSE_TYPE_COMMUNAL_LIFE_CARE = 37; // 介護予防認知症対応型共同生活介護
    public const SERVICE_TYPE_CODE_MEDICAL_CLINIC                                                      = 55; // 介護医療院

    /**
     * 種類が選択出来る要介護度
     * keyはservice_type_code_idとcare_level_id
     */
    public const relationCareLevels = [
        1 => [6 => '要介護１', 7 => '要介護２', 8 => '要介護３', 9 => '要介護４', 10 => '要介護５'],
        2 => [5 => '要支援２'],
        3 => [6 => '要介護１', 7 => '要介護２', 8 => '要介護３', 9 => '要介護４', 10 => '要介護５'],
        4 => [6 => '要介護１', 7 => '要介護２', 8 => '要介護３', 9 => '要介護４', 10 => '要介護５'],
        5 => [1 => '非該当', 4 => '要支援１', 5 => '要支援２'],
        6 => [6 => '要介護１', 7 => '要介護２', 8 => '要介護３', 9 => '要介護４', 10 => '要介護５'],
    ];

    protected $connection = 'mysql';
    protected $table = 'm_service_types';
    protected $primaryKey = 'service_type_code_id';

    protected $guarded = [
        'service_type_code_id',
    ];

    public function publicSpending()
    {
        return $this->hasMany('App\Models\PublicSpending', 'service_type_code_id', 'service_type_code_id');
    }

    /**
     * テーブルの値は月初固定
     * @param  Builder $query
     * @return Builder
     */
    public function scopeDate($query, $year, $month)
    {
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y-m-d');
        return $query
            ->whereDate('service_start_date', '<=', "${year}-${month}-1")
            ->whereDate('service_end_date', '>=', $lastDate);
    }


    public function scopeGetServiceTypeCodeId($query, $serviceTypeCode)
    {
        return $query
            ->where('service_type_code', $serviceTypeCode)
            ->select('service_type_code_id');
    }
}
