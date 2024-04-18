<?php

namespace App\Http\Controllers\GroupHome\ResultInfo;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetFaclityUserInformationRequest;
use App\Http\Requests\GroupHome\ResultInfo\StayOutManagementRequest;
use App\Http\Requests\GroupHome\ResultInfo\StayOutRequest;
use App\Service\GroupHome\StayOutService;

class StayOutController extends Controller
{
    public function userInfo(GetFaclityUserInformationRequest $request)
    {
        $params = [
            'facility_user_id' => $request->facility_user_id,
        ];

        $stayOutService = new StayOutService();
        return $stayOutService->stayOutList($params);
    }

    public function stayOutDetail(StayOutRequest $request)
    {
        $params = [
            'id' => $request->id,
        ];

        $stayOutService = new StayOutService();
        return $stayOutService->stayOutDetail($params);
    }

    public function save(StayOutManagementRequest $request)
    {
        $params = [
            'facility_user_id' => $request->facility_user_id,
            'start_date' => $request->start_date,
            'start_time' => $request->start_time,
            'meal_of_the_day_start_morning' => $request->meal_of_the_day_start_morning,
            'meal_of_the_day_start_lunch' => $request->meal_of_the_day_start_lunch,
            'meal_of_the_day_start_snack' => $request->meal_of_the_day_start_snack,
            'meal_of_the_day_start_dinner' => $request->meal_of_the_day_start_dinner,
            'end_date' => $request->end_date,
            'end_time' => $request->end_time,
            'meal_of_the_day_end_morning' => $request->meal_of_the_day_end_morning,
            'meal_of_the_day_end_lunch' => $request->meal_of_the_day_end_lunch,
            'meal_of_the_day_end_snack' => $request->meal_of_the_day_end_snack,
            'meal_of_the_day_end_dinner' => $request->meal_of_the_day_end_dinner,
            'reason_for_stay_out' => $request->reason_for_stay_out,
            'remarks' => $request->remarks ?? "",
        ];

        if (isset($request->id)) {
            $params["id"] = $request->id;
        }
        $stayOutService = new StayOutService();
        $res = $stayOutService->save($params);

        if ($res) {
            return [];
        }
        abort(500);
    }

    public function delete(StayOutRequest $request)
    {
        if (empty($request->id)) {
            return [];
        }

        $stayOutService = new StayOutService();
        $res = $stayOutService->delete($request->id);

        if ($res) {
            return [];
        }
        abort(500);
    }
}
