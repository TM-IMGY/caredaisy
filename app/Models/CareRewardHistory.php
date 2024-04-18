<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class CareRewardHistory extends Model
{

    /* サービス形態 */
    public const SERVICE_FORM_GENERAL_TYPE              = 1; // 一般型
    public const SERVICE_FORM_EXTERNAL_SERVICE_USE_TYPE = 2; // 外部サービス利用型

    protected $table = 'i_care_reward_histories';
    protected $connection = 'mysql';
    protected $guarded = ['id'];

    /**
     * start_monthは月初、end_monthは月末固定
     * @param Builder $query
     * @return Builder
     */
    public function scopeDate($query, $year, $month)
    {
        $lastDate = (new \DateTimeImmutable("${year}-${month}"))->modify('last day of')->format('Y-m-d');
        return $query
            ->whereDate('start_month', '<=', "${year}-${month}-1")
            ->whereDate('end_month', '>=', $lastDate);
    }
}
