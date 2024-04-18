<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityAddition extends Model
{
    protected $table = 'm_facility_additions';
    protected $connection = 'mysql';

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeDate($query, $year, $month)
    {
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y/m/d');
        return $query
            ->whereDate('addition_start_date', '<=', "${year}/${month}/1")
            ->whereDate('addition_end_date', '>=', $lastDate);
    }
}
