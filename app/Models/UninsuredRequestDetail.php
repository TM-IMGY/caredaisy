<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UninsuredRequestDetail extends Model
{
    protected $table = 'i_uninsured_request_details';
    protected $connection = 'mysql';
    protected $primaryKey = "id";

    protected $guarded = [
        'id',
      ];
}
