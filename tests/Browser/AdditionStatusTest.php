<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\FacilityInformation;
use Tests\Browser\Pages\Top;
use Tests\DuskTestCase;

/**
 * @group addition_status
 */
class AdditionStatusTest extends DuskTestCase
{
    // 加算の「なし」の値
    const ADDITION_INVALID_VAL = 1;
    // 加算の「あり」「Ⅰ型」の値
    const ADDITION_EFFECTIVENESS_VAL = 2;
    // 夜減加算の「あり」の値
    const NIGHT_SHIFT_VAL = 6;

    // 各加算のデータセット
    public function dataProvider()
    {
        return [
            '認知症対応型共同生活介護' => [
                [
                    'title' => '認知症対応型共同生活介護',
                    'service' => [
                        'section',
                        'vacancy',
                        'night_shift',
                        'physical_restraint',
                        'night_care_over_capacity',
                        'night_care',
                        'juvenile_dementia',
                        'hospitalization_cost',
                        'nursing_care',
                        'initial',
                        'medical_cooperation',
                        'consultation',
                        'dementia_specialty',
                        'improvement_of_living_function',
                        'nutrition_management',
                        'oral_hygiene_management',
                        'oral_screening',
                        'scientific_nursing',
                        'strengthen_service_system',
                        'treatment_improvement',
                        'improvement_of_specific_treatment',
                        'baseup',
                        'over_capacity',
                    ]
                ]
            ],
            '介護予防認知症対応型共同生活介護' => [
                [
                    'title' => '介護予防認知症対応型共同生活介護',
                    'service' => [
                        'section',
                        'vacancy',
                        'night_shift',
                        'physical_restraint',
                        'night_care_over_capacity',
                        'night_care',
                        'juvenile_dementia',
                        'hospitalization_cost',
                        'initial',
                        'consultation',
                        'dementia_specialty',
                        'improvement_of_living_function',
                        'nutrition_management',
                        'oral_hygiene_management',
                        'oral_screening',
                        'scientific_nursing',
                        'strengthen_service_system',
                        'treatment_improvement',
                        'improvement_of_specific_treatment',
                        'baseup',
                        'over_capacity',
                    ]
                ]
            ],
            '特定施設入居者生活介護' => [
                [
                    'title' => '特定施設入居者生活介護',
                    'service' => [
                        'vacancy',
                        'physical_restraint',
                        'support_continued_occupancy',
                        'improvement_of_living_function',
                        'individual_function_training_1',
                        'individual_function_training_2',
                        'adl_maintenance_etc',
                        'night_nursing_system',
                        'juvenile_dementia',
                        'medical_institution_cooperation',
                        'oral_hygiene_management',
                        'oral_screening',
                        'scientific_nursing',
                        'discharge_cooperation',
                        'nursing_care',
                        'dementia_specialty',
                        'strengthen_service_system',
                        'treatment_improvement',
                        'improvement_of_specific_treatment',
                        'baseup',
                    ]
                ]
            ],
            '地域密着型特定施設入居者生活介護' => [
                [
                    'title' => '地域密着型特定施設入居者生活介護',
                    'service' => [
                        'vacancy',
                        'physical_restraint',
                        'support_continued_occupancy',
                        'improvement_of_living_function',
                        'individual_function_training_1',
                        'individual_function_training_2',
                        'adl_maintenance_etc',
                        'night_nursing_system',
                        'juvenile_dementia',
                        'medical_institution_cooperation',
                        'oral_hygiene_management',
                        'oral_screening',
                        'scientific_nursing',
                        'discharge_cooperation',
                        'nursing_care',
                        'dementia_specialty',
                        'strengthen_service_system',
                        'treatment_improvement',
                        'improvement_of_specific_treatment',
                        'baseup',
                    ]
                ]
            ],
            '介護予防特定施設入居者生活介護' => [
                [
                    'title' => '介護予防特定施設入居者生活介護',
                    'service' => [
                        'vacancy',
                        'physical_restraint',
                        'improvement_of_living_function',
                        'individual_function_training_1',
                        'individual_function_training_2',
                        'juvenile_dementia',
                        'medical_institution_cooperation',
                        'oral_hygiene_management',
                        'oral_screening',
                        'scientific_nursing',
                        'dementia_specialty',
                        'strengthen_service_system',
                        'treatment_improvement',
                        'improvement_of_specific_treatment',
                        'baseup',
                    ]
                ]
            ],
        ];
    }

