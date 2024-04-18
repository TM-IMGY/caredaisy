<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * 特別診療費コードマスタのテーブルの操作に責任を持つクラス。
 * IDとタイムスタンプ以外は国が指定したものを利用している。
 */
class SpecialMedicalCode extends Model
{
    protected $table = 'special_medical_codes';
    protected $connection = 'mysql';

    protected $guarded = [
        'id',
    ];

    const SERVICE_NAME = [
        "01" => "感染対策指導管理",
        "02" => "特定施設管理",
        "03" => "特定施設管理個室加算",
        "04" => "特定施設管理2人部屋加算",
        "05" => "初期入所診療管理",
        "06" => "重症皮膚潰瘍管理指導",
        "09" => "薬剤管理指導",
        "57" => "薬剤管理指導情報活用加算",
        "10" => "特別薬剤管理指導加算",
        "11" => "医学情報提供（Ⅰ）",
        "12" => "医学情報提供（Ⅱ）",
        "18" => "理学療法（Ⅰ）",
        "19" => "理学療法（Ⅱ）",
        "20" => "理学療法リハビリ計画加算",
        "22" => "理学療法日常動作訓練指導加算",
        "48" => "理学療法リハビリ体制強化加算",
        "58" => "理学療法（Ⅰ）情報活用加算",
        "59" => "理学療法（Ⅱ）情報活用加算",
        "25" => "作業療法",
        "27" => "作業療法リハビリ計画加算",
        "29" => "作業療法日常動作訓練指導加算",
        "49" => "作業療法リハビリ体制強化加算",
        "60" => "作業療法情報活用加算",
        "31" => "摂食機能療法",
        "32" => "精神科作業療法",
        "33" => "認知症入所精神療法",
        "34" => "褥瘡対策指導管理（Ⅰ）",
        "56" => "褥瘡対策指導管理（Ⅱ）",
        "35" => "重度療法管理",
        "39" => "言語聴覚療法",
        "50" => "言語聴覚療法リハビリ体制強化加算",
        "61" => "言語聴覚療法情報活用加算",
        "42" => "理学療法（Ⅰ）（減算）",
        "43" => "理学療法（Ⅱ）（減算）",
        "45" => "作業療法（減算）",
        "47" => "言語聴覚療法（減算）",
        "52" => "短期集中リハビリテーション",
        "54" => "集団コミュニケーション療法",
        "55" => "認知症短期集中リハビリテーション"
    ];

    // ブラウザの特別診療費タブの番号からidentification_numを取得
    const SERVICE_NUM = [
        "01" => "0001",
        "02" => "0002",
        "03" => "0003",
        "04" => "0004",
        "05" => "0005",
        "06" => "0006",
        "09" => "0009",
        "57" => "0057",
        "10" => "0010",
        "11" => "0011",
        "12" => "0012",
        "18" => "0018",
        "19" => "0019",
        "20" => "0020",
        "22" => "0022",
        "48" => "0048",
        "58" => "0058",
        "59" => "0059",
        "25" => "0025",
        "27" => "0027",
        "29" => "0029",
        "49" => "0049",
        "60" => "0060",
        "31" => "0031",
        "32" => "0032",
        "33" => "0033",
        "34" => "0034",
        "56" => "0056",
        "35" => "0035",
        "39" => "0039",
        "50" => "0050",
        "61" => "0061",
        "42" => "0042",
        "43" => "0043",
        "45" => "0045",
        "47" => "0047",
        "52" => "0052",
        "54" => "0054",
        "55" => "0055"
    ];

    /**
     * 事業所の対象年月の特別診療費コードを全て返す。
     * @param int $facilityId 事業所ID
     * @param string $serviceTypeCode サービス種類コード
     * @param int $year 対象年
     * @param int $month 対象月
     * @return array
     */
    public static function getFacilityTargetYm(int $facilityId, string $serviceTypeCode, int $year, int $month): array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $specialMedicalCodes = self::
            where('service_type_code', $serviceTypeCode)
            ->whereDate('start_date', '<=', $targetMonthEndDate)
            ->whereDate('end_date', '>=', $targetMonthStartDate)
            // 使用されないカラムが多いためselectするカラムを制限している。
            ->select([
                'history_num',
                'id',
                'identification_num',
                'service_type_code',
                'special_medical_name',
                'start_date',
                'end_date',
                'unit'
            ])
            ->get()
            ->toArray();

        return $specialMedicalCodes;
    }

    public function scopeDate($query, $startDate, $endDate)
    {
        return $query
            ->whereDate('start_date', '<=', $startDate)
            ->whereDate('end_date', '>=', $endDate);
    }

    /**
     * 対象年月中に有効な特別診療コードを返す。
     * @param array $specialMedicalCodeIds 特別診療コードID。
     * @param int $year 対象年。
     * @param int $month 対象月。
     * @return array
     */
    public static function getValidDuringTheTargetYm(array $specialMedicalCodeIds, int $year, int $month): array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $serviceCodes = self::whereDate('start_date', '<=', $targetMonthEndDate)
            ->whereDate('end_date', '>=', $targetMonthStartDate)
            ->whereIn('id', $specialMedicalCodeIds)
            // 使用されないカラムが多いためselectするカラムを制限している。
            ->select([
                'history_num',
                'id',
                'identification_num',
                'service_type_code',
                'special_medical_name',
                'start_date',
                'end_date',
                'unit'
            ])
            ->get()
            ->toArray();

        return $serviceCodes;
    }
}
