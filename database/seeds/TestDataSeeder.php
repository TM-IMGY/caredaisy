<?php

use App\Models\FirstServicePlan;
use App\Models\ServicePlan;
use App\Models\StayOutManagement;
use App\Utility\SeedingUtility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Carbon\CarbonImmutable;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // i_staffs
        $staffData = SeedingUtility::getData('database/seeding_src/test_data/i_staffs.csv');
        for ($i = 0, $cnt = count($staffData); $i < $cnt; $i++) {
            $staffData[$i]['employee_number'] = Crypt::encrypt($staffData[$i]['employee_number']);
            DB::table('i_staffs')->insert($staffData[$i]);
        }

        // i_accounts
        $accountData = SeedingUtility::getData('database/seeding_src/test_data/i_accounts.csv');
        for ($i = 0, $cnt = count($accountData); $i < $cnt; $i++) {
            $accountData[$i]['password'] = Hash::make($accountData[$i]['password']);
            DB::table('i_accounts')->insert($accountData[$i]);
        }

        // i_corporations
        $corporationData = SeedingUtility::getData('database/seeding_src/test_data/i_corporations.csv');
        for ($i = 0, $cnt = count($corporationData); $i < $cnt; $i++) {
            DB::table('i_corporations')->insert($corporationData[$i]);
        }

        // corporation_account
        $corporationAccountData = SeedingUtility::getData('database/seeding_src/test_data/corporation_account.csv');
        for ($i = 0, $cnt = count($corporationAccountData); $i < $cnt; $i++) {
            DB::table('corporation_account')->insert($corporationAccountData[$i]);
        }

        // i_institutions
        $institutionData = SeedingUtility::getData('database/seeding_src/test_data/i_institutions.csv');
        for ($i = 0, $cnt = count($institutionData); $i < $cnt; $i++) {
            DB::table('i_institutions')->insert($institutionData[$i]);
        }

        // i_facilities
        $facilityData = SeedingUtility::getData('database/seeding_src/test_data/i_facilities.csv');
        for ($i = 0, $cnt = count($facilityData); $i < $cnt; $i++) {
            DB::table('i_facilities')->insert($facilityData[$i]);
        }

        // i_staff_histories
        $staffHistoryData = SeedingUtility::getData('database/seeding_src/test_data/i_staff_histories.csv');
        for ($i = 0, $cnt = count($staffHistoryData); $i < $cnt; $i++) {
            $staffHistoryData[$i]['emergency_contact_information'] = Crypt::encrypt($staffHistoryData[$i]['emergency_contact_information']);
            $staffHistoryData[$i]['location'] = Crypt::encrypt($staffHistoryData[$i]['location']);
            $staffHistoryData[$i]['name'] = Crypt::encrypt($staffHistoryData[$i]['name']);
            $staffHistoryData[$i]['name_kana'] = Crypt::encrypt($staffHistoryData[$i]['name_kana']);
            $staffHistoryData[$i]['phone_number'] = Crypt::encrypt($staffHistoryData[$i]['phone_number']);
            DB::table('i_staff_histories')->insert($staffHistoryData[$i]);
        }

        // i_auth_extents
        $authExtentsData = SeedingUtility::getData('database/seeding_src/test_data/i_auth_extents.csv');
        for ($i = 0, $cnt = count($authExtentsData); $i < $cnt; $i++) {
            DB::table('i_auth_extents')->insert($authExtentsData[$i]);
        }

        // i_services
        $serviceData = SeedingUtility::getData('database/seeding_src/test_data/i_services.csv');
        for ($i = 0, $cnt = count($serviceData); $i < $cnt; $i++) {
            DB::table('i_services')->insert($serviceData[$i]);
        }

        // i_care_rewards
        $careRewardData = SeedingUtility::getData('database/seeding_src/test_data/i_care_rewards.csv');
        for ($i = 0, $cnt = count($careRewardData); $i < $cnt; $i++) {
            DB::table('i_care_rewards')->insert($careRewardData[$i]);
        }

        // i_care_reward_histories
        $careRewardHistoryData = SeedingUtility::getData('database/seeding_src/test_data/i_care_reward_histories.csv');
        for ($i = 0, $cnt = count($careRewardHistoryData); $i < $cnt; $i++) {
            DB::table('i_care_reward_histories')->insert($careRewardHistoryData[$i]);
        }

        // m_facility_additions
        $facilityAdditionData = SeedingUtility::getData('database/seeding_src/test_data/m_facility_additions.csv');
        for ($i = 0, $cnt = count($facilityAdditionData); $i < $cnt; $i++) {
            DB::table('m_facility_additions')->insert($facilityAdditionData[$i]);
        }

        // i_facility_users
        $facilityUserData = SeedingUtility::getData('database/seeding_src/test_data/i_facility_users.csv');
        for ($i = 0, $cnt = count($facilityUserData); $i < $cnt; $i++) {
            // TODO: 見づらければEloquentから作成する。
            $facilityUserData[$i]['insurer_no'] = Crypt::encrypt($facilityUserData[$i]['insurer_no']);
            $facilityUserData[$i]['insured_no'] = Crypt::encrypt($facilityUserData[$i]['insured_no']);
            $facilityUserData[$i]['last_name'] = Crypt::encrypt($facilityUserData[$i]['last_name']);
            $facilityUserData[$i]['first_name'] = Crypt::encrypt($facilityUserData[$i]['first_name']);
            $facilityUserData[$i]['last_name_kana'] = Crypt::encrypt($facilityUserData[$i]['last_name_kana']);
            $facilityUserData[$i]['first_name_kana'] = Crypt::encrypt($facilityUserData[$i]['first_name_kana']);
            $facilityUserData[$i]['postal_code'] = Crypt::encrypt($facilityUserData[$i]['postal_code']);
            $facilityUserData[$i]['location1'] = Crypt::encrypt($facilityUserData[$i]['location1']);
            $facilityUserData[$i]['location2'] = Crypt::encrypt($facilityUserData[$i]['location2']);
            $facilityUserData[$i]['phone_number'] = Crypt::encrypt($facilityUserData[$i]['phone_number']);
            $facilityUserData[$i]['cell_phone_number'] = Crypt::encrypt($facilityUserData[$i]['cell_phone_number']);

            if (array_key_exists('diagnostician', $facilityUserData[$i])) {
                $facilityUserData[$i]['diagnostician'] = Crypt::encrypt($facilityUserData[$i]['diagnostician']);
            }

            if (array_key_exists('consenter', $facilityUserData[$i])) {
                $facilityUserData[$i]['consenter'] = Crypt::encrypt($facilityUserData[$i]['consenter']);
            }

            if (array_key_exists('consenter_phone_number', $facilityUserData[$i])) {
                $facilityUserData[$i]['consenter_phone_number'] = Crypt::encrypt($facilityUserData[$i]['consenter_phone_number']);
            }

            DB::connection('confidential')->table('i_facility_users')->insert($facilityUserData[$i]);
        }

        // i_stay_out_managements
        $stayOutManagements = SeedingUtility::getData('database/seeding_src/test_data/i_stay_out_managements.csv');
        for ($i = 0, $cnt = count($stayOutManagements); $i < $cnt; $i++) {
            DB::table('i_stay_out_managements')->insert($stayOutManagements[$i]);
        }

        // i_user_benefit_informations
        $userBenefitInformationData = SeedingUtility::getData('database/seeding_src/test_data/i_user_benefit_informations.csv');
        for ($i = 0, $cnt = count($userBenefitInformationData); $i < $cnt; $i++) {
            DB::table('i_user_benefit_informations')->insert($userBenefitInformationData[$i]);
        }

        // i_user_care_informations
        $userCareInformationData = SeedingUtility::getData('database/seeding_src/test_data/i_user_care_informations.csv');
        for ($i = 0, $cnt = count($userCareInformationData); $i < $cnt; $i++) {
            DB::table('i_user_care_informations')->insert($userCareInformationData[$i]);
        }

        // i_user_facility_informations
        $userFacilityInformationData = SeedingUtility::getData('database/seeding_src/test_data/i_user_facility_informations.csv');
        for ($i = 0, $cnt = count($userFacilityInformationData); $i < $cnt; $i++) {
            DB::table('i_user_facility_informations')->insert($userFacilityInformationData[$i]);
        }

        // i_user_facility_service_informations
        $userFacilityServiceInformationData = SeedingUtility::getData('database/seeding_src/test_data/i_user_facility_service_informations.csv');
        for ($i = 0, $cnt = count($userFacilityServiceInformationData); $i < $cnt; $i++) {
            DB::table('i_user_facility_service_informations')->insert($userFacilityServiceInformationData[$i]);
        }

        // i_user_independence_informations
        $userIndependenceInformationData = SeedingUtility::getData('database/seeding_src/test_data/i_user_independence_informations.csv');
        for ($i = 0, $cnt = count($userIndependenceInformationData); $i < $cnt; $i++) {
            DB::table('i_user_independence_informations')->insert($userIndependenceInformationData[$i]);
        }

        // i_user_public_expense_informations
        $userPublicExpenseInformationData = SeedingUtility::getData('database/seeding_src/test_data/i_user_public_expense_informations.csv');
        for ($i = 0, $cnt = count($userPublicExpenseInformationData); $i < $cnt; $i++) {
            DB::table('i_user_public_expense_informations')->insert($userPublicExpenseInformationData[$i]);
        }

        // i_service_results
        $serviceResultData = SeedingUtility::getData('database/seeding_src/test_data/i_service_results.csv');
        for ($i = 0, $cnt = count($serviceResultData); $i < $cnt; $i++) {
            DB::table('i_service_results')->insert($serviceResultData[$i]);
        }

        // basic_remarks
        $basicRemarksData = SeedingUtility::getData('database/seeding_src/test_data/basic_remarks.csv');
        for ($i = 0, $cnt = count($basicRemarksData); $i < $cnt; $i++) {
            DB::table('basic_remarks')->insert($basicRemarksData[$i]);
        }

        // injuries_sicknesses
        $injuriesSicknessData = SeedingUtility::getData('database/seeding_src/test_data/injuries_sicknesses.csv');
        for ($i = 0, $cnt = count($injuriesSicknessData); $i < $cnt; $i++) {
            DB::table('injuries_sicknesses')->insert($injuriesSicknessData[$i]);
        }

        // injuries_sickness_details
        $injuriesSicknessDetailData = SeedingUtility::getData('database/seeding_src/test_data/injuries_sickness_details.csv');
        for ($i = 0, $cnt = count($injuriesSicknessDetailData); $i < $cnt; $i++) {
            DB::table('injuries_sickness_details')->insert($injuriesSicknessDetailData[$i]);
        }

        // injuries_sickness_relations
        $injuriesSicknessRelationData = SeedingUtility::getData('database/seeding_src/test_data/injuries_sickness_relations.csv');
        for ($i = 0, $cnt = count($injuriesSicknessRelationData); $i < $cnt; $i++) {
            DB::table('injuries_sickness_relations')->insert($injuriesSicknessRelationData[$i]);
        }

        // i_invoices
        $InvoicesData = SeedingUtility::getData('database/seeding_src/test_data/i_invoices.csv');
        for ($i = 0, $cnt = count($InvoicesData); $i < $cnt; $i++) {
            DB::table('i_invoices')->insert($InvoicesData[$i]);
        }

        // i_uninsured_items
        $uninsuredItemsData = SeedingUtility::getData('database/seeding_src/test_data/i_uninsured_items.csv');
        for ($i = 0, $cnt = count($uninsuredItemsData); $i < $cnt; $i++) {
            DB::table('i_uninsured_items')->insert($uninsuredItemsData[$i]);
        }

        // i_uninsured_item_histories
        $uninsuredItemHistoriesData = SeedingUtility::getData('database/seeding_src/test_data/i_uninsured_item_histories.csv');
        for ($i = 0, $cnt = count($uninsuredItemHistoriesData); $i < $cnt; $i++) {
            DB::table('i_uninsured_item_histories')->insert($uninsuredItemHistoriesData[$i]);
        }

        $model = factory(ServicePlan::class)->create([
            'facility_user_id' => 1,
            'plan_start_period' => '2021/11/01',
            'plan_end_period' => '介護花子a',
            'status' => 1,
            'certification_status' => 2,
            'recognition_date' => '2021/11/01',
            'care_period_start' => '2021/11/01',
            'care_period_end' => '2024/10/31',
            'care_level_name' => '要介護３',
            'independence_level' => 2,
            'dementia_level' => 7
        ]);

        factory(FirstServicePlan::class)->create([
            'service_plan_id' => $model->id
        ]);

        factory(StayOutManagement::class)->create([
            'facility_user_id' => 1,
            'start_date' =>  CarbonImmutable::now()->day(3),
            'end_date' => CarbonImmutable::now()->day(13),
        ]);

        // i_service_plans
        $servicePlansData = SeedingUtility::getData('database/seeding_src/test_data/i_service_plans.csv');
        for ($i = 0, $cnt = count($servicePlansData); $i < $cnt; $i++) {
            DB::table('i_service_plans')->insert($servicePlansData[$i]);
        }

        // i_first_service_plans
        $firstServicePlansData = SeedingUtility::getData('database/seeding_src/test_data/i_first_service_plans.csv');
        for ($i = 0, $cnt = count($firstServicePlansData); $i < $cnt; $i++) {
            DB::table('i_first_service_plans')->insert($firstServicePlansData[$i]);
        }

        // facility_user_burden_limits
        $facilityUserBurdenLimitsData = SeedingUtility::getData('database/seeding_src/test_data/facility_user_burden_limits.csv');
        for ($i = 0, $cnt = count($facilityUserBurdenLimitsData); $i < $cnt; $i++) {
            DB::table('facility_user_burden_limits')->insert($facilityUserBurdenLimitsData[$i]);
        }

        $this->insertRegacyUser();
    }

    /**
     * 移行段階につきレガシーとして残したGH00002ユーザーを作成する。
     * TODO: 段階を見て削除する。
     */
    public function insertRegacyUser()
    {
        $timestamp = new DateTime();

        $staffId = DB::table('i_staffs')->insertGetId([
            'employee_number' => Crypt::encrypt('9999999'),
            'date_of_employment' => '2021/04/01 0:00:00'
        ]);

        $accountId = DB::table('i_accounts')->insertGetId([
            'employee_number' => 'GH00002',
            'password' => '$2y$10$aoggcw91YUvQtp6FW.9BC.jA41qVk.5wNFSyrMPgCXNhvdROBzkna', // GH00002
            'account_name' => 'グループホーム テスト2',
            'auth_id' => 1,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'staff_id' => $staffId
        ]);

        // i_corporationsにデータを挿入する
        $corporationId = DB::table('i_corporations')->insertGetId([
            'name' => '有限会社　サポートライフ西湘',
            'abbreviation' => '(有)サポートライフ西湘',
            'representative' => '原　博文',
            'phone_number' => '0465-35-2856',
            'fax_number' => '',
            'postal_code' => '250-0002',
            'location' => '神奈川県小田原市寿町４－１４－１９',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        // i_institutionsにデータを挿入する
        $institutionId = DB::table('i_institutions')->insertGetId([
            'corporation_id' => $corporationId,
            'name' => 'グループホームローズハウス',
            'abbreviation' => 'ローズハウス',
            'representative' => '岸　豪基',
            'phone_number' => '0465-35-2856',
            'fax_number' => '0465-35-5130',
            'postal_code' => '250-0002',
            'location' => '神奈川県小田原市寿町４－１４－１９',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        // i_facilitiesにデータを挿入する
        $facilityId = DB::table('i_facilities')->insertGetId([
            'facility_number' => '1472301025',
            'facility_name_kanji' => 'グループホームローズハウス',
            'facility_name_kana' => 'グループホームローズハウス',
            'insurer_no' => '140079',
            'area' => '5',
            'postal_code' => '250-0002',
            'location' => '神奈川県小田原市寿町４－１４－１９',
            'phone_number' => '0465-35-2856',
            'fax_number' => '0465-35-5130',
            'remarks' => '',
            'invalid_flag' => '0',
            'abbreviation' => 'ローズハウス',
            'institution_id' => $institutionId,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $staffId = DB::table('i_staff_histories')->insertGetId([
            'staff_id' => $staffId,
            'corporation_id' => $corporationId,
            'institution_id' => $institutionId,
            'facility_id' => $facilityId,
            'name' => Crypt::encrypt('GH00002'),
            'name_kana' => Crypt::encrypt('GH00002'),
            'gender' => '1',
            'employment_status' => '1',
            'employment_class' => '1',
            'working_status' => '1',
            'location' => Crypt::encrypt('GH00002住所'),
            'phone_number' => Crypt::encrypt('0000000000'),
            'emergency_contact_information' => Crypt::encrypt('GH00002住所')
        ]);

        $staffId = DB::table('i_auth_extents')->insertGetId([
            'staff_id' => $staffId,
            'auth_id' => '1',
            'corporation_id' => $corporationId,
            'institution_id' => $institutionId,
            'facility_id' => $facilityId,
            'start_date' => '2021/01/01 0:00:00',
        ]);

        // corporation_account
        DB::table('corporation_account')->insertGetId([
            'account_id' => $accountId,
            'corporation_id' => $corporationId,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        // i_services
        $serviceId = DB::table('i_services')->insertGetId([
            'facility_id' => $facilityId,
            'service_type_code_id' => 1,
            'area' => '5',
            'change_date' => '2021/9/24',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        // i_care_rewards
        $careRewardId = DB::table('i_care_rewards')->insertGetId([
            'service_id' => $serviceId,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        // i_care_reward_histories
        $careRewardHistoryId = DB::table('i_care_reward_histories')->insert([
            'care_reward_id' => $careRewardId,
            'start_month' => '2021/9/1',
            'end_month' => '9999/12/31',
            'section' => '2',
            'vacancy' => '1',
            'night_shift' => '1',
            'night_care' => '1',
            'juvenile_dementia' => '1',
            'nursing_care' => '2',
            'medical_cooperation' => '2',
            'dementia_specialty' => '2',
            'strengthen_service_system' => '1',
            'treatment_improvement' => '2',
            'night_care_over_capacity' => '1',
            'improvement_of_living_function' => '1',
            'improvement_of_specific_treatment' => '3',
            'emergency_response' => '1',
            'over_capacity' => '1',
            'physical_restraint' => '1',
            'initial' => '1',
            'consultation' => '1',
            'nutrition_management' => '1',
            'oral_hygiene_management' => '1',
            'oral_screening' => '1',
            'scientific_nursing' => '1',
            'hospitalization_cost' => '2',
            'discount' => '1',
            'covid-19' => '1',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
