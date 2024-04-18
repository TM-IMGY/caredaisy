<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialMedicalSelect extends Model
{
    protected $table = 'special_medical_selects';
    protected $connection = 'mysql';

    protected $guarded = [
        'id',
    ];

}
