<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\SpecialMedicalCodeGetRequest;
use App\Service\GroupHome\SpecialMedicalCodeService;

/**
 * 特別診療費コードのコントローラー。
 */
class SpecialMedicalCodeController extends Controller
{
    /**
     * 特別診療費コードを返す。
     * @param SpecialMedicalCodeGetRequest $request
     */
    public function get(SpecialMedicalCodeGetRequest $request)
    {
        $facilityId = $request->facility_id;
        $serviceTypeCode = $request->service_type_code;
        $year = $request->year;
        $month = $request->month;

        $service = new SpecialMedicalCodeService();
        $specialMedicalCodes = $service->get($facilityId, $serviceTypeCode, $year, $month);

        return $specialMedicalCodes;
    }
}
