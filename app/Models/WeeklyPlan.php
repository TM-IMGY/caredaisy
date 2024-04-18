<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class WeeklyPlan extends Model
{
    protected $connection = 'mysql';
    protected $guarded = [
        'id',
    ];

    /**
     * relations
     */
    public function weeklyPlanDetail()
    {
        return $this->hasOne(WeeklyPlanDetail::class);
    }

    public function facility()
    {
        return $this->hasOne(Facility::class, 'facility_id', 'facility_id');
    }

    public function facilityUser()
    {
        return $this->hasOne(FacilityUser::class, 'facility_user_id', 'facility_user_id');
    }

    public function servicePlan()
    {
        return $this->hasOne(ServicePlan::class);
    }

    public static function replicateWithServicePlan(int $newServicePlanId, int $baseServicePlanId)
    {
        $weeklyPlans = self::where('service_plan_id', $baseServicePlanId)->get();
        foreach ($weeklyPlans as $weeklyPlan) {
            $clone = $weeklyPlan->replicate();
            $clone->service_plan_id = $newServicePlanId;
            $clone->save();

            $clone->weeklyPlanDetail()->create($weeklyPlan->weeklyPlanDetail->toArray());
            $clone->save();
        }
    }
}
