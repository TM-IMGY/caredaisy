<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries\AutoServiceCodeGetInputBoundary;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\AutoServiceCodeGetRequest;

class AutoServiceCodeController extends Controller
{
    /**
     * @param AutoServiceCodeGetRequest $request
     * @param AutoServiceCodeGetInputBoundary $interactor
     */
    public function get(AutoServiceCodeGetRequest $request, AutoServiceCodeGetInputBoundary $interactor)
    {
        $outputData = $interactor->handle(
            $request->facility_id,
            $request->facility_user_id,
            $request->year,
            $request->month
        );
        return $outputData->getData();
    }
}
