<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareReward extends Model
{
    protected $table = 'i_care_rewards';
    protected $connection = 'mysql';

    protected $guarded = [
      'id',
    ];
}
