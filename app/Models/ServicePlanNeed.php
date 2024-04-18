<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePlanNeed extends Model
{
    protected $table = 'i_service_plan_needs';
    protected $connection = 'mysql';

  public function serviceLongPlans()
  {
    return $this->hasMany('App\Models\ServiceLongPlan', 'service_plan_need_id', 'id');
  }

  /**
   * 最新の交付済みプランから長期をコピーして保存する
   * App\Models\SecondServicePlan のCopyOfRelatedDataからの呼び出しのみに使用
   * @param int $newNeedId
   * @param int $latestNeedId
   */
  public static function copyLong($newNeedId, $latestNeedId)
  {
    $latestNeedAndRelationLongPlanData = self::
        where('id', $latestNeedId)
        ->with('serviceLongPlans')
        ->first()
        ->toArray();

    foreach ($latestNeedAndRelationLongPlanData['service_long_plans'] as $key => $value) {
        $insertLong = [
            'service_plan_need_id' => $newNeedId,
            'goal' => $value['goal'],
            'sort' => $value['sort'],
        ];

        $newLongPlanId = ServiceLongPlan::insertGetId($insertLong);

        ServiceLongPlan::copyShort($newLongPlanId, $value['id']);
    }
  }
}
