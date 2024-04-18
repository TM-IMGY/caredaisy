<?php

namespace App\Models\Hospitac;

use Illuminate\Database\Eloquent\Model;

/**
 * 患者基本情報モデル
 */
class PatientBasic extends Model
{
    protected $connection = 'confidential';

    protected $casts = [
        'birthday' => 'date',
        'death_date' => 'date',
    ];

    public function file()
    {
        return $this->belongsTo('App\Models\Hospitac\HospitacFileLinkage', 'hospitac_file_coordination_id');
    }
}
