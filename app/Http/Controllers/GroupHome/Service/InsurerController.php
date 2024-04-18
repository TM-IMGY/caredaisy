<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\InsurerGetRequest;
use App\Service\GroupHome\InsurerService;

class InsurerController extends Controller
{
  /**
   * @param InsurerGetRequest $request
   * @return array
   */
    public function get(InsurerGetRequest $request) : array
    {
        $service = new InsurerService();
        $data = $service->get($request->insurer_no, $request->year, $request->month);

        return $data;
    }
}
