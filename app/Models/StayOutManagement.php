<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * 施設利用者の外泊情報のテーブルを操作するクラス。
 */
class StayOutManagement extends Model
{

    /* 外泊理由 */
    public const REASON_FOR_STAY_OUT_GO_OUT          = 1; // 外出
    public const REASON_FOR_STAY_OUT_OVERNIGHT_STAY  = 2; // 外泊
    public const REASON_FOR_STAY_OUT_HOSPITALIZATION = 3; // 入院
    public const REASON_FOR_STAY_OUT_FACILITY        = 5; // 入所
    public const REASON_FOR_STAY_OUT_OTHERS          = 4; // その他

    protected $table = 'i_stay_out_managements';
    protected $connection = 'mysql';
    protected $primaryKey = 'id';

    protected $guarded = [
        'id',
    ];

    /**
     * 施設利用者の対象年月の情報を全て返す。
     * @param int $facilityUserId
     * @param int $year
     * @param int $month
     * @return array
     */
    public static function getTargetYm(int $facilityUserId, int $year, int $month): array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        // 施設利用者の対象年月の外泊情報を取得する。
        $stayOuts = self::where('facility_user_id', $facilityUserId)
            ->whereDate('start_date', '<=', $targetMonthEndDate)
            ->whereRaw('( end_date is null OR end_date >= ? )', [$targetMonthStartDate])
            ->get()
            ->toArray();

        return $stayOuts;
    }

    /**
     * 対象年月の範囲で絞り込みを行う
     */
    public function scopeDate($query, $year, $month)
    {
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y-m-d');
        return $query
            ->whereDate('start_date', '>=', "${year}-${month}-1")
            ->whereDate('end_date', '<=', $lastDate);
    }

    /**
     * 複数の施設利用者の対象年月の情報を全て返す。
     * @param int[] $facilityUserIds
     * @param int $year
     * @param int $month
     * @return array
     */
    public static function listTargetYm(array $facilityUserIds, int $year, int $month): array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        // 施設利用者全員の対象年月の外泊情報を取得する。
        $stayOuts = self::whereIn('facility_user_id', $facilityUserIds)
            ->whereDate('start_date', '<=', $targetMonthEndDate)
            ->whereRaw('( end_date is null OR end_date >= ? )', [$targetMonthStartDate])
            ->get()
            ->toArray();

        return $stayOuts;
    }
}
