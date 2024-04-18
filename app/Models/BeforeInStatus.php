<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeforeInStatus extends Model
{
    protected $table = 'm_before_in_statuses';
    protected $connection = 'mysql';

    /**
     * 引数で渡す年月を範囲に含むか
     * @return Builder
     */
    public function scopeYearMonth($query, $year, $month)
    {
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y-m-d');
        return $query
            ->whereDate('before_in_statuses_start_date', '<=', "${year}-${month}-1")
            ->whereDate('before_in_statuses_end_date', '>=', $lastDate);
    }
}
