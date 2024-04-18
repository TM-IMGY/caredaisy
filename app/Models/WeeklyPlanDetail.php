<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class WeeklyPlanDetail extends Model
{
    public const SERVICE_EVERYDAY = 0;
    public const WEEKLY = [
        1 => 'mon',
        2 => 'tue',
        3 => 'wed',
        4 => 'thu',
        5 => 'fri',
        6 => 'sat',
        7 => 'sun'
    ];
    public const SERVICE_MAIN = 8;
    public const SERVICE_NOT_WEEKLY = 9;

    protected $connection = 'mysql';
    protected $guarded = [
        'id'
    ];

    public function weeklyPlan()
    {
        return $this->belongsTo(WeeklyPlan::class);
    }

    public function weeklyService()
    {
        return $this->belongsTo(WeeklyService::class);
    }

    public function scopeIsWeekly($query)
    {
        return $query->whereIn('service_day', array_keys(self::WEEKLY));
    }

    public static function upsertManyWeekly($datas, $servicePlan, $facilityId)
    {
        // 差分をとるために現在のID一覧を取得する
        $currentWeeklyPlanDetailIdList = $servicePlan->weeklyPlanDetails()->isWeekly()->pluck('weekly_plan_details.id');

        $requestedWeeklyPlanDetailIdList = collect();
        foreach ($datas as $weekly) {
            $detail = self::upsertWeekly($weekly, $servicePlan, $facilityId);
            $requestedWeeklyPlanDetailIdList->push($detail->id);
        }

        // DBにあるがリクエストにIDがなかった=削除された
        $diff = $currentWeeklyPlanDetailIdList->diff($requestedWeeklyPlanDetailIdList);
        $diff->each(function ($id) {
            self::find($id)->delete(); // observeを発火させるため
        });
    }

    public static function upsertWeekly($weekly, $servicePlan, $facilityId)
    {
        // 新しいサービスの作成
        if (!empty($weekly['content'])) {
            $otherCategory = WeeklyServiceCategory::retrieveOtherCategory();
            $newService = WeeklyService::create([
                'facility_id' => $facilityId,
                'type' => WeeklyService::TYPE_GENERAL,
                'description' => $weekly['content'],
                'weekly_service_category_id' => $otherCategory->id
            ]);
            $weekly['weekly_service_id'] = $newService->id;
            unset($weekly['content']); // 週サービスは空欄にする
        }

        return self::upsert($weekly, $servicePlan, $facilityId);
    }

    public static function upsertManyMainWork($datas, $servicePlan, $facilityId)
    {
        foreach ($datas as $mainWork) {
            unset($mainWork['weekly_service_id']); // 週サービス以外は空欄にする
            $mainWork['service_day'] = self::SERVICE_MAIN;
            self::upsert($mainWork, $servicePlan, $facilityId);
        }
    }

    public static function upsertOtherService($data, $servicePlan, $facilityId)
    {
        unset($data['weekly_service_id']); // 週サービス以外は空欄にする
        $data['service_day'] = self::SERVICE_NOT_WEEKLY;
        $data['start_minutes'] = 0;
        $data['end_minutes'] = 0;
        self::upsert($data, $servicePlan, $facilityId);
    }

    public static function upsert($data, $servicePlan, $facilityId)
    {
        if (!empty($data['id'])) {
            $detail = self::find($data['id']);
        } else {
            // 週単位以外のサービス
            $detail = self::where('service_day', self::SERVICE_NOT_WEEKLY)
                ->whereHas('weeklyPlan', function ($q) use ($servicePlan) {
                    $q->where('service_plan_id', $servicePlan->id);
                })
                ->first();
        }

        // 作成時は親レコードを作成する
        if (empty($detail)) {
            $plan = new WeeklyPlan();
            $plan->facility_id = $facilityId;
            $plan->facility_user_id = $servicePlan->facility_user_id;
            $plan->service_plan_id = $servicePlan->id;
            $plan->save();

            $detail = new self();
            $data['weekly_plan_id'] = $plan->id;
        }

        $detail->fill($data);
        $detail->save();

        return $detail;
    }
}
