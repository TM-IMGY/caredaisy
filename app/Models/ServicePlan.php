<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePlan extends Model
{

    /* ステータス */
    public const STATUS_SAVED      = 1; // 保存
    public const STATUS_SUBMISSED  = 2; // 提出
    public const STATUS_DETERMINED = 3; // 確定
    public const STATUS_ISSUED     = 4; // 交付済

    protected $table = 'i_service_plans';
    protected $connection = 'mysql';
    protected $guarded = [
      'id',
    ];

    public function weeklyPlans()
    {
        return $this->hasMany(WeeklyPlan::class);
    }

    public function weeklyPlanDetails()
    {
        return $this->hasManyThrough(WeeklyPlanDetail::class, WeeklyPlan::class);
    }

    public function facilityUser()
    {
        return $this->belongsTo(FacilityUser::class, 'facility_user_id');
    }

    public function secondServicePlan()
    {
      return $this->hasMany('App\Models\SecondServicePlan', 'service_plan_id', 'id');
    }

    public function scopeSelectIssuedPlanInfo($query, $clm, $condition, $target)
    {
        return $query->where($condition, $target)->where('status', self::STATUS_ISSUED)->select($clm);
    }

    /**
     * ケアプラン期間内の有効なサービス数を抽出する
     */
    public static function checkEffectiveService($servicePlanId)
    {
        $carePlan = self::where('id', $servicePlanId)->first();
        $count = UserFacilityServiceInformation::where('facility_user_id', $carePlan['facility_user_id'])
        ->where('use_start', '<=', $carePlan['start_date'])
        ->where('use_end', '>=', $carePlan['start_date'])
        ->count();

        return $count;
    }
}
