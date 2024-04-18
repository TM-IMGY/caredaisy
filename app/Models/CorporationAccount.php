<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorporationAccount extends Model
{
    protected $table = 'corporation_account';
    protected $connection = 'mysql';

    protected $fillable = ['account_id', 'corporation_id'];
}
