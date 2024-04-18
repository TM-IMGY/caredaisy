<?php

namespace App\Http\Controllers\GroupHome\StaffInfo;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\StaffInfo\StaffRequest;
use App\Service\GroupHome\StaffService;

class StaffInfoController extends Controller
{
    public function index(){
        return view('group_home.staff_info.staff_info');
    }

    public function save(StaffRequest $request)
    {
        $params = [
            'staff_id' => $request->staff_id,
            'staff_history_id' => $request->staff_history_id,
            'facility_id' => $request->facility_id,
            'employee_number' => $request->employee_number,
            'date_of_employment' => $request->date_of_employment,
            'password' => $request->password,
            'password_changed' => $request->password_changed,
            'corporation_id' => $request->corporation_id,
            'institution_id' => $request->institution_id,
            'facility_id' => $request->facility_id,
            'name' => $request->name,
            'name_kana' => $request->name_kana,
            'gender' => $request->gender,
            'employment_status' => $request->employment_status,
            'employment_class' => $request->employment_class,
            'working_status' => $request->working_status,
            'location' => $request->location,
            'phone_number' => $request->phone_number,
            'emergency_contact_information' => $request->emergency_contact_information,
        ];
        
        $staffService = new StaffService();
        $res = $staffService->save($params);

        if ($res) {
            // var_dump($res);
            return $res;
        }
        abort(500);
    }
    /**
     * スタッフ履歴の取得
     */
    public function getStaffHistory(Request $request)
    {
        $params = [
            'staff_id' => $request->staff_id,
        ];
        
        $staffService = new StaffService();
        return $staffService->getStaffHistoryData($params);
    }
    /**
     * スタッフ一覧の取得
     */
    public function getStaffList(Request $request)
    {
        $params = [
            'facility_id' => $request->facility_id,
        ];
        
        $staffService = new StaffService();
        return $staffService->getStaffList($params);
    }
}
