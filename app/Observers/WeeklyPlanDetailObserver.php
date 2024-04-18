<?php

namespace App\Observers;

use App\Models\WeeklyPlanDetail;

class WeeklyPlanDetailObserver
{
    public function deleted(WeeklyPlanDetail $model)
    {
        // 親も削除する
        $model->weeklyPlan->delete();
    }
}
