<?php

namespace App\Models;

use App\Lib\Entity\FacilityUserBenefit;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * 施設利用者の給付率テーブルの操作に責任を持つクラス。
 */
class UserBenefitInformation extends Model
{
    protected $table = 'i_user_benefit_informations';
    protected $connection = 'mysql';
    protected $primaryKey = 'benefit_information_id';

    protected $guarded = [
        'benefit_information_id',
    ];

    public function i_facility_users()
    {
        return $this->belongsTo('App\Models\FacilityUser', 'facility_user_id', 'facility_user_id');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeDate($query, $year, $month)
    {
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y-m-d');
        return $query
            ->whereDate('effective_start_date', '<=', $lastDate)
            ->whereDate('expiry_date', '>=', "${year}-${month}-1");
    }
}
