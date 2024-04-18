<?php

namespace App\Models\Hospitac;

use Illuminate\Database\Eloquent\Model;

/**
 * 患者診療情報モデル
 */
class PatientMedicalCare extends Model
{
    protected $connection = 'mysql';

    protected $casts = [
        'medical_care_date' => 'date',
    ];

    protected $guarded = [
        'id',
    ];

    // HOSPITAC側から送られてくるファイルデータを取得するための各種バイト数
    public const MEDICAL_CARE_DATE_BYTE = [10,17]; // 診療日
    public const MEDICAL_CARE_INFO_COUNT_BYTE = [18,19]; // 診療情報数
    public const ORDER_NUMBER_BYTE = [0,29]; // オーダー番号
    public const DATA_TYPE_BYTE = [30,30]; // データ種別
    public const RECEIPT_CODE_BYTE = [31,39]; // レセ電コード
    public const ITEM_NAME_BYTE = [40,119]; // 項目名称
    public const SERVICE_CODE_BYTE = [120,125]; // サービスコード
    public const UNINSURED_COST_BYTE = [126,131]; // 自費金額
    public const QUANTITY_BYTE = [132,141]; // 数量
    public const COUNT_BYTE = [142,144]; // 回数
    public const SPECIAL_DIET_COUNT_BYTE = [145,145]; // 特食回数
    public const OCCUPATION_BYTE = [0,1]; // 職種
    public const MEDICAL_ROW_SERVICE_CODE_BYTE = [2,7]; // サービスコード
    public const REHABILITATION_SICKNESS_NAME_BYTE = [8,107]; // リハ病名

    // データ種別
    const DATA_TYPE_MEDICAL_PRACTICE = 1; // 診療行為
    const DATA_TYPE_FOOD = 3; // 食事
    const DATA_TYPE_UNINSURED_COST = 4; // 自費

    // バッチ処理内で差分チェックするカラム
    const DIFF_CHECK_COLUMNS= [
        'receipt_code',
        'item_name',
        'service_code',
        'uninsured_cost',
        'quantity',
        'count',
        'special_diet_count',
        'occupation',
        'rehabilitation_sickness_name'
    ];

    public function file()
    {
        return $this->belongsTo('App\Models\Hospitac\HospitacFileLinkage', 'hospitac_file_coordination_id');
    }
}
