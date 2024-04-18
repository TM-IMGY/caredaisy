<?php

namespace App\Http\Controllers\GroupHome\CarePlanInfo;

use App\Http\Controllers\Controller;
use App\Service\GroupHome\CarePlanService;

class CarePlanInfoController extends Controller
{
    public function index()
    {
        return view('group_home.care_plan_info.care_plan_info');
    }

    public function getPlanEndDates()
    {
        $carePlanService = new CarePlanService();
        return $carePlanService->getPlanEndDates();
    }
}