    public function type55DataProvider()
    {
        return [
            '介護医療院' => [
                [
                    'title' => '介護医療院',
                    'service' => [
                        'service_form',
                        'section',
                        'over_capacity',
                        'night_shift',
                        'vacancy',
                        'registered_nurse_ratio',
                        'unit_care_undevelopment',
                        'physical_restraint',
                        'safety_subtraction',
                        'nutritional_subtraction',
                        'recuperation_subtraction',
                        'juvenile_dementia',
                        'overnight_expenses_cost',
                        'trial_exit_service_fee',
                        'other_consultation_cost',
                        'initial',
                        're_entry_nutrition_cooperation',
                        'before_leaving_visit_guidance',
                        'after_leaving_visit_guidance',
                        'leaving_guidance',
                        'leaving_information_provision',
                        'after_leaving_alignment',
                        'home_visit_nursing_Instructions',
                        'nutrition_management_strength',
                        'oral_transfer',
                        'oral_maintenance',
                        'oral_hygiene',
                        'recuperation_food',
                        'home_return_support',
                        'emergency_treatment',
                        'dementia_specialty',
                        'emergency_response',
                        'severe_dementia_treatment',
                        'excretion_support',
                        'promotion_independence_support',
                        'scientific_nursing',
                        'long_term_medical_treatment',
                        'safety_measures_system',
                        'strengthen_service_system',
                        'treatment_improvement',
                        'improvement_of_specific_treatment',
                        'baseup',
                        'severe_skin_ulcer',
                        'drug_guidance',
                        'group_communication_therapy',
                        'physical_therapy',
                        'occupational_therapy',
                        'speech_hearing_therapy',
                        'psychiatric_occupational_therapy',
                        'other_rehabilitation_provision',
                        'dementia_short_rehabilitation',
                    ],
                    // 加算のうち特別診療費に関係があるサービス
                    'special_medical_expense_service' => [
                        'severe_skin_ulcer',
                        'drug_guidance',
                        'group_communication_therapy',
                        'physical_therapy',
                        'occupational_therapy',
                        'speech_hearing_therapy',
                        'psychiatric_occupational_therapy',
                        'other_rehabilitation_provision',
                        'dementia_short_rehabilitation',
                    ],
                    // 特別診療費 サービス一覧
                    'special_medical_expense_item' => [
                        'infection_control_guidance',
                        'specific_facility_management',
                        'facility_management_private_room',
                        'facility_management_double_room',
                        'medical_care_management',
                        'severe_skin_ulcer',
                        'drug_guidance',
                        'drug_guidance_information_utilization',
                        'special_drug_guidance',
                        'medical_information_provision_1',
                        'medical_information_provision_2',
                        'physical_therapy_1',
                        'physical_therapy_2',
                        'physical_therapy_rehabilitation_plan',
                        'physical_therapy_daily_movement',
                        'physical_therapy_rehabilitation_reinforcement',
                        'physical_therapy_1_information_exercise',
                        'physical_therapy_2_information_exercise',
                        'occupational_therapy',
                        'occupational_therapy_rehabilitation_plan',
                        'occupational_therapy_daily_movement',
                        'occupational_therapy_rehabilitation_reinforcement',
                        'occupational_therapy_information_exercise',
                        'psychiatric_occupational_therapy',
                        'dementia_admission_psychotherapy',
                        'pressure_ulcer_control_guidance_1',
                        'pressure_ulcer_control_guidance_2',
                        'severe_therapy_management',
                        'speech_hearing_therapy',
                        'speech_hearing_therapy_rehabilitation',
                        'speech_hearing_therapy_information_exercise',
                        'physical_therapy_1_subtraction',
                        'physical_therapy_2_subtraction',
                        'occupational_therapy_subtraction',
                        'speech_hearing_therapy_subtraction',
                        'short_concentration_rehabilitation',
                        'group_communication_therapy',
                        'dementia_short_rehabilitation',
                    ],
                    // 加算「あり」で自動チェックが付く特別診療費のサービス
                    'auto_check_target' => [
                        'severe_skin_ulcer',
                        'drug_guidance',
                        'drug_guidance_information_utilization',
                        'special_drug_guidance',
                        'physical_therapy_1',
                        'physical_therapy_2',
                        'physical_therapy_rehabilitation_plan',
                        'physical_therapy_rehabilitation_reinforcement',
                        'physical_therapy_1_information_exercise',
                        'physical_therapy_2_information_exercise',
                        'occupational_therapy',
                        'occupational_therapy_rehabilitation_plan',
                        'occupational_therapy_rehabilitation_reinforcement',
                        'occupational_therapy_information_exercise',
                        'psychiatric_occupational_therapy',
                        'dementia_admission_psychotherapy',
                        'speech_hearing_therapy',
                        'speech_hearing_therapy_rehabilitation',
                        'speech_hearing_therapy_information_exercise',
                        'physical_therapy_1_subtraction',
                        'physical_therapy_2_subtraction',
                        'occupational_therapy_subtraction',
                        'speech_hearing_therapy_subtraction',
                        'short_concentration_rehabilitation',
                        'group_communication_therapy',
                        'dementia_short_rehabilitation',
                    ]
                ]
            ],
        ];
    }

