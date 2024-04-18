<?php

namespace App\Http\Controllers\GroupHome\StaffInfo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GroupHome\StaffInfo\AuthExtentRequest;
use App\Service\GroupHome\AuthExtentService;

class AuthExtentController extends Controller
{
    public function save(AuthExtentRequest $request)
    {
        $params = [
            'staff_id' => $request->staff_id,
            'auth_extent_id' => $request->auth_extent_id,
            'corporation_id' => $request->corporation_id,
            'institution_id' => $request->institution_id,
            'facility_id' => $request->facility_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'administrator' => $request->administrator,
            'claimant' => $request->claimant,
            'planner' => $request->planner,
        ];

        $authExtentService = new AuthExtentService();
        $res = $authExtentService->save($params);

        if ($res) {
            // var_dump($res);
            return $res;
        }
        abort(500);
    }
    
    /**
     * 一覧の取得
     */
    public function getAuthExtent(Request $request)
    {
        $params = [
            'staff_id' => $request->staff_id,
        ];
        
        $authExtentService = new AuthExtentService();
        return $authExtentService->getAuthExtentHistoryData($params);
    }
}
