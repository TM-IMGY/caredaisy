<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ServicePlanRequest;
use App\Service\Api\ServicePlanService;
use Exception;
use Log;

class ServicePlanController
{
    public function __construct(ServicePlanService $servicePlan)
    {
        $this->servicePlan = $servicePlan;
    }

    public function pdf(ServicePlanRequest $request)
    {
        try {
            $employeeNumber          = $request->user()->employee_number;
            $facilityNumber          = $request->get('facility_number');
            $careDaisyFacilityUserId = $request->get('care_daisy_facility_user_id');
            $servicePlanId           = $request->get('service_plan_id');
            $pagingNo                = $request->get('paging_no');

            $facilityId = $this->servicePlan->getFacilityId($employeeNumber, $facilityNumber);
            if (empty($facilityId)) {
                // facility_numberからfacility_idが取得できなかった場合はE00005を返す
                return response()->validationError('[E00005]指定した事業所が見つかりません。');
            }

            $response = $this->servicePlan->getPdf($facilityId, $facilityNumber, $careDaisyFacilityUserId, $servicePlanId, $pagingNo);
            if (empty($response)) {
                return response()->warning('[W00001]条件に一致する介護計画書が存在しません。');
            }

            Log::channel('api')->notice('['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.'] api:SUCCESS');
            return $response;
        } catch (Exception $e) {
            report($e);
            return response()->error('[E00008]サーバ内で予期せぬエラーが発生いたしました。');
        }
    }
}