    /**
     * 加算状況の表示テスト
     * 何回も呼び出すので共通化しとく
     */
    public function testAdditionStatusView()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();
            $browser
                ->loginAs($user)
                ->visit(new FacilityInformation());

            // 加算状況タブボタンをクリックした場合、加算状況フォームが表示されることをテストする。
            $browser
                ->waitFor('@addition-status-button')
                ->click('@addition-status-button')
                ->waitFor('#js-new_addition_status')
                ->assertVisible('@addition-status-form-label');
        });
    }

    /**
     * 加算状況の更新のテスト
     * @dataProvider dataProvider
     * @param $requestParam リクエストデータ
     * @return void
     */
    public function testUpdateStatus($requestParam)
    {
        self::testAdditionStatusView();
        $this->createBrowsersFor(function (Browser $browser) use ($requestParam) {
            // 加算開始月
            $additionStartMonth = '2021/11';
            // 加算終了月
            $additionEndMonth = '2022/03';

            $browser
                ->click('@'.$requestParam['title'])
                ->pause(2000)
                // 選択したサービス名がハイライトされているかチェックする
                ->assertAttribute(
                    '@'.$requestParam['title'],
                    'class',
                    'corporate_item service_label active_target'
                )
                // 最新の履歴が選択されているか
                ->assertAttribute(
                    '#table_tbody_addition_status > tr',
                    'class',
                    'selectTableAdditionStatus as_care_reward_history_table_record_selected'
                );
                // ラジオボタンを「あり」「なし」交互に選択する
                $i = 1;
                foreach ($requestParam['service'] as $value) {
                    // ベースアップ等支援加算は「なし」にする
                    if ($value == 'baseup') {
                        $i++;
                        continue;
                    } elseif (($i % 2) == 0) {
                        // 偶数列は「あり」にする
                        $browser
                            ->scrollIntoView('[name="'.$value.'"]')
                            ->click('.radio_addition_status2 > label:nth-child(2) > input[name='.$value.'] + span');
                    }
                    $i++;
                }
            // 和暦が想定外の挙動をする可能性があるので月変更は最後に設定する
            $browser
                // 「開始月」の値を変更する
                ->type('#search_start_addition_status', $additionStartMonth)
                // 「終了月」の値を変更する
                ->type('#search_end_addition_status', $additionEndMonth);

                // 保存ボタンを押下→ポップアップの「いいえ」を押下→ポップアップが表示されていないことを確認する
            $browser
                ->click('#js-updata-popup_addition_status')
                ->waitFor('#cancelbtn_addition_status')
                ->click('#cancelbtn_addition_status')
                ->assertMissing('#overflow_addition_status'); // ポップアップが表示されていないことの確認

            // 更新が正常に完了しているかチェックする
            $browser
                // 保存ボタンを押下する
                ->click('#js-updata-popup_addition_status')
                ->waitFor('#updatabtn_addition_status')
                // ポップアップ内「はい」を押下する
                ->click('#updatabtn_addition_status')
                ->pause(3000)
                // 新規登録ボタンを押下する
                ->click('#js-new_addition_status')
                // 履歴を選択
                ->click('tr#selectTdAdditionStatus')
                ->pause(1000)
                // 「開始月」が更新した月になっていることを確認
                ->assertInputValue('#search_start_addition_status', $additionStartMonth)
                // 「終了月」が更新した月になっていることを確認
                ->assertInputValue('#search_end_addition_status', $additionEndMonth);

            // ラジオボタンが「あり」「なし」交互になっているか
            $i = 1;
            foreach ($requestParam['service'] as $value) {
                if ($value == 'baseup' || ($i % 2) == 1) {
                    // 奇数列とベースアップ等支援加算は「なし」
                    $browser
                        ->scrollIntoView('[name="'.$value.'"]')
                        ->assertRadioSelected($value, self::ADDITION_INVALID_VAL);
                } elseif (($i % 2) == 0) {
                    // 偶数列は「あり」
                    $browser
                        ->scrollIntoView('[name="'.$value.'"]')
                        ->assertRadioSelected($value, self::ADDITION_EFFECTIVENESS_VAL);
                }
                $i++;
            }
        });
        // ログアウトしてセッションデータを削除する
        foreach (static::$browsers as $browser) {
            $browser->driver->manage()->deleteAllCookies();
        }
    }

    /**
     * 加算状況の新規登録をテストする
     * @dataProvider dataProvider
     * @param $requestParam リクエストデータ
     * @return void
     */
    public function testNewRegister($requestParam)
    {
        self::testAdditionStatusView();
        $this->createBrowsersFor(function (Browser $browser) use ($requestParam) {

            $additionStartEndMonth = '2022/04';

            $browser
                ->click('@'.$requestParam['title'])
                ->pause(2000)
                // 選択したサービス名がハイライトされているかチェックする
                ->assertAttribute(
                    '@'.$requestParam['title'],
                    'class',
                    'corporate_item service_label active_target'
                )
                // 最新の履歴が選択されているかチェックする
                ->assertAttribute(
                    '#table_tbody_addition_status > tr',
                    'class',
                    'selectTableAdditionStatus as_care_reward_history_table_record_selected'
                )
                // 新規登録ボタンを押下
                ->click('#js-new_addition_status')
                ->pause(1000)
                // 「開始月」に最新履歴の翌月が入力されているかチェックする
                // 月は更新処理で設定した値によって変わるので注意
                ->assertInputValue('#search_start_addition_status', $additionStartEndMonth)
                // 「終了月」に値が入っていないかチェックする
                ->assertInputValue('#search_end_addition_status', "");

            // ラジオボタンが最新履歴の情報を引き継いでいるかチェックする
            // テストデータの初期値を変える、またはテストアカウントを変更する場合は要修正
            // ラジオボタンが「あり」「なし」交互になっているか
            $i = 1;
            foreach ($requestParam['service'] as $value) {
                if ($value == 'baseup' || ($i % 2) == 1) {
                    // 奇数列とベースアップ等支援加算は「なし」
                    $browser
                        ->scrollIntoView('[name="'.$value.'"]')
                        ->assertRadioSelected($value, self::ADDITION_INVALID_VAL);
                } elseif (($i % 2) == 0) {
                    // 偶数列は「あり」
                    $browser
                        ->scrollIntoView('[name="'.$value.'"]')
                        ->assertRadioSelected($value, self::ADDITION_EFFECTIVENESS_VAL);
                }
                $i++;
            }
            // ラジオボタンを全て「あり」「加算Ⅰ」で選択する
            foreach ($requestParam['service'] as $value) {
                // ベースアップ等支援加算は「なし」にする
                if ($value == 'baseup') {
                    continue;
                }
                $browser->click('.radio_addition_status2 > label:nth-child(2) > input[name='.$value.'] + span');
            }
            // 和暦が想定外の挙動をする可能性があるので月変更は最後に設定する
            $browser
                // 「終了月」に値を入れる
                ->type('#search_end_addition_status', $additionStartEndMonth);

            // 登録が正常に完了しているかチェックする
            $browser
                // 保存ボタンを押下する
                ->click('#js-updata-popup_addition_status')
                ->pause(3000)
                // 新規登録ボタンを押下する
                ->click('#js-new_addition_status')
                // 履歴を選択
                ->click('tr#selectTdAdditionStatus')
                ->pause(1000)
                // 「開始月」が更新した月になっていることを確認
                ->assertInputValue('#search_start_addition_status', $additionStartEndMonth)
                // 「終了月」が更新した月になっていることを確認
                ->assertInputValue('#search_end_addition_status', $additionStartEndMonth);

            // ラジオボタンが全て「あり」になっているか
            foreach ($requestParam['service'] as $value) {
                if ($value == 'baseup') {
                    // ベースアップ等支援加算は「なし」
                    $browser
                        ->scrollIntoView('[name="'.$value.'"]')
                        ->assertRadioSelected($value, self::ADDITION_INVALID_VAL);
                    continue;
                } elseif ($value == 'night_shift') {
                    // 夜減の「あり」は6
                    $browser
                        ->scrollIntoView('[name="'.$value.'"]')
                        ->assertRadioSelected($value, self::NIGHT_SHIFT_VAL);
                    continue;
                }
                $browser
                    ->scrollIntoView('[name="'.$value.'"]')
                    ->assertRadioSelected($value, self::ADDITION_EFFECTIVENESS_VAL);
            }
        });
        // ログアウトしてセッションデータを削除する
        foreach (static::$browsers as $browser) {
            $browser->driver->manage()->deleteAllCookies();
        }
    }

    /**
     * 履歴の選択テスト
     * @dataProvider dataProvider
     * @param $requestParam リクエストデータ
     * @return void
     */
    public function testChangeHistory($requestParam)
    {
        self::testAdditionStatusView();
        $this->createBrowsersFor(function (Browser $browser) use ($requestParam) {
            $browser
                ->click('@'.$requestParam['title'])
                ->pause(2000)
                // 選択したサービス名がハイライトされているかチェックする
                ->assertAttribute(
                    '@'.$requestParam['title'],
                    'class',
                    'corporate_item service_label active_target'
                )
                // 最新の履歴が選択されているかチェックする
                ->assertAttribute(
                    '#table_tbody_addition_status > tr',
                    'class',
                    'selectTableAdditionStatus as_care_reward_history_table_record_selected'
                )
                ->pause(2000)
                // 2番目の履歴を選択する
                ->click('#table_tbody_addition_status > tr + tr')
                ->pause(2000)
                // 2番目の履歴が選択されていることの確認
                ->assertAttribute(
                    '#table_tbody_addition_status > tr + tr',
                    'class',
                    'selectTableAdditionStatus as_care_reward_history_table_record_selected'
                );
        });
    }

    /**
     * 種類55のテスト
     * @dataProvider type55DataProvider
     * @param $type55DataProvider リクエストデータ
     * @return void
     */
    public function testAdditionStatusOfType55($type55DataProvider)
    {
        $this->browse(function (Browser $browser) use ($type55DataProvider) {
            // 介護医療院アカウントでログイン
            $user = User::where('employee_number', 'hospital@0000000008.care-daisy.com')->first();
            $browser->loginAs($user)->visit(new FacilityInformation());

            // 加算状況タブボタンをクリックした場合、加算状況フォームが表示されることをテストする。
            $browser
                ->pause(1000)
                ->click('@'.$type55DataProvider['title'])
                ->waitFor('@addition-status-form-label')
                // 選択したサービス名がハイライトされているかチェックする
                ->assertAttribute(
                    '@'.$type55DataProvider['title'],
                    'class',
                    'corporate_item service_label active_target'
                )
                // 「特別診療費」タブが表示されているかチェックする
                ->assertVisible('#special_medical_expenses_view')
                // 初期状態で「加算状況」タブが選択されているかチェックする
                ->assertAttribute(
                    '#addtion_status_view',
                    'class',
                    'view_tab addition_view_tab active'
                );
            // 特別診療費に関係がある加算を「あり」にして更新する
            foreach ($type55DataProvider['special_medical_expense_service'] as $value) {
                $browser
                    ->pause(500)
                    ->scrollIntoView('[name="'.$value.'"]')
                    ->click('.radio_addition_status2 > label:nth-child(2) > input[name='.$value.'] + span')
                    ->assertRadioSelected($value, self::ADDITION_EFFECTIVENESS_VAL);
            }
            $browser
                // 保存ボタンを押下する
                ->click('#js-updata-popup_addition_status')
                ->waitFor('#updatabtn_addition_status')
                // ポップアップ内「はい」を押下する
                ->click('#updatabtn_addition_status')
                ->pause(3000);

            // 特別診療費に関係がある加算を「あり」になっているかチェックする
            foreach ($type55DataProvider['special_medical_expense_service'] as $value) {
                $browser
                    ->scrollIntoView('[name="'.$value.'"]')
                    ->assertRadioSelected($value, self::ADDITION_EFFECTIVENESS_VAL);
            }
        });
    }
}
