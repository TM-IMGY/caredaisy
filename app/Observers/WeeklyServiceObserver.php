<?php

namespace App\Observers;

use App\Models\WeeklyService;
use App\Models\WeeklyServiceCategory;

class WeeklyServiceObserver
{
    public function creating(WeeklyService $model)
    {
        // 親がいない場合作成する
        if (empty($model->weekly_service_category_id)) {
            $category = WeeklyServiceCategory::create($model->toArray());
            $model->weekly_service_category_id = $category->id;
        }
    }
}
