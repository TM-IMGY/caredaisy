<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InjuriesSicknessRelation extends Model
{
    protected $table = 'injuries_sickness_relations';
    protected $connection = 'mysql';
    protected $primaryKey = "id";

    protected $guarded = [
        'id',
    ];

    public function specialMedicalCode()
    {
        return $this->belongsTo('App\Models\SpecialMedicalCode');
    }
}
