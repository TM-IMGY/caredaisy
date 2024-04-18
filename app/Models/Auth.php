<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auth extends Model
{
    protected $table = 'm_auths';
    protected $connection = 'mysql';

    protected $primaryKey = "auth_id";

    protected $guarded = ['auth_id'];

    protected $casts = [
        'request'  => 'json',
        'authority'  => 'json',
        'care_plan'  => 'json',
        'facility'  => 'json',
        'facility_user_1'  => 'json',
        'facility_user_2'  => 'json',
    ];
}
