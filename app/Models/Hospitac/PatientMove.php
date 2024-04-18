<?php

namespace App\Models\Hospitac;

use Illuminate\Database\Eloquent\Model;

/**
 * 患者移動情報モデル
 */
class PatientMove extends Model
{
    protected $connection = 'mysql';

    protected $casts = [
        'birthday' => 'date',
    ];

    public function file()
    {
        return $this->belongsTo('App\Models\Hospitac\HospitacFileLinkage', 'hospitac_file_coordination_id');
    }
}
