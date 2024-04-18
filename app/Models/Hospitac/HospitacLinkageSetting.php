<?php

namespace App\Models\Hospitac;

use Illuminate\Database\Eloquent\Model;

/**
 * HOSPITAC連携設定モデル
 */
class HospitacLinkageSetting extends Model
{
    protected $connection = 'mysql';

    protected $casts = [
        'linkage_flg' => 'boolean',
    ];

    public function facility()
    {
        return $this->belongsTo('App\Models\Facility', 'facility_id');
    }
}
