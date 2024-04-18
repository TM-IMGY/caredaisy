<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Http\Controllers\Controller;
use App\Service\GroupHome\FacilityService;

class FacilityController extends Controller
{
    /**
     * 事業所データを返す
     */
    public function getRelatedData()
    {
        $service = new FacilityService();
        $data = $service->getRelatedData();
        return $data;
    }
}
