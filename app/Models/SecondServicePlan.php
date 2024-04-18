<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecondServicePlan extends Model
{
    protected $table = 'i_second_service_plans';
    protected $connection = 'mysql';

  public function servicePlanNeeds()
  {
    return $this->hasMany('App\Models\ServicePlanNeed', 'second_service_plan_id', 'id');
  }

  /**
   * 最新の交付済みプランからニーズをコピーして保存する
   * SecondServicePlanから下に紐づく各テーブルデータをコピーする場合にのみ使用
   * @param int $secondServicePlanID
   * @param int $latestServicePlan2Id
   */
  public static function CopyOfRelatedData($secondServicePlanID, $latestServicePlan2Id)
  {
    $latestServicePlanAndRelationNeedData = self::
      with('servicePlanNeeds')
      ->where('service_plan_id', $latestServicePlan2Id)
      ->first()
      ->toArray();

    foreach ($latestServicePlanAndRelationNeedData['service_plan_needs'] as $key => $value) {
      $insertNeed = [
        'second_service_plan_id' => $secondServicePlanID,
        'needs' => $value['needs'],
        'sort' => $value['sort'],
      ];

      $needId = ServicePlanNeed::insertGetId($insertNeed);

      ServicePlanNeed::copyLong($needId, $value['id']);
    }
  }
}
