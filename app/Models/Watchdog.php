<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watchdog extends Model
{
    protected $table = 'i_watchdogs';
    protected $connection = 'mysql';
    protected $guarded = [
        'id',
    ];
    public $timestamps = false;
}
