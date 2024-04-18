<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * サービス実績テーブルの操作に責任を持つクラス。
 * カラムに間違えやすい service_count_date と service_count があるので注意する。
 */
class ServiceResult extends Model
{

    /* 計算種別 */
    public const CALC_KIND_INDIVIDUAL              = 1; // 個別
    public const CALC_KIND_OFFICE                  = 2; // 事業所
    public const CALC_KIND_SERVICE_TYPE_SUCCESSION = 3; // サービス種別承継
    public const CALC_KIND_SPECIAL                 = 4; // 特殊
    public const CALC_KIND_TOTAL                   = 5; // 合計

    protected $table = 'i_service_results';
    protected $connection = 'mysql';
    protected $primaryKey = 'service_result_id';

    protected $guarded = [
        'service_result_id',
    ];

    public function facilityUser()
    {
        return $this->hasOne('App\Models\FacilityUser', 'facility_user_id', 'facility_user_id');
    }

    public function serviceCodeData()
    {
        return $this->hasOne('App\Models\ServiceCode', 'service_item_code_id', 'service_item_code_id');
    }

    public function specialMedicalCode()
    {
        return $this->belongsTo('App\Models\SpecialMedicalCode', 'special_medical_code_id');
    }

    /**
     * 施設利用者の対象年月のサービス実績を返す。
     * @param int $facilityUserId
     * @param int $year
     * @param int $month
     * @return array
     */
    public static function getFacilityUserTargetYm(int $facilityUserId, int $year, int $month)
    {
        $serviceResults = self::where('calc_kind', ServiceResult::CALC_KIND_INDIVIDUAL)
            ->where('facility_user_id', $facilityUserId)
            ->whereYear('target_date', $year)
            ->whereMonth('target_date', $month)
            ->get()
            ->toArray();

        return $serviceResults;
    }

    /**
     * 複数の施設利用者の対象年月の総合計情報(計算種別5)を取得する。
     * @param int[] $facilityUserIds
     * @param int $year
     * @param int $month
     * @return array
     */
    public static function listFacilityUserTargetYmTotal(array $facilityUserIds, int $year, int $month): array
    {
        $data = self::whereYear('target_date', $year)
            ->whereMonth('target_date', $month)
            ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
            ->whereIn('facility_user_id', $facilityUserIds)
            ->get();

        if(count($data) == 0){
            return [];
        }

        return $data->toArray();
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeDate($query, $year, $month)
    {
        return $query->whereYear('target_date', $year)->whereMonth('target_date', $month);
    }
}
