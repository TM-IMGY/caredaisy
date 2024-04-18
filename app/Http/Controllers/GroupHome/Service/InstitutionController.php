<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Http\Controllers\Controller;
use App\Service\GroupHome\InstitutionService;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    /**
     * 事業所データを返す
     * @return Response
     */
    public function getRelatedData(Request $request)
    {
        $service = new InstitutionService();
        return $service->getRelatedData($request);
    }
}
