<?php

namespace App\Http\Controllers\GroupHome\CarePlanInfo;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\CarePlanInfo\ServicePlanGetRequest;
use App\Service\GroupHome\ServicePlanService;

class ServicePlanListController extends Controller
{
    /**
     * @param ServicePlanGetRequest
     */
    public function get(ServicePlanGetRequest $request)
    {
        $param = [
            'id' => $request->service_plan_id,
            'clm' => $request->clm,
        ];

        $service = new ServicePlanService();
        return $service->get($param);
    }
}
