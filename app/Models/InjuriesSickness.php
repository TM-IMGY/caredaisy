<?php

namespace App\Models;
use Carbon\CarbonImmutable;

use Illuminate\Database\Eloquent\Model;

class InjuriesSickness extends Model
{
    protected $table = 'injuries_sicknesses';
    protected $connection = 'mysql';
    protected $primaryKey = "id";

    protected $guarded = [
        'id',
      ];

    public function injuriesSicknessDetails()
    {
        return $this->hasMany('App\Models\InjuriesSicknessDetail', 'injuries_sicknesses_id');
    }

    /**
     * 対象年月の範囲で絞り込みを行う
     */
    public function scopeDate($query, $year, $month)
    {
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y-m-d');
        return $query
            ->whereDate('start_date', '<=', "${year}-${month}-1")
            ->whereDate('end_date', '>=', $lastDate);
    }

    public static function getInjuriesSicknessDetails($request)
    {
      $injuriesSicknessInfo = self::
        where('facility_user_id', $request->facility_user_id)
        ->get()
        ->toArray();
    }
}
