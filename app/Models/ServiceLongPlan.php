<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceLongPlan extends Model
{
    protected $table = 'i_service_long_plans';
    protected $connection = 'mysql';

  public function serviceShortPlans()
  {
    return $this->hasMany('App\Models\ServiceShortPlan', 'service_long_plan_id', 'id');
  }

  /**
   * 最新の交付済みプランから短期をコピーして保存する
   * App\Models\SecondServicePlan のCopyOfRelatedDataからの呼び出しのみに使用
   * @param int $newLongId
   * @param int $latestLongId
   */
  public static function copyShort($newLongId, $latestLongId)
  {
    $latestLongPlanAndRelationShortPlanData = self::
      with('serviceShortPlans')
      ->where('id', $latestLongId)
      ->first()
      ->toArray();

    foreach ($latestLongPlanAndRelationShortPlanData['service_short_plans'] as $key => $value) {
      $insertShort = [
        'service_long_plan_id' => $newLongId,
        'goal' => $value['goal'],
        'sort' => $value['sort'],
      ];

      $newShortId = ServiceShortPlan::insertGetId($insertShort);

      ServiceShortPlan::copySupport($newShortId, $value['id']);
    }
  }
}
