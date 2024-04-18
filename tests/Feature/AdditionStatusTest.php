<?php

namespace Tests\Feature;

use App\User;
use Auth;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\CareReward;
use App\Models\CareRewardHistory;

/**
 *
 */
class AdditionStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function dataProvider()
    {
        return [
            "type32" => [
                [
                    "facility_id" => 6,
                    "start_date" => null,
                    "end_date" => null,
                    "service_id" => 21,
                    "service_type_code_id" => 1
                ],
                [
                    "start_month" => "2023-04-01",
                    "end_month" => "2023-05-31",
                    'section' => "1",
                    'vacancy' => "1",
                    'night_shift' => "1",
                    'physical_restraint' => "1",
                    'night_care_over_capacity' => "1",
                    'night_care' => "1",
                    'juvenile_dementia' => "1",
                    'hospitalization_cost' => "1",
                    'nursing_care' => "1",
                    'initial' => "1",
                    'medical_cooperation' => "1",
                    'consultation' => "1",
                    'dementia_specialty' => "1",
                    'improvement_of_living_function' => "1",
                    'nutrition_management' => "2",
                    'oral_hygiene_management' => "2",
                    'oral_screening' => "1",
                    'scientific_nursing' => "2",
                    'strengthen_service_system' => "1",
                    'treatment_improvement' => "2",
                    'improvement_of_specific_treatment' => "2",
                    'over_capacity' => "1",
                    'baseup' => "2",
                    "discount" => "1"
                ],
                [
                    [49, 50, 52, 58, 63, 2390]
                ]
            ],
            "type37" => [
                [
                    "facility_id" => 6,
                    "start_date" => null,
                    "end_date" => null,
                    "service_id" => 22,
                    "service_type_code_id" => 2
                ],
                [
                    "start_month" => "2023-04-01",
                    "end_month" => "2023-05-31",
                    "section" => "1",
                    "vacancy" => "1",
                    "night_shift" => "1",
                    "physical_restraint" => "1",
                    "night_care_over_capacity" => "1",
                    "night_care" => "1",
                    "juvenile_dementia" => "1",
                    "hospitalization_cost" => "1",
                    "initial" => "1",
                    "consultation" => "1",
                    "dementia_specialty" => "1",
                    "improvement_of_living_function" => "1",
                    "nutrition_management" => "2",
                    "oral_hygiene_management" => "2",
                    "oral_screening" => "1",
                    "scientific_nursing" => "2",
                    "strengthen_service_system" => "1",
                    "treatment_improvement" => "2",
                    "improvement_of_specific_treatment" => "2",
                    'baseup' => "2",
                    "over_capacity" => "1",
                    "discount" => "1",
                ],
                [
                    [126, 127, 129, 133, 138, 2391]
                ]
            ],
            "type33" => [
                [
                    "facility_id" => 6,
                    "start_date" => null,
                    "end_date" => null,
                    "service_id" => 23,
                    "service_type_code_id" => 3
                ],
                [
                    "start_month" => "2023-04-01",
                    "end_month" => "2023-05-31",
                    "service_form" => "1",
                    "vacancy" => "1",
                    "physical_restraint" => "1",
                    "support_continued_occupancy" => "1",
                    "improvement_of_living_function" => "1",
                    "individual_function_training_1" => "1",
                    "individual_function_training_2" => "1",
                    "adl_maintenance_etc" => "2",
                    "night_nursing_system" => "1",
                    "juvenile_dementia" => "1",
                    "medical_institution_cooperation" => "2",
                    "oral_hygiene_management" => "2",
                    "oral_screening" => "1",
                    "scientific_nursing" => "2",
                    "discharge_cooperation" => "1",
                    "nursing_care" => "1",
                    "dementia_specialty" => "1",
                    "strengthen_service_system" => "1",
                    "treatment_improvement" => "2",
                    "improvement_of_specific_treatment" => "2",
                    'baseup' => "2",
                    "discount" => "1",
                ],
                [
                    [165, 170, 172, 794, 797, 2392]
                ]
            ],
            "type36" => [
                [
                    "facility_id" => 6,
                    "start_date" => null,
                    "end_date" => null,
                    "service_id" => 24,
                    "service_type_code_id" => 4
                ],
                [
                    "start_month" => "2023-04-01",
                    "end_month" => "2023-05-31",
                    "service_form" => "1",
                    "vacancy" => "1",
                    "physical_restraint" => "1",
                    "support_continued_occupancy" => "1",
                    "improvement_of_living_function" => "1",
                    "individual_function_training_1" => "1",
                    "individual_function_training_2" => "1",
                    "adl_maintenance_etc" => "2",
                    "night_nursing_system" => "1",
                    "juvenile_dementia" => "1",
                    "medical_institution_cooperation" => "1",
                    "oral_hygiene_management" => "2",
                    "oral_screening" => "1",
                    "discharge_cooperation" => "1",
                    "nursing_care" => "1",
                    "dementia_specialty" => "1",
                    "scientific_nursing" => "2",
                    "strengthen_service_system" => "1",
                    "treatment_improvement" => "2",
                    "improvement_of_specific_treatment" => "2",
                    'baseup' => "2",
                    "discount" => "1",
                ],
                [
                    [822, 827, 840, 844, 847, 2393]
                ]
            ],
            "type35" => [
                [
                    "facility_id" => 6,
                    "start_date" => null,
                    "end_date" => null,
                    "service_id" => 25,
                    "service_type_code_id" => 5
                ],
                [
                    "start_month" => "2023-04-01",
                    "end_month" => "2023-05-31",
                    "service_form" => "1",
                    "vacancy" => "1",
                    "physical_restraint" => "1",
                    "improvement_of_living_function" => "1",
                    "individual_function_training_1" => "1",
                    "individual_function_training_2" => "1",
                    "juvenile_dementia" => "1",
                    "medical_institution_cooperation" => "1",
                    "oral_hygiene_management" => "2",
                    "oral_screening" => "1",
                    "scientific_nursing" => "2",
                    "dementia_specialty" => "1",
                    "strengthen_service_system" => "1",
                    "treatment_improvement" => "2",
                    "improvement_of_specific_treatment" => "2",
                    'baseup' => "2",
                    "discount" => "1",
                ],
                [
                    [865, 867, 995, 998, 2394]
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param $requestParam リクエストデータ
     * @param $additionStatus ラジオボタンの設定
     * @param $serviceCodeIds 事業所加算該当サービスコード
     * @test
     */
    public function facilityAddition($requestParam, $additionStatus, $serviceCodeIds)
    {
        $this->assertFalse(Auth::check());

        // 認証する。
        $user = User::
            where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')
            ->first();
        $this->actingAs($user);
        $this->assertTrue(Auth::check());

        // 登録処理を実行
        $response = $this->post(
            'group_home/facility_info/addition_status/insert/care_reward_history',
            [
                'facility_id' => $requestParam['facility_id'],
                'start_date' => null,
                'end_date' => null,
                'service_id' => $requestParam['service_id'],
                'service_type_code_id' => $requestParam['service_type_code_id'],
                'care_reward_history' => $additionStatus
            ]
        );
        // 正常に登録が完了しているかどうか
        $response->assertStatus(200);

        // 事業所加算テーブルに登録されているかチェックする
        for ($i=0; $i < count($serviceCodeIds) ; $i++) {
            $this->assertDatabaseHas('m_facility_additions',[
                'facility_id' => $requestParam['facility_id'],
                'service_item_code_id' => $serviceCodeIds[$i],
                'addition_start_date' => '2023-04-01',
                'addition_end_date' => '2023-05-31'
            ]);
        }

        // 事業所加算を削除する
        $careReward = CareReward::
            where('service_id', $requestParam['service_id'])
            ->latest('id')
            ->first();

        $id = CareRewardHistory::
            where('care_reward_id', $careReward['id'])
            ->select('id')
            ->first();

        $careRewardHistory = $additionStatus;
        // ラジオボタンの設定を全て「1」にする
        foreach ($careRewardHistory as $key => $value) {
            if ($key == 'start_month' || $key == 'end_month') {
                continue;
            }
            $careRewardHistory[$key] = 1;
        }
        $careRewardHistory['care_reward_histories_id'] = $id['id'];

        // 登録処理を実行する
        $response = $this->post(
            'group_home/facility_info/addition_status/update/care_reward_history',
            [
                'facility_id' => $requestParam['facility_id'],
                'start_date' => '2023/04/01',
                'end_date' => '2023/05/31',
                'service_id' => $requestParam['service_id'],
                'service_type_code_id' => $requestParam['service_type_code_id'],
                'care_reward_history' => $careRewardHistory
            ]
        );
        $response->assertStatus(200);

        // 事業所加算が削除されているかチェックする
        for ($i=0; $i < count($serviceCodeIds) ; $i++) {
            $this->assertDatabaseMissing('m_facility_additions',[
                'facility_id' => $requestParam['facility_id'],
                'service_item_code_id' => $serviceCodeIds[$i],
                'addition_start_date' => '2023-04-01',
                'addition_end_date' => '2023-05-31'
            ]);
        }

    }

    /**
     * @dataProvider dataProvider
     * 異常系
     * 主にバリデーションエラー
     */
    public function testFail($requestParam, $additionStatus, $serviceCodeIds)
    {
        $params = [
        'route' => 'group_home/facility_info/addition_status/insert/care_reward_history',
        'request' => [
                'facility_id' => $requestParam['facility_id'],
                'start_date' => null,
                'end_date' => null,
                'service_id' => $requestParam['service_id'],
                'service_type_code_id' => $requestParam['service_type_code_id'],
                'care_reward_history' => null
            ]
        ];
        $this->assertFalse(Auth::check());

        // 認証する。
        $user = User::
            where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')
            ->first();
        $this->actingAs($user);
        $this->assertTrue(Auth::check());

        // 履歴データと開始月・終了月が重複した場合
        $params['request']['care_reward_history'] = $additionStatus;
        $params['request']['care_reward_history']['start_month'] = "2022-04-01";
        $params['request']['care_reward_history']['end_month'] = "2022-04-30";

        $response = $this->post($params['route'], $params['request']);
        // バリデーションエラーが返ってきているかチェックする
        $response->assertStatus(400);

        // 開始月・終了月が異常値
        $params['request']['care_reward_history'] = $additionStatus;
        $params['request']['care_reward_history']['start_month'] = "2000-04-01";
        $params['request']['care_reward_history']['end_month'] = "2090-04-30";

        $response = $this->post($params['route'], $params['request']);
        $response->assertStatus(400);

        // 2022/10 の改定で追加されるベースアップ等支援加算のバリデーション
        // 開始月が2022/10 より前はベースアップ等支援加算は利用できない
        $params['request']['care_reward_history'] = $additionStatus;
        $params['request']['care_reward_history']['start_month'] = "2022-09-01";
        $params['request']['care_reward_history']['end_month'] = "2023-04-30";

        $response = $this->post($params['route'], $params['request']);
        // 設定したバリデーションメッセージを取得できているかチェックする
        $response->assertJsonValidationErrors([
            'care_reward_history' => "ベースアップ等支援加算を「あり」にする場合は、<br>新規作成ボタン押下後、開始月を2022年10月以降に設定してください。"
        ]);
        $response->assertStatus(400);

        // ベースアップ等支援加算を「あり」にしているが、処遇改善加算が「なし」
        // ベースアップ等支援加算は処遇改善加算と両方を利用していなくてはいけない
        $params['request']['care_reward_history'] = $additionStatus;
        $params['request']['care_reward_history']['treatment_improvement'] = "1";

        $response = $this->post($params['route'], $params['request']);
        // 設定したバリデーションメッセージを取得できているかチェックする
        $response->assertJsonValidationErrors([
            'care_reward_history' => '処遇体制加算未取得のためベースアップ等支援加算は設定出来ません。'
        ]);
        $response->assertStatus(400);
    }

}
