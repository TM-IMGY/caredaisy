<?php

namespace App\Service\GroupHome;

use App\Models\ServicePlan;
use App\Models\CorporationAccount;
use App\Models\Facility;
use App\Models\Institution;
use App\Models\UserFacilityInformation;

class CarePlanService
{
    public function getPlanEndDates(){
      // ログインユーザーに紐づいている事業所を取得
        $corporationId = CorporationAccount::where('account_id', \Auth::id())->select('corporation_id')->first();
        $institutionIdList = Institution::where('corporation_id', $corporationId->corporation_id)->select('id')->get()->map(function($item){ return $item->id;
        });
        $facilityIdList = Facility::whereIn('institution_id', $institutionIdList)->select('facility_id')->get()->toArray();
        $facilityIds = array_column($facilityIdList, 'facility_id');

      // 事業所に登録されている利用者IDを取得
        $facilityUserId = UserFacilityInformation::whereIn('facility_id', $facilityIds)
            ->select('facility_user_id')
            ->get();

      // 一致する利用者IDを持つ「交付済」ステータスの履歴から利用者IDと終了日を取得
        $planUserEndDates = ServicePlan::whereIn('facility_user_id', $facilityUserId)
            ->where('status', ServicePlan::STATUS_ISSUED)
            ->select('facility_user_id', 'end_date')
            ->orderBy('end_date', 'desc')
            ->get();

        return $planUserEndDates->groupBy('facility_user_id');
    }
}
