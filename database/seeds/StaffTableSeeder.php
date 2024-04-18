<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

use App\Models\Staff;
use App\Models\StaffHistory;
use App\Models\AuthExtent;

class StaffTableSeeder extends Seeder
{
    const NAME = '管理者';
    const NAKE_KANA = 'カンリシャ';
    /**
     * Run the database seeds.
     *　スタッフ未設定アカウントに管理者設定のスタッフを追加する
     * @return void
     */
    public function run()
    {
        //管理者権限はauth_id：1のため固定設定
        $auth_id = 1;

        //api用アカウント・管理者アカウントは権限作成を実施しない
        $users = App\User::where('staff_id', null)
                        ->where('employee_number', 'NOT LIKE', '%api%')
                        ->where('auth_id','<>','99')
                        ->get();
        foreach ($users as $key => $user) {

            //アカウントに紐づく法人・施設IDを取得
            $corporation_account = App\Models\CorporationAccount::where('account_id',$user->account_id)->first();
            $institutions = App\Models\Institution::where('corporation_id',$corporation_account->corporation_id)->get();
            $staff = factory(Staff::class)->create([
                     'employee_number' => Crypt::encrypt($user->employee_number)
            ]);

            foreach($institutions as $institution) {
                    //施設IDに紐づく事業所IDを取得
                    $facility = App\Models\Facility::where('institution_id',$institution->id)->first();

                    //アカウントのstaff_idを更新
                    $user->fill([
                        'staff_id' => $staff->id
                    ]);
                    $user->save();

                   factory(StaffHistory::class)->create([
                        'staff_id' => $staff->id,
                        'facility_id' => $facility->facility_id,
                        'name' => Crypt::encrypt(self::NAME),
                        'name_kana' => Crypt::encrypt(self::NAKE_KANA),
                    ]);
                    factory(AuthExtent::class)->create([
                        'staff_id' => $staff->id,
                        'corporation_id' => $corporation_account->corporation_id,
                        'institution_id' => $institution->id,
                        'facility_id' => $facility->facility_id,
                        'auth_id' => $auth_id,
                    ]);
             }
        }
    }
}