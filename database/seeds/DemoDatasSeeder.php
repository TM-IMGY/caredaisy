<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

use App\User;
use App\Models\CorporationAccount;
use App\Models\Corporation;
use App\Models\Institution;
use App\Models\Facility;
use App\Models\UserFacilityInformation;
use App\Models\FacilityUser;
use App\Models\UserBenefitInformation;
use App\Models\UserPublicExpenseInformation;
use App\Models\UserCareInformation;
use App\Models\UserIndependenceInformation;
use App\Models\Approval;
use App\Models\FacilityAddition;
use App\Models\Service;
use App\Models\UserFacilityServiceInformation;
use App\Models\CareReward;
use App\Models\CareRewardHistory;
use App\Models\UninsuredItem;
use App\Models\UninsuredItemHistory;
use App\Models\UninsuredRequest;
use App\Models\ServicePlan;
use App\Models\FirstServicePlan;
use App\Models\SecondServicePlan;
use App\Models\ServicePlanNeed;
use App\Models\ServiceLongPlan;
use App\Models\ServiceShortPlan;
use App\Models\ServicePlanSupport;

class DemoDatasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $columns = collect(['employee_number', 'password']);
        $values = collect([
            ['Lending_account01@9999999999.care-daisy.com', 'HKXg4XCvJw'],
            ['Lending_account02@9999999999.care-daisy.com', 'LPTWknHExh'],
            ['Lending_account03@9999999999.care-daisy.com', 'eJ5VJBokWc'],
            ['Lending_account04@9999999999.care-daisy.com', 'wunLp5J6Q7'],
            ['Lending_account05@9999999999.care-daisy.com', 'AmY4OxjnWY']
        ]);
        $users = $values->map(fn($x) => $columns->combine($x))->toArray();

        $columns = collect(['item', 'unit_cost', 'set_one', 'meal', 'daily_necessary']);
        $values = collect([
            ['朝食', 200, 1, 1, 0],
            ['昼食', 200, 1, 1, 0],
            ['夕食', 500, 1, 1, 0],
            ['おやつ', null, 1, 1, 0],
            ['日用品１', 300, 1, 0, 1]
        ]);
        $uninsured_items = $values->map(fn($x) => $columns->combine($x))->toArray();

        $columns = collect(['insurer_no', 'insured_no', 'last_name', 'first_name', 'last_name_kana', 'first_name_kana', 'postal_code', 'location1', 'location2', 'phone_number', 'cell_phone_number']);
        $values = collect([
            ['123456', '0000000101', '秋田', '敬久', 'アキタ', 'ノリヒサ', '123-4567', '東京都豊島区', '池袋 3-5-7', '03-1234-5678', '03-1234-5679'],
            ['123456', '0000000102', '茨城', '昌', 'イバラキ', 'マサル', '123-4567', '東京都豊島区', '池袋 3-5-7', '03-1234-5678', '03-1234-5679'],
            ['123456', '0000000103', '岩手', '拓也', 'イワテ', 'タクヤ', '123-4567', '東京都豊島区', '池袋 3-5-7', '03-1234-5678', '03-1234-5679'],
            ['123456', '0000000104', '熊本', '郁夫', 'クマモト', 'イクオ', '123-4567', '東京都豊島区', '池袋 3-5-7', '03-1234-5678', '03-1234-5679']
        ]);
        $facility_users = $values->map(fn($x) => $columns->combine($x))->toArray();

        $id = 0;//DBにデータがないなら0スタート
        foreach ($users as $key => $value) {
            $id++;
            $facility_user_id = $id + 100;
            factory(User::class)->create([
                'password' => Hash::make($value['password']),
                'employee_number' => $value['employee_number'],
                'account_id' => $id
            ]);

            factory(Corporation::class)->create([
                'id' => $id,
                'name' => '株式会社 池袋まどぐち',
                'abbreviation' => ' (株)池袋まどぐち',
                'representative' => '介護 太郎',
                'phone_number' => '03-0123-4567',
                'fax_number' => '03-0123-4589',
                'postal_code' => '171-0014',
                'location' => '東京都豊島区池袋2-43-1',
                'remarks' => NULL
            ]);

            factory(CorporationAccount::class)->create([
                'account_id'=> $id,
                'corporation_id' => $id
            ]);

            factory(Institution::class)->create([
                'id' => $id,
                'corporation_id' => $id,
                'name' => 'グループホーム池袋まどぐち',
                'abbreviation' => ' 池袋まどぐち',
                'representative' => '介護 次郎',
                'phone_number' => '03-0123-4567',
                'fax_number' => '03-0123-4589',
                'postal_code' => '171-0014',
                'location' => '東京都豊島区池袋2-43-1',
                'remarks' => NULL

            ]);

            factory(Facility::class)->create([
                'facility_id' => $id,
                'institution_id' => $id,
                'facility_number' => 100000000 + $id,
                'facility_name_kanji' => 'グループホーム池袋まどぐち',
                'facility_name_kana' => 'グループホームイケブクロマドグチ',
                'insurer_no' => 100000,
                'area' => '5',
                'postal_code' => '171-0014',
                'location' => '東京都豊島区池袋2-43-1',
                'phone_number' => '03-0123-4567',
                'fax_number' => '03-0123-4589',
                'remarks' => NULL,
                'invalid_flag' => '0',
                'abbreviation' => '池袋まどぐち',
                'facility_manager' => '介護 三郎'
            ]);

            factory(FacilityUser::class)->create([
                'facility_user_id' => $facility_user_id,
                'insured_no' => Crypt::encrypt('0000000001'),
                'insurer_no' => Crypt::encrypt('123456'),
                'last_name' => Crypt::encrypt('青森'),
                'first_name' => Crypt::encrypt('申吾'),
                'last_name_kana' => Crypt::encrypt('アオモリ'),
                'first_name_kana' => Crypt::encrypt('シンゴ'),
                'location1' => Crypt::encrypt('東京都豊島区'),
                'location2' => Crypt::encrypt('池袋 3-5-7'),
                'phone_number' => Crypt::encrypt('03-1234-5678'),
                'cell_phone_number' => Crypt::encrypt('03-1234-5679'),
                'end_date' => NULL,
                'death_date' => NULL,
                'death_reason' => NULL,
                'diagnosis_date' => NULL,
                'diagnostician' => NULL,
                'consent_date' => NULL,
                'consenter' => NULL,
                'consenter_phone_number' => NULL
            ]);
            factory(UserFacilityInformation::class)->create([
                'facility_user_id'=> $facility_user_id,
                'facility_id' => $id
            ]);

            $user_count = 0;
            foreach ($facility_users as $user_key => $user_value) {
                $user_count++;
                factory(FacilityUser::class)->create([
                    'facility_user_id' => $id * 10 + $user_count,
                    'insurer_no' => Crypt::encrypt($user_value['insurer_no']),
                    'insured_no' => Crypt::encrypt($user_value['insured_no']),
                    'last_name' => Crypt::encrypt($user_value['last_name']),
                    'first_name' => Crypt::encrypt($user_value['first_name']),
                    'last_name_kana' => Crypt::encrypt($user_value['last_name_kana']),
                    'first_name_kana' => Crypt::encrypt($user_value['first_name_kana']),
                    'postal_code' => Crypt::encrypt($user_value['postal_code']),
                    'location1' => Crypt::encrypt($user_value['location1']),
                    'location2' => Crypt::encrypt($user_value['location2']),
                    'phone_number' => Crypt::encrypt($user_value['phone_number']),
                    'cell_phone_number' => Crypt::encrypt($user_value['cell_phone_number']),
                    'end_date' => NULL,
                    'death_date' => NULL,
                    'death_reason' => NULL,
                    'diagnosis_date' => NULL,
                    'diagnostician' => NULL,
                    'consent_date' => NULL,
                    'consenter' => NULL,
                    'consenter_phone_number' => NULL
                ]);
                factory(UserFacilityInformation::class)->create([
                    'facility_user_id'=> $id * 10 + $user_count,
                    'facility_id' => $id
                ]);
            }




            factory(UserBenefitInformation::class)->create([
                'facility_user_id'=> $facility_user_id
            ]);

            factory(UserPublicExpenseInformation::class)->create([
                'facility_user_id'=> $facility_user_id
            ]);
            factory(UserCareInformation::class)->create([
                'facility_user_id'=> $facility_user_id
            ]);
            factory(UserIndependenceInformation::class)->create([
                'facility_user_id'=> $facility_user_id
            ]);
            factory(Approval::class)->create([
                'facility_user_id'=> $facility_user_id,
                'facility_id' => $id
            ]);
            factory(FacilityAddition::class)->create([
                'facility_id'=> $id
            ]);

            $service = factory(Service::class)->create([
                'facility_id'=> $id
            ]);

            factory(UserFacilityServiceInformation::class)->create([
                'facility_user_id'=> $facility_user_id,
                'facility_id'=> $id,
                'service_id'=> $service->id
            ]);
            $carereward = factory(CareReward::class)->create([
                'service_id'=> $service->id
            ]);
            factory(CareRewardHistory::class)->create([
                'care_reward_id'=> $carereward->id
            ]);
            $uninsured_item = factory(UninsuredItem::class)->create([
                'service_id'=> $service->id
            ]);
            foreach ($uninsured_items as $k => $v) {
                $uninsured_item_history = factory(UninsuredItemHistory::class)->create([
                    'uninsured_item_id'=> $uninsured_item->id,
                    'item' => $v['item'],
                    'unit_cost' => $v['unit_cost'],
                    'set_one' => $v['set_one'],
                    'meal' => $v['meal'],
                    'daily_necessary' => $v['daily_necessary']
                ]);
                //手動で登録して下さい。
                // factory(UninsuredRequest::class)->create([
                //     'uninsured_item_history_id'=> $uninsured_item_history->id,
                //     'facility_user_id'=> $id
                // ]);
                //factory(UninsuredRequestDetail::class)->create();
            }


            $service_plan = factory(ServicePlan::class)->create([
                'facility_user_id'=> $facility_user_id
            ]);
            factory(FirstServicePlan::class)->create([
                'service_plan_id'=> $service_plan->id
            ]);
            $second_service_plan = factory(SecondServicePlan::class)->create([
                'service_plan_id'=> $service_plan->id
            ]);
            $service_plan_need = factory(ServicePlanNeed::class)->create([
                'second_service_plan_id'=> $second_service_plan->id
            ]);
            $service_long_plan = factory(ServiceLongPlan::class)->create([
                'service_plan_need_id'=> $service_plan_need->id
            ]);
            $service_short_plan = factory(ServiceShortPlan::class)->create([
                'service_long_plan_id'=> $service_long_plan->id
            ]);
            factory(ServicePlanSupport::class)->create([
                'service_short_plan_id'=> $service_short_plan->id
            ]);
            factory(ServicePlanSupport::class)->create([
                'service_short_plan_id'=> $service_short_plan->id,
                'service' => '②週3回夜間歩行でのトイレ誘導声掛けする',
                'staff' => '介護職員'
            ]);
        }
    }
}
