<?php

namespace App\Models\Hospitac;

use Illuminate\Database\Eloquent\Model;

/**
 * HOSPITAC連携ファイル情報モデル
 */
class HospitacFileLinkage extends Model
{
    protected $connection = 'mysql';

    protected $casts = [
        'file_created_dt' => 'datetime',
    ];

    protected $guarded = [
        'id',
    ];

    const NOT_CAPTURED = 1; // 未取込
    const CAPTURED = 2; // 取込済
    const NO_IMPORT_REQUIRED = 3; // 取込不要
    const IMPORT_ERROR = 4; // 取込エラー
    const FILE_IMPORT_ERROR = 5; // ファイル取込エラー
    // 種別
    const TYPE_OF_PATIENT_BASIC = 'PT'; // 患者基礎情報
    const TYPE_OF_PATIENT_MOVE = 'IN'; // 患者移動情報
    const TYPE_OF_PATIENT_MEDICAL_CARE = 'TR'; // 患者診療情報

    // HOSPITAC側から送られてくるファイルデータを取得するための各種バイト数
    const TYPE_BYTE = [0,1]; // 種別
    const PROCESSING_CATEGORY_BYTE = [2,3]; // 処理区分
    const FILE_CREATED_DT_BYTE = [4,17]; // ファイル作成日時
    const FILE_LENGTH_BYTE = [21,26]; // ファイル長
    const MEDICAL_INSTITUTION_CODE_BYTE = [27,36]; // 医療機関コード
    const PATIENT_NUMBER_BYTE = [0,9]; // 患者番号

    public function linkageSetting()
    {
        return $this->belongsTo('App\Models\Hospitac\HospitacLinkageSetting', 'medical_institution_code', 'medical_institution_code');
    }

    public function patientMedicalCares()
    {
        return $this->hasMany('App\Models\Hospitac\PatientMedicalCare', 'hospitac_file_coordination_id');
    }

    public function userFacilityInfos()
    {
        return $this->belongsTo('App\Models\UserFacilityInformation', 'patient_number', 'contractor_number');
    }
}
