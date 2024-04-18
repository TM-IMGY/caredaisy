<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class External extends Model
{
    protected $table = 'i_external_user_id_associations';
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $guarded = [
        'id'
    ];
}
