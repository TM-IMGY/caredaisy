<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InjuriesSicknessDetail extends Model
{
    protected $table = 'injuries_sickness_details';
    protected $connection = 'mysql';
    protected $primaryKey = "id";

    protected $guarded = [
        'id',
      ];

    public function injuriesSicknessRelations()
    {
        return $this->hasMany('App\Models\InjuriesSicknessRelation', 'injuries_sicknesses_detail_id');
    }

}
