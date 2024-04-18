<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Http\Controllers\Controller;
use App\Service\GroupHome\CorporationService;

class CorporationController extends Controller
{
    /**
     * 事業所データを返す
     * @return Response
     */
    public function getRelatedData()
    {
        $service = new CorporationService();
        return $service->getRelatedData();
    }
}
