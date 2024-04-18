<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasicRemarks extends Model
{
    protected $table = 'basic_remarks';
    protected $connection = 'mysql';

    protected $guarded = [
        'id',
    ];

}
