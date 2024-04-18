<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 利用者負担限度額モデル
 */
class FacilityUserBurdenLimit extends Model
{
    protected $table = 'facility_user_burden_limits';
    protected $connection = 'mysql';

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    protected $guarded = [
        'id',
    ];

    public static function histories($facilityUserId)
    {
        $histories = self::where('facility_user_id', $facilityUserId)
            ->orderBy('start_date', 'DESC')
            ->get();
        return $histories;
    }
}
