<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\FacilityInformation;
use Tests\Browser\Pages\StaffInformation;
use Tests\DuskTestCase;

class SpecialMedicalExpensesTest extends DuskTestCase
{

    // テストで使用するデータ
    public function type55DataProvider()
    {
        return [
            '介護医療院' => [
                [
                    'title' => '介護医療院',
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
     * 「47 言語聴覚療法（減算）」にチェックを入れ、
     * 保存ボタンを押下してもチェックがされた状態となっているかをテストする。
     * デグレチェックのため、複数項目のチェックや他のサービスでもテストをする。
     * @return void
     */
    public function testCheckTransition(): void
    {
        $this->browse(function (Browser $browser) {

        // 特別診療開始月
        $ymSpecialMedicStart = '2021/10';

        // 特別診療終了月
        $ymSpecialMedicEnd = '2022/11';

        // 介護医療院の事業所があるアカウントでログインし、事業所情報画面へ遷移する。
        $user = User::where('employee_number', 'hospital@0000000008.care-daisy.com')->first();

        $browser
            ->loginAs($user)
            ->visit(new FacilityInformation())
            ->pause(2000);

        // 介護医療院タブを押下し、加算状況画面へ遷移する。
        $browser
            ->click('.survice > li > label', 'class', '.corporate_item service_label')
            ->pause(2000);

        // 特別診療費タブを押下し、特別診療費画面へ遷移する。
        $browser
            ->click('#special_medical_expenses_view')
            ->pause(2000);

        // 開始月・終了月を入力し、入力した数字が消えずに表示される。
        $browser
            ->type('#special_medical_expenses_start', $ymSpecialMedicStart)
            ->click('[data-month="9"] > a', 'class', '#ui-state-ym-default')
            ->pause(2000);

        $browser
            ->type('#special_medical_expenses_end', $ymSpecialMedicEnd)
            ->click('[data-month="10"] > a', 'class', '#ui-state-ym-default')
            ->pause(2000);

        // 保存ボタンを押下し、開始月・終了月が保存される。
        $browser
            ->click('#special_medical_expenses_save')
            ->pause(2000);

        // 開始月・終了月が保存されているかを確認するために、
        // 別画面に移動・再度特別診療費画面に戻り、保存されている値を確認する。
        $browser
            ->visit(new StaffInformation())
            ->pause(2000)
            ->visit(new FacilityInformation())
            ->pause(2000)
            ->click('.survice > li > label', 'class', '.corporate_item service_label')
            ->pause(2000)
            ->click('#special_medical_expenses_view')
            ->pause(2000)
            ->assertValue('#special_medical_expenses_start', $ymSpecialMedicStart)
            ->assertValue('#special_medical_expenses_end', $ymSpecialMedicEnd);

        // 言語聴覚療法（減算）チェックテスト

        // 「47　言語聴覚療法（減算）」にチェックを入れる。
        $browser
            ->scrollIntoView('[name="speech_hearing_therapy_subtraction"]')
            ->check('[name="speech_hearing_therapy_subtraction"]')
            ->pause(2000);

        // 保存ボタンを押下し、「47　言語聴覚療法（減算）」にチェックがされた状態となっている。
        $browser
            ->scrollIntoView('#special_medical_expenses_save')
            ->click('#special_medical_expenses_save')
            ->pause(2000)
            ->assertChecked('[name="speech_hearing_therapy_subtraction"]');

        // 「47　言語聴覚療法（減算）」のチェックを外す。
        $browser
            ->scrollIntoView('[name="speech_hearing_therapy_subtraction"]')
            ->uncheck('[name="speech_hearing_therapy_subtraction"]')
            ->pause(2000);

        // 保存ボタンを押下し、「47　言語聴覚療法（減算）」のチェックが外れた状態となっている。
        $browser
            ->scrollIntoView('#special_medical_expenses_save')
            ->click('#special_medical_expenses_save')
            ->pause(2000)
            ->assertNotChecked('[name="speech_hearing_therapy_subtraction"]');

        // 「45　作業療法（減算）」「47　言語聴覚療法（減算）」「52　短期集中リハビリテーション」
        // にチェックを入れる。
        $browser
            ->scrollIntoView('[name="short_concentration_rehabilitation"]')
            ->check('[name="occupational_therapy_subtraction"]')
            ->check('[name="speech_hearing_therapy_subtraction"]')
            ->check('[name="short_concentration_rehabilitation"]')
            ->pause(2000);

        // 保存ボタンを押下し、「45　作業療法（減算）」「47　言語聴覚療法（減算）」「52　短期集中リハビリテーション」
        // にチェックがされた状態となっている。
        $browser
            ->scrollIntoView('#special_medical_expenses_save')
            ->click('#special_medical_expenses_save')
            ->pause(2000)
            ->assertChecked('[name="occupational_therapy_subtraction"]')
            ->assertChecked('[name="speech_hearing_therapy_subtraction"]')
            ->assertChecked('[name="short_concentration_rehabilitation"]');

        // 「45　作業療法（減算）」「47　言語聴覚療法（減算）」「52　短期集中リハビリテーション」のチェックを外す。
        $browser
            ->scrollIntoView('[name="short_concentration_rehabilitation"]')
            ->uncheck('[name="occupational_therapy_subtraction"]')
            ->uncheck('[name="speech_hearing_therapy_subtraction"]')
            ->uncheck('[name="short_concentration_rehabilitation"]')
            ->pause(2000);

        // 保存ボタンを押下し、「45　作業療法（減算）」「47　言語聴覚療法（減算）」「52　短期集中リハビリテーション」
        // のチェックが外れた状態となっている。
        $browser
            ->scrollIntoView('#special_medical_expenses_save')
            ->click('#special_medical_expenses_save')
            ->pause(2000)
            ->assertNotChecked('[name="occupational_therapy_subtraction"]')
            ->assertNotChecked('[name="speech_hearing_therapy_subtraction"]')
            ->assertNotChecked('[name="short_concentration_rehabilitation"]');

        // 感染対策指導管理チェックテスト

        // 「01　感染対策指導管理」にチェックを入れる。
        $browser
            ->check('[name="infection_control_guidance"]')
            ->pause(2000);

        // 保存ボタンを押下し、「01　感染対策指導管理」にチェックがされた状態となっている。
        $browser
            ->click('#special_medical_expenses_save')
            ->pause(2000)
            ->assertChecked('[name="infection_control_guidance"]');

        // 「01　感染対策指導管理」のチェックを外す。
        $browser
            ->uncheck('[name="infection_control_guidance"]')
            ->pause(2000);

        // 保存ボタンを押下し、「01　感染対策指導管理」のチェックが外れた状態となっている。
        $browser
            ->click('#special_medical_expenses_save')
            ->pause(2000)
            ->assertNotChecked('[name="infection_control_guidance"]');

        // 「45　作業療法（減算）」「47　言語聴覚療法（減算）」「52　短期集中リハビリテーション」
        // にチェックを入れる。
        $browser
            ->check('[name="infection_control_guidance"]')
            ->check('[name="specific_facility_management"]')
            ->check('[name="facility_management_private_room"]')
            ->pause(2000);

        // 保存ボタンを押下し、「45　作業療法（減算）」「47　言語聴覚療法（減算）」「52　短期集中リハビリテーション」
        // にチェックがされた状態となっている。
        $browser
            ->click('#special_medical_expenses_save')
            ->pause(2000)
            ->assertChecked('[name="infection_control_guidance"]')
            ->assertChecked('[name="specific_facility_management"]')
            ->assertChecked('[name="facility_management_private_room"]');

        // 「45　作業療法（減算）」「47　言語聴覚療法（減算）」「52　短期集中リハビリテーション」のチェックを外す。
        $browser
            ->uncheck('[name="infection_control_guidance"]')
            ->uncheck('[name="specific_facility_management"]')
            ->uncheck('[name="facility_management_private_room"]')
            ->pause(2000);

        // 保存ボタンを押下し、「45　作業療法（減算）」「47　言語聴覚療法（減算）」「52　短期集中リハビリテーション」
        // のチェックが外れた状態となっている。
        $browser
            ->click('#special_medical_expenses_save')
            ->pause(2000)
            ->assertNotChecked('[name="infection_control_guidance"]')
            ->assertNotChecked('[name="specific_facility_management"]')
            ->assertNotChecked('[name="facility_management_private_room"]');
        });
    }

    /**
     * 特別診療費の新規登録をテストする
     * @dataProvider type55DataProvider
     * @param $type55DataProvider リクエストデータ
     * @return void
     */
    public function testSpecialMedicalExpenses($type55DataProvider)
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'hospital@0000000008.care-daisy.com')->first();
            $browser->loginAs($user)->visit(new FacilityInformation());
        });

        $this->createBrowsersFor(function (Browser $browser) use ($type55DataProvider) {
            // バリデーションエラー(月重複)確認用開始月
            $duplicationStartMonth = '2021/10';
            // バリデーションエラー(月重複)確認用終了月
            $duplicationEndMonth = '2022/11';
            // バリデーションエラー(月重複)メッセージ
            $duplicationMsg = '期間重複している履歴があります。';
            // バリデーションエラー(指定不可月)確認用開始月
            $outOfTermStartMonth = '2021/03';
            // バリデーションエラー(指定不可月)確認用終了月
            $outOfTermEndMonth = '2021/09';
            // バリデーションエラー(指定不可月)メッセージ
            $outOfTermMsg = '開始月、終了月を2021/4~2024/3の範囲で指定してください';
            // 新規登録用開始月
            $newStartMonth = '2022/12';
            // 新規登録用終了月
            $newEndMonth = '2023/03';

            // 加算状況タブボタンを押下時、初期状態で「加算状況加算状況フォームが表示されることをテストする。
            $browser
                ->waitFor('@addition-status-button')
                ->click('@addition-status-button')
                ->waitFor('#js-new_addition_status')
                ->assertVisible('@addition-status-form-label')
                // 初期状態で「加算状況」がハイライトされてるかチェックする
                ->assertAttribute(
                    '#addtion_status_view',
                    'class',
                    'view_tab addition_view_tab active'
                )
                // 「特別診療費」側に遷移する
                ->click('#special_medical_expenses_view')
                ->waitFor('#special_medical_expenses_new_register')
                // 「特別診療費」タブがハイライトされているかチェックする
                ->assertAttribute(
                    '#special_medical_expenses_view',
                    'class',
                    'view_tab special_medical_expenses_view_tab active'
                )
                // 新規登録ボタンを押下する
                // 履歴は直前のテスト「testCheckTransition」で作成されている
                ->click('#special_medical_expenses_new_register');
                // 自動チェック対象サービスにチェックがついていることを確認する
                foreach ($type55DataProvider['auto_check_target'] as $value) {
                    $browser->assertChecked($value);
                }

            // バリデーションメッセージ表示確認
            // 指定不可月を設定した場合の表示チェックを行う
            $browser
                // 開始月を設定する
                ->type('#special_medical_expenses_start', $outOfTermStartMonth)
                // 終了月を設定する
                ->type('#special_medical_expenses_end', $outOfTermEndMonth)
                // 保存ボタンを押下する
                ->click('#special_medical_expenses_save')
                ->waitFor('.popup_cancel_sp_medical')
                // 正しいバリデーションメッセージが表示されていることをチェックする
                ->assertSee($outOfTermMsg)
                // ポップアップを閉じる
                ->click('.popup_cancel_sp_medical');

            // 期間重複の場合の表示チェックを行う
            $browser
                // 開始月を設定する
                ->type('#special_medical_expenses_start', $duplicationStartMonth)
                // 終了月を設定する
                ->type('#special_medical_expenses_end', $duplicationEndMonth)
                // 保存ボタンを押下する
                ->click('#special_medical_expenses_save')
                ->waitFor('.popup_cancel_sp_medical')
                // 正しいバリデーションメッセージが表示されていることをチェックする
                ->assertSee($duplicationMsg)
                // ポップアップを閉じる
                ->click('.popup_cancel_sp_medical');

            // 新規登録が正常に完了するかどうか
            $browser
                // 開始月を設定する
                ->type('#special_medical_expenses_start', $newStartMonth)
                // 終了月を設定する
                ->type('#special_medical_expenses_end', $newEndMonth)
                // 保存ボタンを押下する
                ->click('#special_medical_expenses_save')
                ->pause(2000)
                // 作成した履歴が選択されているかチェックする
                ->assertAttribute(
                    '#special_medical_expenses_history_tbody > tr',
                    'class',
                    'select_table_special_medical_expenses as_special_medical_expenses_history_selected'
                )
                ->assertValue('#special_medical_expenses_start', $newStartMonth)
                ->assertValue('#special_medical_expenses_end', $newEndMonth);
        });
    }
}
