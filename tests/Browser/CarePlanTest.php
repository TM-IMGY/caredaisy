<?php

namespace Tests\Browser;

use App\Models\FacilityUser;
use App\Models\ServicePlan;
use App\Models\UserCareInformation;
use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\CarePlan;
use Tests\DuskTestCase;
use Carbon\Carbon;

class CarePlanTest extends DuskTestCase
{
    /**
     * ケアプラン画面のタブ遷移が正しく機能しているかをテストする。
     * @return void
     */
    public function testTabTransition(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'GH00002')->first();

            $browser
                ->loginAs($user)
                ->visit(new CarePlan());

            // 介護計画書1タブボタンをクリックした場合、介護計画書1フォームが表示されることをテストする。
            $browser
                ->click('@care-plan-1-button')
                ->waitFor('@care-plan-1-form-label');

            // 介護計画書2タブボタンをクリックした場合、介護計画書2フォームが表示されることをテストする。
            $browser
                ->click('@care-plan-2-button')
                ->waitFor('@care-plan-2-form-label');
        });
    }

    public function testPlan2Validation(): void
    {
        $targetUser = '施設利用者A';

        $this->browse(function (Browser $browser) use ($targetUser) {
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')
                ->first();

            // ケアプラン報画面に遷移する。
            $browser
                ->loginAs($user)
                ->visit(new CarePlan())
                ->waitForText($targetUser) // 対象利用者が表示されるまで待機する
                ->click('#table_facility_user_id1') //利用者を選択する
                ->waitFor('#service_plan1_history_table_body tr:first-of-type td:nth-of-type(7)', 10)
                ->assertSeeIn('#service_plan1_history_table_body tr:first-of-type td:nth-of-type(7)', '介護花子a')

                ->click('#service_plan1_history_table_body tr:first-of-type td:first-of-type') //履歴を選択する
                ->pause(2000)
                ->waitUntilMissing('@update-dialog')
                ->click('@care-plan-2-button')
                ->waitUntilMissing('@update-dialog')
                ->assertInputValue('#sp2_author', '介護花子a')

                ->waitFor('#sp2_tbody tr:first-of-type td:first-of-type textarea')
                ->assertSeeNothingIn('#sp2_tbody tr:first-of-type td:first-of-type textarea') // 生活全般の解決すべき課題（ニーズ）

                ->type('#sp2_tbody tr:first-of-type td:first-of-type textarea', 'dummyText') // 入力
                ->type('#sp2_tbody tr:first-of-type td:nth-of-type(3) input:nth-of-type(2)', '1900/01/01') // 長期期間（end）
                ->click('#sp2_save_btn')
                ->waitFor('#overflow_sp2', 10)
                ->assertSee('2000年4月以降の年月を入力してください') // バリデーションエラー

                ->click('#close_sp2_popup')
                ->waitUntilMissing('#overflow_sp2')
                ->assertInputValue('#sp2_table tbody tr:first-of-type td:first-of-type textarea', 'dummyText'); // 入力した値が残っている
        });
    }

    /**
     * 退居済もしくは看取り日に日付が入っている利用者には
     * ケアプラン期間アラートが表示されないようにする
     */
    public function testMovedOutUserCarePlanAlertVisible(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')
                ->first();

            // 看取り日入力済み利用者
            $deathedUser = '施設利用者F';
            // 退居済利用者
            $movedOutUser = '施設利用者S';

            // 利用者情報画面に遷移する。
            $browser
                ->loginAs($user)
                // 利用者情報画面に遷移する
                ->visit(new CarePlan())
                // 対象利用者が表示されるまで待機する
                ->waitForText($deathedUser)
                ->pause(1500)
                //看取り日入力済み利用者を選択する
                ->click('#table_facility_user_id6')
                ->pause(1500)
                ->screenshot('user_select1')
                // 対象の利用者にアラートが表示されていないことをチェックする
                ->assertMissing('.fu_table_selected_cell > span')
                //退居日入力済み利用者を選択する
                ->click('#table_facility_user_id81')
                ->pause(1500)
                ->screenshot('user_select2')
                // 対象の利用者にアラートが表示されていないことをチェックする
                ->assertMissing('.fu_table_selected_cell > span');
        });
    }

    /**
     * 利用者一覧サイドバーのアラートにマウスオンした際の文言をテストする
     *
     * 特定ユーザーの認定情報有効期限(i_user_care_informations.care_period_end)を操作し、
     * アラートの文言が適切に変化するかチェックする
     *
     * @return void
     */
    public function testSidebarAlertText()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')
                ->first();

            // 利用者情報画面に遷移する
            $browser->loginAs($user)
                ->visit(new CarePlan())
                ->waitFor('.facility_user_td');

            // サイドバー最上部の利用者のサービスプランを作成
            $userId = $browser->attribute('#user_info_fu_tbody > tr', 'data-facility-user-id');
            $servicePlan = ServicePlan::create([
                'facility_user_id' => $userId,
                'plan_start_period' => today()->subDay(),
                'plan_end_period' => today()->addDay(),
                'status' => ServicePlan::STATUS_ISSUED,
                'certification_status' => 2,
                'recognition_date' => today(),
                'care_period_start' => today()->subMonth(),
                'care_period_end' => today()->addMonths(2),
                'care_level_name' => '要介護度2',
                'independence_level' => 1,
                'dementia_level' => 6,
                'start_date' => today()->subMonth(),
                'end_date' => today()->addMonth()->addDay(),

            ]);

            // 「残り2ヶ月」の文言チェック
            $browser->visit(new CarePlan())
                ->waitFor('#user_info_fu_tbody span.alert_two_months')
                ->pause(1000)
                ->mouseover('#user_info_fu_tbody span.alert_two_months')
                ->waitForText('ケアプラン期間期限切れまで残り2ヶ月です。')
                ->assertSee('ケアプラン期間期限切れまで残り2ヶ月です。');

            // 「残り30日」を表示させるために期限日を更新し、文言チェック
            $servicePlan->end_date = today()->addDays(30);
            $servicePlan->save();
            $browser->visit(new CarePlan())
                ->waitFor('#user_info_fu_tbody span.alert_one_month')
                ->pause(1000)
                ->mouseover('#user_info_fu_tbody span.alert_one_month')
                ->waitForText('ケアプラン期間期限切れまで残り30日です。')
                ->assertSee('ケアプラン期間期限切れまで残り30日です。');

            // 「残り1日」を表示させるために期限日を更新し、文言チェック
            $servicePlan->end_date = today()->addDay();
            $servicePlan->save();
            $browser->visit(new CarePlan())
                ->waitFor('#user_info_fu_tbody span.alert_one_month')
                ->pause(1000)
                ->mouseover('#user_info_fu_tbody span.alert_one_month')
                ->waitForText('ケアプラン期間期限切れまで残り1日です。')
                ->assertSee('ケアプラン期間期限切れまで残り1日です。');

            // 「当日です」を表示させるために期限日を更新し、文言チェック
            $servicePlan->end_date = today();
            $servicePlan->save();
            $browser->visit(new CarePlan())
                ->waitFor('#user_info_fu_tbody span.alert_today')
                ->pause(1000)
                ->mouseover('#user_info_fu_tbody span.alert_today')
                ->waitForText('当日です。')
                ->assertSee('当日です。');

            // テストデータの変更で影響が出ないようにテストデータ削除
            $servicePlan->delete();
        });
    }

    /**
     * 介護計画書1にて
     *
     * ケアプラン期間が1年以内の場合にダイアログが表示されずにデータが保存されること
     */
    public function testServicePlan1SaveData(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

            // TODO: テストを通すための一時的な措置のため、シーディングの段階で入れるデータの変更を検討する
            $plan = ServicePlan::where('facility_user_id', 1)->first();
            $plan->start_date = '2022-01-01';
            $plan->end_date = '2022-12-31';
            $plan->save();

            // ケアプラン画面に遷移する
            $browser->loginAs($user)->visit(new CarePlan())
                ->waitForText('施設利用者S') // 対象利用者が表示されるまで待機する
                ->click('#table_facility_user_id1') //利用者を選択する
                ->pause(5000)
                ->value('#sp1_care_plan_period_start', '2022-01-01') // ケアプラン期間開始日を入力
                ->pause(5000)
                ->value('#sp1_care_plan_period_end', '2022-12-31') // ケアプラン期間終了日を入力
                ->pause(5000)
                ->click('#status_tmp') // 保存ボタンを押下
                ->pause(2000)
                ->assertMissing('@care_plan_dialog') // ダイアログが表示されていないことを確認
                ->pause(10000)
                ->assertSeeIn('@tbody_end_date1', '2022/12/31'); // ケアプラン期間終了日を確認
        });
    }

    /**
     * 介護計画書1にて
     *
     * ケアプラン期間の終了日を開始日から1年以上先の日付に設定した場合
     * ・確認のダイアログが表示されること
     * ・表示されたダイアログで「いいえ」を選択した場合にダイアログが閉じられること
     * ・表示されたダイアログで「はい」を選択した場合に設定した終了日が保存されること
     *
     * 上記を確認するテスト
     */
    public function testServicePlan1CarePlanDialog(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

            // ケアプラン画面に遷移する
            $browser->loginAs($user)->visit(new CarePlan())
                ->waitForText('施設利用者S') // 対象利用者が表示されるまで待機する
                ->click('#table_facility_user_id1') //利用者を選択する
                ->pause(5000)
                ->value('#sp1_care_plan_period_start', '2022-01-01') // ケアプラン期間開始日を入力
                ->pause(5000)
                ->value('#sp1_care_plan_period_end', '2023-01-01') // ケアプラン期間終了日を入力
                ->pause(5000)
                ->click('#status_tmp') // 保存ボタンを押下
                ->pause(5000)
                ->assertVisible('@care_plan_dialog') // ダイアログの表示を確認
                ->click('#status_tmp_cancelbtn_service_plan1') // ダイアログの「いいえ」を押下
                ->assertMissing('@care_plan_dialog') // ダイアログが閉じられたことを確認
                ->click('#status_tmp') // 保存ボタンを押下
                ->pause(5000)
                ->assertVisible('@care_plan_dialog') // ダイアログの表示を確認
                ->click('#status_tmp_updatebtn_service_plan1') // ダイアログの「はい」を押下
                ->pause(10000)
                ->assertSeeIn('@tbody_end_date1', '2023/01/01'); // ケアプラン期間終了日を確認
        });
    }

    /**
     * 介護計画書1にて
     *
     * ・保存ボタン押下後の"service_plan_id"と"first_service_plan_id"の値
     * ・交付済ボタン押下後の"service_plan_id"と"first_service_plan_id"の値
     *
     * 上記が同じ値であることを確認する
     */
    public function testServicePlan1Delivery()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

            $servicePlanId = '4';

            $facilityUser = FacilityUser::find(1);
            $facilityUser->start_date = '2021-01-01';
            $facilityUser->save();

            // 認定情報変更
            $careInfo = UserCareInformation::where('facility_user_id', 1)->first();
            $careInfo->care_period_end = '2025-01-01';
            $careInfo->save();

            // ケアプラン画面に遷移する
            $browser->loginAs($user)->visit(new CarePlan())
                ->waitForText('施設利用者S') // 対象利用者が表示されるまで待機する
                ->click('#table_facility_user_id1') //利用者を選択する
                ->pause(10000)
                ->click('#next_plan_button') // 次回プラン作成ボタン押下
                ->pause(10000)
                ->click('@btn_continue') // 「継続」を選択
                ->value('#sp1_care_plan_period_start', '2023-01-01') // ケアプラン期間開始日を入力
                ->value('#sp1_care_plan_period_end', '2023-12-31') // ケアプラン期間終了日を入力
                ->value('#plan_start_period', '2022-10-10') // 作成日を入力
                ->value('#plan_end_period', 'テスト作成者') // 作成者を入力
                ->pause(5000)
                ->click('#status_tmp') // 保存ボタンを押下
                ->pause(15000)
                ->assertInputValue('#service_plan_id', $servicePlanId) // 値の確認
                ->assertInputValue('#first_service_plan_id', $servicePlanId) // 値の確認
                ->click('#status_done') // 交付済ボタンを押下
                ->pause(5000)
                ->assertVisible('#overflow_service_plan1_delivery_date') // ダイアログの表示を確認
                ->value('#delivery_date_consent', 'テスト作成者') // 同意者を入力
                ->click('#delivery_date_updatebtn_service_plan1') // ダイアログの「はい」を押下
                ->pause(10000)
                ->assertInputValue('#service_plan_id', $servicePlanId) // 値の確認
                ->assertInputValue('#first_service_plan_id', $servicePlanId); // 値の確認
        });
    }

    /**
     * サービス情報未設定のポップアップテスト
     */
    public function testPopup(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')
                ->first();

            $browser
                ->loginAs($user)
                // ケアプランタブへ遷移
                ->visit(new CarePlan())
                ->waitFor('.facility_user_td')
                // 種別なしユーザー選択
                ->click('#table_facility_user_id27')
                // 次回プラン作成ボタン押下
                ->click('#next_plan_button')
                // ポップアップ表示待機
                ->waitForText('利用者のサービス情報が登録されていない、もしくは入居日とサービス開始日が異なる日付のためケアプランが作成できません。')
                // メッセージ確認
                ->assertSee('利用者のサービス情報が登録されていない、もしくは入居日とサービス開始日が異なる日付のためケアプランが作成できません。')
                // ポップアップ閉じる
                ->click('#notificationbtn_service_plan1_create');
        });
    }

    /**
     *次回プラン作成ボタン押下時の挙動テスト
     */
    public function testNextPlanButton(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')
                ->first();

            // 「種別32外泊した人」に登録されている情報
            $startDate = '2000-12-31'; // 入居日
            $serviceStartDate = '2021-01-01'; // サービス有効開始日
            $certificationStatus =  2; //認定済
            $careLevel = '要介護５';
            $recognitionDate = '2020-04-01'; // 認定年月日
            $carePeriodStart = '2020-04-01'; // 有効開始日
            $carePeriodEnd = '2023-03-31'; // 有効終了日
            $today = Carbon::now('Asia/Tokyo')->format('Y-m-d');

            $firstPlanStartPeriod = '2000-04-01'; // ケアプラン交付済ユーザの初回施設サービス計画作成日
            $browser
                ->loginAs($user)
                // ケアプランタブへ遷移
                ->visit(new CarePlan())
                ->waitFor('.facility_user_td')
                // サービス情報あり　認定済み　のユーザー選択
                ->click('#table_facility_user_id36')
                ->waitUntilMissing('@update-dialog',10);
            $browser
                // 次回プラン作成ボタン押下
                ->click('#next_plan_button')
                ->waitUntilMissing('@update-dialog',10)
                // 作成日に今日の日付が入っているか確認
                ->assertInputValue('#plan_start_period', $today)
                // 初回施設サービス計画作成日に入居日が入っていることの確認
                ->assertInputValue('#first_plan_start_period', $startDate)
                // ケアプラン開始日が入居日・サービス有効開始日・認定情報有効開始日のうち
                // 最新日付が入力されているかどうか
                ->assertInputValue('#sp1_care_plan_period_start', $serviceStartDate)
                // 利用者情報ヘッダと同じ認定情報が表示されているか確認
                // 認定状況
                ->assertSelected('#certification_status', $certificationStatus)
                // 要介護度
                ->assertSelected('#care_level', $careLevel)
                // 認定年月日
                ->assertInputValue('#recognition_date', $recognitionDate)
                // 有効開始日
                ->assertInputValue('#care_period_start', $carePeriodStart)
                // 有効終了日
                ->assertInputValue('#care_period_end', $carePeriodEnd)
                //　チェックボックスがチェックされてることの確認
                ->assertChecked('#care_level_dispflg')
                //　チェックボックスが操作できないことの確認
                ->assertDisabled('#care_level_dispflg');

            $browser
                // 認定情報申請中の利用者を選択
                ->click('#table_facility_user_id83')
                // 対象利用者表示待機
                ->waitForText('認定情報申請中')
                // 次回プラン作成ボタン押下
                ->click('#next_plan_button')
                ->pause(2000)
                // 「認定年月日」「有効開始日」「有効終了日」が入力、選択不可であることの確認
                ->assertDisabled('#recognition_date')
                ->assertDisabled('#care_period_start')
                ->assertDisabled('#care_period_end')
                // チェックボックスがチェックされてること
                ->assertChecked('#care_level_dispflg')
                // チェック外す
                ->uncheck('#care_level_dispflg')
                // 申請中から認定済みに変更
                ->select('#certification_status',2)
                // 「認定年月日」「有効開始日」「有効終了日」が入力、選択可能であることの確認
                ->assertEnabled('#recognition_date')
                ->assertEnabled('#care_period_start')
                ->assertEnabled('#care_period_end')
                // チェックボックスがチェックされており操作不可であること
                ->assertChecked('#care_level_dispflg');

            // サービス種類未登録利用者が次回プランを作ろうとした際にアラートが表示されるかどうか
            $alertToNoneService = '<p> 利用者のサービス情報が登録されていない、もしくは入居日とサービス開始日が異なる日付のためケアプランが作成できません。</p>';
            $browser
                // サービス未登録の利用者を選択
                ->click('#table_facility_user_id27')
                // 次回プラン作成ボタン押下
                ->click('#next_plan_button')
                ->waitForText('利用者のサービス情報が登録されていない、もしくは入居日とサービス開始日が異なる日付のためケアプランが作成できません。')
                // サービス種類未登録通知のポップアップが表示されていること
                ->assertSourceHas($alertToNoneService)
                ->click('#notificationbtn_service_plan1_create');

            $browser
                // 交付済みケアプランがあるユーザ選択
                ->click('#table_facility_user_id86')
                ->waitUntilMissing('@update-dialog')
                // 次回プラン作成ボタン押下
                ->click('#next_plan_button')
                ->waitUntilMissing('@update-dialog')
                ->click('#notificationbtn_service_plan1_create')
                // 最新の初回施設サービス計画作成日が表示されていることの確認
                ->assertInputValue('#first_plan_start_period', $firstPlanStartPeriod);
        });
    }

    /**
     * 要介護度プルダウン選択肢確認
     */
    public function testCareLevelSelectList(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')
                ->first();

                $relationCareLevels = [
                    [
                        'facility_user' => '#table_facility_user_id36',
                        'care_level' => ['要介護１', '要介護２' ,'要介護３', '要介護４', '要介護５'],
                    ],
                    [
                        'facility_user' => '#table_facility_user_id20',
                        'care_level' => ['要支援２'],
                    ],
                    [
                        'facility_user' => '#table_facility_user_id21',
                        'care_level' => ['要介護１', '要介護２' ,'要介護３', '要介護４', '要介護５']
                    ],
                    [
                        'facility_user' => '#table_facility_user_id22',
                        'care_level' => ['要介護１', '要介護２' ,'要介護３', '要介護４', '要介護５']
                    ],
                    [
                        'facility_user' => '#table_facility_user_id25',
                        'care_level' => ['非該当', '要支援１' ,'要支援２']
                    ],
                    [
                        'facility_user' => '#table_facility_user_id39',
                        'care_level' => ['要介護１', '要介護２' ,'要介護３', '要介護４', '要介護５']
                    ],
                ];

                $browser
                    ->loginAs($user)
                    // ケアプランタブへ遷移
                    ->visit(new CarePlan())
                    ->waitFor('.facility_user_td');
                foreach ($relationCareLevels as $value) {
                    $browser
                        ->click($value['facility_user'])
                        ->pause(1000)
                        ->click('#next_plan_button')
                        ->pause(2000)
                        ->waitUntilMissing('@update-dialog')
                        ->assertSelectHasOptions('#care_level',$value['care_level']);
                }
        });
    }
}
