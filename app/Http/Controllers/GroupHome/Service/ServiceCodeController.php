<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\ServiceCodeGetServiceCodeRequest;
use App\Http\Requests\GroupHome\Service\ServiceCodeListIncompetentResidentsRequest;
use App\Service\GroupHome\ServiceCodeService;

/**
 * ブラウザからのサービスコードに関するリクエストを捌くコントローラー。
 */
class ServiceCodeController extends Controller
{
    /**
     * サービスコードを返す
     * @param ServiceCodeGetServiceCodeRequest $request
     */
    public function getServiceCodes(ServiceCodeGetServiceCodeRequest $request)
    {
        // パラメーターを取得する
        $params = [
            'service_type_code' => $request->service_type_code,
            'year' => $request->year,
            'month' => $request->month,
        ];

        $serviceCodeService = new ServiceCodeService();
        $serviceCodes = $serviceCodeService->getServiceCodes($params);

        return $serviceCodes;
    }

    /**
     * 特定入所者サービスコードを返す
     * @param ServiceCodeListIncompetentResidentsRequest $request
     */
    public function listIncompetentResidents(ServiceCodeListIncompetentResidentsRequest $request)
    {
        $serviceCodeService = new ServiceCodeService();
        $incompetentResidents = $serviceCodeService->listIncompetentResidents($request->year, $request->month);
        return $incompetentResidents;
    }
}
