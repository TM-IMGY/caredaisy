<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * TODO: サービスコードのカテゴリーを定数として持つパターンがあまりに膨らんでいるので、保持の仕方を再検討する。
 * 同じ項目コードでも内容が全く異なる場合があるので注意する。原則IDで取得すること。
 * 例) (336124)特定施設障害者等支援加算 と (366124)地域特定施設看取り介護加算Ⅰ１。
 */
class ServiceCode extends Model
{

    /* サービス項目コード */
    public const SERVICE_ITEM_CODE_6120 = 6120;
    public const SERVICE_ITEM_CODE_6124 = 6124;
    public const SERVICE_ITEM_CODE_6125 = 6125;
    public const SERVICE_ITEM_CODE_6126 = 6126;
    public const SERVICE_ITEM_CODE_6127 = 6127;
    public const SERVICE_ITEM_CODE_6137 = 6137;
    public const SERVICE_ITEM_CODE_6138 = 6138;
    public const SERVICE_ITEM_CODE_6139 = 6139;
    public const SERVICE_ITEM_CODE_6140 = 6140;
    public const SERVICE_ITEM_CODE_6142 = 6142;
    public const SERVICE_ITEM_CODE_6143 = 6143;
    public const SERVICE_ITEM_CODE_6144 = 6144;

    protected $table = 'm_service_codes';
    protected $connection = 'mysql';

    protected $guarded = [
        'service_item_code_id',
    ];

    // 特定入所者サービスコード。
    // 種類59のみが存在している。
    public const INCOMPETENT_RESIDENT_IDS = [
        // 595511 介護医療院食費
        2385,
        // 595521 介護医療院ユニット型個室
        2386,
        // 595522 介護医療院ユニット型個室的多床室
        2387,
        // 595523 介護医療院従来型個室
        2388,
        // 595524 介護医療院多床室
        2389
    ];

    // 特別診療コード。
    public const SPECIAL_MEDICAL_CODE_ID = 2384;

    /**
     * 対象年月中に有効なサービスコード情報を返す。
     * @param array $serviceItemCodeIds サービス項目コードID。
     * @param int $year
     * @param int $month
     * @return array
     */
    public static function getValidDuringTheTargetYm(array $serviceItemCodeIds, int $year, int $month): array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $serviceCodes = self::whereDate('service_start_date', '<=', $targetMonthEndDate)
            ->whereDate('service_end_date', '>=', $targetMonthStartDate)
            ->whereIn('service_item_code_id', $serviceItemCodeIds)
            ->get()
            ->toArray();

        return $serviceCodes;
    }

    /**
     * 対象年月中に有効な特定入所者サービスのサービスコードを全て返す。
     * @param int $year 対象年。
     * @param int $month 対象月。
     * @return array
     */
    public static function listIncompetentResidents(int $year, int $month): array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $incompetentResidents = self::whereDate('service_start_date', '<=', $targetMonthEndDate)
            ->whereDate('service_end_date', '>=', $targetMonthStartDate)
            ->where('service_type_code', '59')
            ->get()
            ->toArray();

        return $incompetentResidents;
    }

    /**
     * テーブルの値は月初と月末固定
     * @param Builder $query
     * @return Builder
     */
    public function scopeDate($query, $year, $month)
    {
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y-m-d');
        return $query
            ->whereDate('service_start_date', '<=', "${year}-${month}-1")
            ->whereDate('service_end_date', '>=', $lastDate);
    }

    /**
     * @param  Builder $query
     * @return Builder
     */
    public function scopeServiceCode($query, $serviceItemCodeIdList)
    {
        return $query->whereIn('service_item_code_id', $serviceItemCodeIdList);
    }
}
