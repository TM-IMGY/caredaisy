<?php

namespace App\Service\GroupHome;

use App\Models\Staff;
use App\Models\StaffHistory;
use App\Models\Facility;
use App\Models\Institution;
use App\Models\CorporationAccount;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class StaffService
{
    
    public function save($params)
    {
        $account_params = [
            'employee_number' => $params['employee_number'] . '@' . self::getFacilityNumber($params['facility_id']) . '.care-daisy.com',
            'account_name' => $params['name'],
            'auth_id' => 1, //暫定で1固定
        ];

        // 暗号化
        $params = Staff::encryptStaff($params);
        $params = StaffHistory::encryptStaffHistory($params);

        $staff_params = [
            'employee_number' => $params['employee_number'],
            'date_of_employment' => $params['date_of_employment'],
        ];
        $staff_history_params = [
            'facility_id' => $params['facility_id'],
            'name' => $params['name'],
            'name_kana' => $params['name_kana'],
            'gender' => $params['gender'],
            'employment_class' => $params['employment_class'],
            'working_status' => $params['working_status'],
            'location' => $params['location'],
            'phone_number' => $params['phone_number'],
            'emergency_contact_information' => $params['emergency_contact_information'],
        ];


        //画面で変更されたパスワードがあれば追加
        if ($params['password_changed']) {
            $account_params['password'] = password_hash($params['password'], PASSWORD_DEFAULT) ;
        }
        $staff_row = Staff::where([
                ['id',$params["staff_id"]]
            ])->first();
        $staff_history_row = StaffHistory::where([
                ['id',$params["staff_history_id"]]
            ])->first();
        $account_row = User::where([
                ['staff_id',$params["staff_id"]]
            ])->first();

        \DB::beginTransaction();
        try {
            if (!$staff_row) {
                $staff = Staff::create($staff_params);
                $staff_history = new StaffHistory($staff_history_params);
                $staff->history()->save($staff_history);
                $account = new User($account_params);
                $staff->account()->save($account);
                $corporation_account = CorporationAccount::create([
                    'account_id' => $account->account_id ,
                    'corporation_id' => self::getCooporationID($params['facility_id'])
                ]);
            } else {
                $staff_row->update($staff_params);
                $staff = $staff_row;
                $account_row->update($account_params);
                $account = $account_row;

                if (!$staff_history_row) {
                    $staff_history = new StaffHistory($staff_history_params);
                    Staff::find($params["staff_id"])->history()->save($staff_history);
                } else {
                    $staff_history_row->update($staff_history_params);
                    $staff_history = $staff_history_row;
                }
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
        $staff_histories = StaffHistory::where([
            ['staff_id',$params["staff_id"]]
        ])->get();
        return ['staff_history' => $staff_histories,'staff' => $staff, 'account' => $account, 'staff_history_id' => $staff_history->id];
    }
    private function getFacilityNumber($facility_id)
    {
        return Facility::find($facility_id)->facility_number;
    }
    private function getInstitutionID($facility_id)
    {
        return Facility::find($facility_id)->institution_id;
    }
    private function getCooporationID($facility_id)
    {
        return Institution::find(self::getInstitutionID($facility_id))->corporation_id;
    }
    /**
     * スタッフ履歴を取得
     *
     * @param int $staff_id
     * @return staff object
     */
    public function getStaffHistoryData($params)
    {
        $staff = Staff::with(['history', 'account'])->find($params['staff_id']);
        return  $staff;
    }
    /**
     * スタッフ一覧の取得
     * @param int $facility_id
     * @return staff objects
     */
    public function getStaffList($params)
    {
        $latestStaffHistory = \DB::table('i_staff_histories')->select('staff_id', \DB::raw('MAX(id) as last_id'))->groupBy('staff_id');
        $latestAuthExtentHistory = \DB::table('i_auth_extents')->select('staff_id as extent_staff_id', \DB::raw('MAX(id) as last_extent_id'))->groupBy('staff_id');
        $staff = \DB::table('i_staff_histories')->joinSub($latestStaffHistory, 'latestStaff', function($join){
                    $join->on('i_staff_histories.id', '=', 'latestStaff.last_id');
        })->where('facility_id', '=', $params['facility_id'])
                ->leftjoinSub($latestAuthExtentHistory, 'latestExtent', 'i_staff_histories.staff_id', 'latestExtent.extent_staff_id')
                ->select('name', 'latestStaff.staff_id', 'last_id', 'last_extent_id')
                ->orderBy('name_kana')
                ->get();
        foreach ($staff as $value) {
            $value->name = Crypt::decrypt($value->name);
        }
        return $staff;
    }
}
