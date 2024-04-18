<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Exception;

/**
 * この場合のユーザーは施設利用者のことなので注意する。
 * 施設利用者が事業所から提供を受けているサービスのテーブルを操作するクラス。
 */
class UserFacilityServiceInformation extends Model
{

    /* 利用状況 */
    public const USAGE_SITUATION_IN_USE = 1; // 利用中
    public const USAGE_SITUATION_UNUSED = 2; // 未利用

    protected $connection = 'mysql';
    protected $table = 'i_user_facility_service_informations';
    protected $primaryKey = 'user_facility_service_information_id';

    protected $guarded = [
        'user_facility_service_information_id',
    ];

    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }

    public function uninsuredItems()
    {
        return $this->hasMany('App\Models\UninsuredItem', 'service_id', 'service_id');
    }

    /**
     * 引数で渡す年月を範囲に含むか
     * @return Builder
     */
    public function scopeYearMonth($query, $year, $month)
    {
        $startDate = "${year}-${month}-1";
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y-m-d');
        return $query
            ->whereDate('use_start', '<=', $lastDate)
            ->whereDate('use_end', '>=', $startDate);
    }

    /**
     * 施設利用者の対象年月の利用中のサービス情報を最新で返す。
     * @param $facilityUserId 施設利用者ID
     * @param $year 年
     * @param $month 月
     * @return array
     */
    public static function getTargetYmLatest(int $facilityUserId, int $year, int $month): array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $data = self::where('facility_user_id', $facilityUserId)
            ->whereDate('use_start', '<=', $targetMonthEndDate)
            ->whereDate('use_end', '>=', $targetMonthStartDate)
            ->where('usage_situation', 1)
            ->orderBy('use_start', 'desc')
            ->first();

        // レコードが1件もない場合。
        if($data === null){
            return [];
        }

        return $data->toArray();
    }

    /**
     * 施設利用者の対象年月の利用中のサービス情報を全て取得し、開始日降順で返す。
     * 対象年月中に施設利用者が複数のサービスを提供される場合があるので注意する。
     * @param $facilityUserId 施設利用者ID
     * @param $year 年
     * @param $month 月
     * @return array
     */
    public static function listFacilityUserTargetMonth($facilityUserId, $year, $month): array
    {
        // 対象月の月末日を取得する
        $targetMonthEndDate = (new CarbonImmutable("${year}-${month}-1"))->endOfMonth()->format('Y-m-d');
        $data = self::whereDate('use_start', '<=', $targetMonthEndDate)
            ->whereDate('use_end', '>=', "${year}-${month}-1")
            ->where('facility_user_id', $facilityUserId)
            ->where('usage_situation', 1)
            ->orderBy('use_start', 'desc')
            ->get()
            ->toArray();
        return $data;
    }

    /**
     * 対象開始日以降のサービスIDを全て取得する
     */
    public static function facilityUserEffectiveService($facilityUserId, $date)
    {
        $targetDate = (new CarbonImmutable($date))->format('Y-m-d');
        $data = self::whereDate('use_start', '<=', $targetDate)
            ->whereDate('use_end', '>=', $targetDate)
            ->where('facility_user_id', $facilityUserId)
            ->where('usage_situation', self::USAGE_SITUATION_IN_USE)
            ->pluck('service_id')
            ->toArray();

        return $data;
    }

    public static function getLatestUserFacilityServiceInformation($facilityUserId)
    {
        try {
            $model = self::where("facility_user_id", $facilityUserId)
                ->where('usage_situation', self::USAGE_SITUATION_IN_USE)
                ->orderBy('use_start', 'DESC')
                ->first();

        } catch (Exception $e) {
            report($e);
            return false;
        }
        return $model;
    }
}
