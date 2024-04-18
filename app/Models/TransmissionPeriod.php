<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransmissionPeriod extends Model
{
    protected $table = 'transmission_period';
    protected $connection = 'mysql';
    protected $guarded = [
        'id',
    ];
}
