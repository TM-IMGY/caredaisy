<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialMedicalDetail extends Model
{
    protected $table = 'special_medical_details';
    protected $connection = 'mysql';

    protected $guarded = [
        'id',
    ];
}
