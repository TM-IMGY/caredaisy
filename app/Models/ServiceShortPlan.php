<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceShortPlan extends Model
{
    protected $table = 'i_service_short_plans';
    protected $connection = 'mysql';

  public function servicePlanSupports()
  {
    return $this->hasMany('App\Models\ServicePlanSupport', 'service_short_plan_id', 'id');
  }

  /**
   * 最新の交付済みプランからサービスをコピーして保存する
   * App\Models\SecondServicePlan のCopyOfRelatedDataからの呼び出しのみに使用
   * @param int $newShortId
   * @param int $latestShortId
   */
  public static function copySupport($newShortId, $latestShortId)
  {
    $latestShortPlanAndRelationSupportData = self::
      with('servicePlanSupports')
      ->where('id', $latestShortId)
      ->first()
      ->toArray();

    foreach ($latestShortPlanAndRelationSupportData['service_plan_supports'] as $key => $value) {
      $insertSupport = [
        'service_short_plan_id' => $newShortId,
        'service' => $value['service'],
        'staff' => $value['staff'],
        'frequency' => $value['frequency'],
        'sort' => $value['sort']
      ];

      ServicePlanSupport::insertGetId($insertSupport);
    }
  }
}
