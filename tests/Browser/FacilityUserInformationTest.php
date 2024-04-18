<?php

namespace Tests\Browser;

use App\Models\FacilityUser;
use App\User;
use Carbon\Carbon;
use Faker\Factory;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\FacilityUserInformation;
use Tests\DuskTestCase;

/**
 * @group facility_user_information
 */
class FacilityUserInformationTest extends DuskTestCase
{
    /**
     * 利用者情報画面の基本情報タブ以外の各タブが遷移・クリックできないことをテストする。。(利用者情報なし)
     * @return void
     */
    public function testNoUserTabTransition(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'GH00002')->first();

            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                ->pause(3000)
                ->assertVisible('@facility-user-basic-form-label')
                // 各タグのstyle属性に期待値の内容が設定されていることを確認する
                // クリックイベント等は使用できないため、属性に設定された値で確認
                // 標準化後、CSSクラス指定に切り替える可能性アリ
                // 基本情報タブ
                ->assertAttribute('@facility-user-basic-button', 'style', 'color: rgb(250, 250, 250);')
                // サービスタブ
                ->assertAttribute('@facility-user-service-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 認定情報タブ
                ->assertAttribute('@facility-user-care-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 自立度タブ
                ->assertAttribute('@facility-user-independence-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 公費情報タブ
                ->assertAttribute('@facility-user-public-expenditure-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 給付率タブ
                ->assertAttribute('@facility-user-benefit-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 請求先情報タブ
                ->assertAttribute('@facility-user-billing-address-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 傷病名タブ
                ->assertAttribute('@facility-user-injury-and-illness-button', 'style', 'visibility: hidden; color: rgb(168, 168, 168); pointer-events: none;')
                // 基本摘要タブ
                ->assertAttribute('@facility-user-basic-abstract-button', 'style', 'visibility: hidden; color: rgb(168, 168, 168); pointer-events: none;');
        });
    }
    /**
     * 利用者情報画面のタブ遷移が正しく機能しているかをテストする。(利用者情報あり)
     * @return void
     */
    public function testUserTabTransition(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'hospital@0000000008.care-daisy.com')->first();

            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                // 基本情報タブボタンをクリックした場合、基本情報フォームが表示されること
                ->click('@facility-user-basic-button')
                ->waitFor('@facility-user-basic-form-label')
                ->assertVisible('@facility-user-basic-form-label')
                // サービスタブボタンをクリックした場合、サービスフォームが表示されること
                ->click('@facility-user-service-button')
                ->waitFor('@facility-user-service-form-label')
                ->assertVisible('@facility-user-service-form-label')
                // 認定情報タブボタンをクリックした場合、認定情報フォームが表示されること。
                ->click('@facility-user-care-button')
                ->waitFor('@facility-user-care-form-label')
                ->assertVisible('@facility-user-care-form-label')
                // 自立度タブボタンをクリックした場合、自立度フォームが表示されること
                ->click('@facility-user-independence-button')
                ->waitFor('@facility-user-independence-form-label')
                ->assertVisible('@facility-user-independence-form-label')
                // 公費情報タブボタンをクリックした場合、公費情報フォームが表示されること
                ->click('@facility-user-public-expenditure-button')
                ->waitFor('@facility-user-public-expenditure-form-label')
                ->assertVisible('@facility-user-public-expenditure-form-label')
                // 給付率タブボタンをクリックした場合、給付率フォームが表示されること
                ->click('@facility-user-benefit-button')
                ->waitFor('@facility-user-benefit-form-label')
                ->assertVisible('@facility-user-benefit-form-label')
                // 請求先情報タブボタンをクリックした場合、請求先情報フォームが表示されること
                ->click('@facility-user-billing-address-button')
                ->waitFor('@facility-user-billing-address-form-label')
                ->assertVisible('@facility-user-billing-address-form-label')
                // 傷病名タブボタンをクリックした場合、傷病名フォームが表示されること
                ->click('@facility-user-injury-and-illness-button')
                ->waitFor('@facility-user-injury-and-illness-label')
                ->assertVisible('@facility-user-injury-and-illness-label')
                // 基本摘要タブボタンをクリックした場合、基本摘要フォームが表示されること
                ->click('@facility-user-basic-abstract-button')
                ->waitFor('@facility-user-basic-abstract-form-label')
                ->assertVisible('@facility-user-basic-abstract-form-label');
        });
    }

    /**
     * 施設利用者の新規登録をテストする。
     * 新居ボタン押下に基本情報タブ以外の各タブが遷移・クリックできないことをテストする。
     * 利用者情報登録後のタブ遷移が正しく機能しているかをテストする。
     * @return void
     */
    public function testRegisterFacilityUser(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'hospital@0000000008.care-daisy.com')->first();

            // 施設利用者のテストデータを作成する。
            $faker = Factory::create('ja_JP');
            $afterStatus = rand(1, 8);
            $beforeStatus = rand(1, 9);
            $bloodType = rand(1, 5);
            $city = $faker->city;
            $dateTime = Carbon::now('Asia/Tokyo');
            // TODO: ブラウザがOSの言語設定からinput type dateの日付書式を判断していると見られたための仮実装。
            // $dateTimeMdy = $dateTime->format('mdY');
            $dateTimeYmd = $dateTime->format('Y/m/d');
            $firstName = $faker->firstName;
            $firstNameKana = $faker->firstKanaName;
            $gender = rand(1, 2);
            $insurerNumber = '141010'; // ランダム生成だと存在する保険者番号が作成されないので一旦指定する
            $lastName = $faker->lastName;
            $lastNameKana = $faker->lastKanaName;
            $fullName = $lastName . $firstName;
            $phoneNumber = $faker->phoneNumber;
            $postCode = substr_replace($faker->postcode, '-', 3, 0);
            $randomNumber10 = str_pad(rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
            $rhType = rand(1, 3);
            $sentence = $faker->sentence;
            $streetName = $faker->streetName;

            // 利用者情報画面に遷移する。
            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                // レイアウトが出来上がるまで待機
                ->waitFor('@facility-user-basic-form-label')
                //->waitFor('@facility-user-moving-into-button')
                // 基本情報が表示されているか確認
                ->assertVisible('@facility-user-basic-form-label')
                ->pause(10000)
                // 入居ボタンを押下する。
                ->click('@facility-user-moving-into-button')
                ->pause(3000)
                // 各タグのstyle属性に期待値の内容が設定されていることを確認する
                // クリックイベント等は使用できないため、属性に設定された値で確認
                // 標準化後、CSSクラス指定に切り替える可能性アリ
                // 基本情報タブ
                ->assertAttribute('@facility-user-basic-button', 'style', 'color: rgb(250, 250, 250);')
                // サービス情報タブ
                ->assertAttribute('@facility-user-service-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 認定情報タブ
                ->assertAttribute('@facility-user-care-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 自立度タブ
                ->assertAttribute('@facility-user-independence-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 公費情報タブ
                ->assertAttribute('@facility-user-public-expenditure-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 給付率タブ
                ->assertAttribute('@facility-user-benefit-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 請求先情報タブ
                ->assertAttribute('@facility-user-billing-address-button', 'style', 'color: rgb(168, 168, 168); pointer-events: none;')
                // 傷病名タブ
                ->assertAttribute('@facility-user-injury-and-illness-button', 'style', 'visibility: visible; color: rgb(168, 168, 168); pointer-events: none;')
                // 基本摘要タブ
                ->assertAttribute('@facility-user-basic-abstract-button', 'style', 'visibility: visible; color: rgb(168, 168, 168); pointer-events: none;')
                // 必須の値を埋める
                // 利用者姓
                ->type('@facility-user-form-last-name', $lastName)
                // 利用者名
                ->type('@facility-user-form-first-name', $firstName)
                // セイ（フリガナ）
                ->type('@facility-user-form-last-name-kana', $lastNameKana)
                // メイ（フリガナ）
                ->type('@facility-user-form-first-name-kana', $firstNameKana)
                // 性別
                ->radio('gender', $gender)
                // 生年月日
                ->type('@facility-user-form-birthday', $dateTimeYmd)
                // 被保険者番号
                ->type('@facility-user-form-insured-no', $randomNumber10)
                // 保険者番号
                ->type('@facility-user-form-insurer-no', $insurerNumber)
                // 入居日(利用開始)
                ->type('@facility-user-form-start-date', $dateTimeYmd)
                // 入居前の状況
                ->select('@facility-user-form-before-status', $beforeStatus)
                // 必須でない値を入力する。
                // 契約者番号
                ->type('@facility-user-contractor-number', $randomNumber10)
                // 血液型
                ->radio('blood_type', $bloodType)
                // RH
                ->radio('rh_type', $rhType)
                // 郵便番号
                ->type('@facility-user-form-postal-code', $postCode)
                // 住所1
                ->type('@facility-user-form-location-1', $city)
                // 住所2
                ->type('@facility-user-form-location-2', $streetName)
                // 電話番号
                ->type('@facility-user-form-phone-number', $phoneNumber)
                // 携帯番号
                ->type('@facility-user-form-cell-phone-number', $phoneNumber)
                // 退居日(利用終了)
                ->type('@facility-user-form-end-date', $dateTimeYmd)
                // 退居後の状況
                ->select('@facility-user-form-after-status', $afterStatus)
                // 診断日
                ->type('@facility-user-form-diagnosis-date', $dateTimeYmd)
                // 診断者
                ->type('@facility-user-form-diagnostician', $fullName)
                // 同意日
                ->type('@facility-user-form-consent-date', $dateTimeYmd)
                // 同意者
                ->type('@facility-user-form-consenter', $fullName)
                // 同意者連絡先
                ->type('@facility-user-form-consenter-phone-number', $phoneNumber)
                // 看取り日
                ->type('@facility-user-form-death-date', $dateTimeYmd)
                // 看取り理由
                ->type('@facility-user-form-death-reason', $sentence)
                //->screenshot('facility_user_information_test_register')
                // 保存ボタンを押下する
                ->click('@facility-user-save-button')
                ->pause(7000)
                // はいボタンを押下する
                ->click('@confirmation-dialog-button-yes')
                //->waitForReload()
                ->pause(10000)
                // 施設利用者リストに登録した施設利用者が存在していることを確認する。
                ->waitForText($fullName);
            // 登録した施設利用者が選択されていることを確認する
            // 新規追加された利用者情報のIDを取得する。
            $getNewUserId = $browser->attribute('.fu_table_selected_record', 'data-facility-user-id');
            // 選択されているユーザー名と登録したときのユーザー名が一致しているか確認
            $browser->assertSeeIn('#table_facility_user_id' . $getNewUserId . ' > td', $fullName)
                ->assertVisible('@facility-user-basic-form-label')
                // 登録した内容が表示されていることを確認する
                ->waitForTextIn('@facility-user-information-header', $fullName)
                ->assertInputValue('@facility-user-form-last-name', $lastName)
                ->assertInputValue('@facility-user-form-first-name', $firstName)
                ->assertInputValue('@facility-user-form-last-name-kana', $lastNameKana)
                ->assertInputValue('@facility-user-form-first-name-kana', $firstNameKana)
                ->waitFor('#bi_gender_1')
                ->assertRadioSelected('gender', $gender)
                ->assertInputValue('@facility-user-form-birthday', $dateTimeYmd)
                ->assertInputValue('@facility-user-form-insured-no', $randomNumber10)
                ->assertInputValue('@facility-user-form-insurer-no', $insurerNumber)
                ->assertInputValue('@facility-user-form-start-date', $dateTimeYmd)
                ->assertSelected('@facility-user-form-before-status', $beforeStatus)
                ->assertInputValue('@facility-user-contractor-number', $randomNumber10)
                ->assertRadioSelected('blood_type', $bloodType)
                ->assertRadioSelected('rh_type', $rhType)
                ->assertInputValue('@facility-user-form-postal-code', $postCode)
                ->assertInputValue('@facility-user-form-location-1', $city)
                ->assertInputValue('@facility-user-form-location-2', $streetName)
                ->assertInputValue('@facility-user-form-phone-number', $phoneNumber)
                ->assertInputValue('@facility-user-form-cell-phone-number', $phoneNumber)
                ->assertInputValue('@facility-user-form-end-date', $dateTimeYmd)
                ->assertSelected('@facility-user-form-after-status', $afterStatus)
                ->assertInputValue('@facility-user-form-diagnosis-date', $dateTimeYmd)
                ->assertInputValue('@facility-user-form-diagnostician', $fullName)
                ->assertInputValue('@facility-user-form-consent-date', $dateTimeYmd)
                ->assertInputValue('@facility-user-form-consenter', $fullName)
                ->assertInputValue('@facility-user-form-consenter-phone-number', $phoneNumber)
                ->assertInputValue('@facility-user-form-death-date', $dateTimeYmd)
                ->assertInputValue('@facility-user-form-death-reason', $sentence)
                //->screenshot('facility_user_information_test_register_result');
                // 基本情報タブボタンをクリックした場合、基本情報フォームが表示されること。
                ->click('@facility-user-basic-button')
                ->assertVisible('@facility-user-basic-form-label')
                // サービスタブボタンをクリックした場合、サービスフォームが表示されること。
                ->click('@facility-user-service-button')
                ->assertVisible('@facility-user-service-form-label')
                // 認定情報タブボタンをクリックした場合、認定情報フォームが表示されること。
                ->click('@facility-user-care-button')
                ->assertVisible('@facility-user-care-form-label')
                // 自立度タブボタンをクリックした場合、自立度フォームが表示されること。
                ->click('@facility-user-independence-button')
                ->assertVisible('@facility-user-independence-form-label')
                // 公費情報タブボタンをクリックした場合、公費情報フォームが表示されること。
                ->click('@facility-user-public-expenditure-button')
                ->assertVisible('@facility-user-public-expenditure-form-label')
                // 給付率タブボタンをクリックした場合、給付率フォームが表示されること。
                ->click('@facility-user-benefit-button')
                ->assertVisible('@facility-user-benefit-form-label')
                // 請求先情報タブボタンをクリックした場合、請求先情報フォームが表示されること。
                ->click('@facility-user-billing-address-button')
                ->assertVisible('@facility-user-billing-address-form-label')
                // 傷病名タブボタンをクリックした場合、傷病名フォームが表示されること。
                ->click('@facility-user-injury-and-illness-button')
                ->assertVisible('@facility-user-injury-and-illness-label')
                ->pause(2000)
                // 新規登録の場合、サービス情報を登録していないためモーダル表示されること。
                ->assertVisible('#overflow')
                ->assertSee('サービス種類の登録後、傷病名登録をお願いいたします。')
                ->click('.popup_cancel')
                ->pause(2000)
                // 基本摘要タブボタンをクリックした場合、基本摘要フォームが表示されること。
                ->click('@facility-user-basic-abstract-button')
                ->assertVisible('@facility-user-basic-abstract-form-label');
        });
    }

    /**
     * 退居済もしくは看取り日に日付が入っている利用者には
     * 認定情報期間アラートが表示されないようにする
     */
    public function testMovedOutUserApprovalAlertVisible(): void
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
                ->visit(new FacilityUserInformation())
                // 対象利用者が表示されるまで待機する
                ->waitForText($deathedUser)
                //看取り日入力済み利用者を選択する
                ->click('#table_facility_user_id6 > td')
                ->pause(1000)
                // 看取り日が空欄でないことをチェックする
                ->assertInputValueIsNot('@facility-user-form-death-date', '')
                // 対象の利用者に認定情報アラートが表示されていないことをチェックする
                ->assertMissing('.fu_table_selected_cell > span')
                // 退居済利用者を選択する
                ->click('#table_facility_user_id81 > td')
                ->pause(1000)
                // 退居日が空欄でないことをチェックする
                ->assertInputValueIsNot('@facility-user-form-end-date', '')
                // 対象の利用者に認定情報アラートが表示されていないことをチェックする
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
                ->visit(new FacilityUserInformation())
                ->waitFor('.facility_user_td');

            // サイドバー最上部の利用者の認定情報を取得
            $userId = $browser->attribute('#user_info_fu_tbody > tr', 'data-facility-user-id');
            $careInfo = FacilityUser::with('careInformations')->find($userId)->careInformations[0];

            // 「残り2ヶ月」を表示させるために期限日を更新し、文言チェック
            $careInfo->care_period_end = today()->addMonth()->addDay();
            $careInfo->save();
            $browser->visit(new FacilityUserInformation())
                ->waitFor('#user_info_fu_tbody span.alert_two_months')
                ->mouseover('#user_info_fu_tbody span.alert_two_months')
                ->waitForText('介護認定期限切れまで残り2ヶ月です。')
                ->assertSee('介護認定期限切れまで残り2ヶ月です。');

            // 「残り30日」を表示させるために期限日を更新し、文言チェック
            $careInfo->care_period_end = today()->addDays(30);
            $careInfo->save();
            $browser->visit(new FacilityUserInformation())
                ->waitFor('#user_info_fu_tbody span.alert_one_month')
                ->mouseover('#user_info_fu_tbody span.alert_one_month')
                ->waitForText('介護認定期限切れまで残り30日です。')
                ->assertSee('介護認定期限切れまで残り30日です。');

            // 「残り1日」を表示させるために期限日を更新し、文言チェック
            $careInfo->care_period_end = today()->addDay();
            $careInfo->save();
            $browser->visit(new FacilityUserInformation())
                ->waitFor('#user_info_fu_tbody span.alert_one_month')
                ->mouseover('#user_info_fu_tbody span.alert_one_month')
                ->waitForText('介護認定期限切れまで残り1日です。')
                ->assertSee('介護認定期限切れまで残り1日です。');

            // 「当日です」を表示させるために期限日を更新し、文言チェック
            $careInfo->care_period_end = today();
            $careInfo->save();
            $browser->visit(new FacilityUserInformation())
                ->waitFor('#user_info_fu_tbody span.alert_today')
                ->mouseover('#user_info_fu_tbody span.alert_today')
                ->waitForText('当日です。')
                ->assertSee('当日です。');

            // テストデータの変更で外部に影響が出ないようにもとの値に戻す
            $careInfo->care_period_end = $careInfo->getOriginal('care_period_end');
            $careInfo->save();
        });
    }

    /**
     * 申請中と認定済が混在する認定情報レコードを持つ
     * 利用者のリスト表示アイコンが申請中アイコンとなっていることを確認する。
     */
    public function testApprovalProcessedAlertCheck(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')
                ->first();
            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                ->pause(2000)
                // 認定情報に認定済と申請中が存在する利用者を使用する
                ->click('@tr_facility_user_id34')
                ->pause(2000)
                // 「介護認定を申請中です」の文言チェック
                ->waitFor('.fu_table_selected_record span.shinsei')
                ->mouseover('.fu_table_selected_record span.shinsei')
                ->waitForText('介護認定を申請中です。')
                ->assertSee('介護認定を申請中です。');
        });
    }

    /**
     * 利用者情報の基本情報タブにて保存ボタン押下後のポップアップメッセージをテストする。
     *
     * @return void
     */
    public function testSaveBtnClickPopUpText()
    {
        $this->browse(function (Browser $browser) {
            // 入力日
            $dateTime = Carbon::now('Asia/Tokyo');
            // 看取り日の設定値
            $deathDate = $dateTime->copy()->format('Y/m/d');

            $updateDialog = '#bi_dialog_window';
            $insertDialog = 'この内容で保存しますか';
            $checkMessagePattern1 = '<p id="bi_dialog_msg">変更した内容を更新しますか？</p>';
            $checkMessagePattern2 = '<p id="bi_dialog_msg">入居日が現在より離れていますが保存しますか</p>';
            $checkMessagePattern3 = '<p id="bi_dialog_msg">・入居日が現在より離れています<br>・退居日 と 退居後の状況の入力がありません<br>このまま内容を保存してよろしいですか？</p>';
            $checkMessagePattern4 = '<p id="bi_dialog_msg">退居日が現在より離れていますが保存しますか</p>';
            $checkMessagePattern6 = '<p id="bi_dialog_msg">退居日 と 退居後の状況の入力がありませんが、<br>このまま内容を保存してよろしいですか</p>';
            $checkMessageInsert = '<p class="caredaisy_confirmation_dialog_message">この内容で保存しますか</p>';

            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')
                ->first();

            // 利用者情報画面に遷移する
            $browser->loginAs($user)
                ->visit(new FacilityUserInformation());
            // レイアウト（基本情報）が出来上がるまで待機
            $browser->waitFor('@facility-user-basic-form-label');

            /**
             * 更新の場合パターン1
             * メッセージチェック：『変更した内容を更新しますか？』
             */
            $startDate = $dateTime->copy()->addMonth(6)->format('Y/m/d');
            $endDate = $dateTime->copy()->addMonth(6)->format('Y/m/d');
            $browser
                // 入居日・退居日・看取り日に入力値を設定
                ->pause(1000)
                ->type('@facility-user-form-start-date', $startDate)
                ->type('@facility-user-form-end-date', $endDate)
                ->type('@facility-user-form-death-date', '')
                // 保存ボタンを押下する
                ->click('@facility-user-save-button')
                // ポップアップメッセージのチェック
                ->waitFor($updateDialog, 10)
                ->assertSourceHas($checkMessagePattern1)
                ->click('#bi_dialog_no');

            /**
             * 更新の場合パターン2
             * メッセージチェック：『入居日が現在より離れていますが保存しますか』
             * 条件1：入居日が更新日+6カ月より未来日かつ、2099/12/31の範囲内か
             * 退居日・看取り日未入力
             */
            // 入居日の設定値
            $startDate = $dateTime->copy()->addMonth(6)->addDay()->format('Y/m/d');
            $browser
                // 入居日・退居日・看取り日に入力値を設定
                ->type('@facility-user-form-start-date', $startDate)
                ->type('@facility-user-form-end-date', '')
                ->type('@facility-user-form-death-date', '')
                // 保存ボタンを押下する
                ->click('@facility-user-save-button')
                // ポップアップメッセージのチェック
                ->waitFor($updateDialog, 10)
                ->assertSourceHas($checkMessagePattern2)
                ->click('#bi_dialog_no');

            /**
             * 更新の場合パターン3
             * メッセージチェック：\
             * 『・入居日が現在より離れています
             *   ・退居日 と 退居後の状況の入力がありません
             *    このまま内容を保存してよろしいですか？』
             * 条件1：入居日が更新日+6カ月より未来日かつ、2099/12/31の範囲内か
             * 条件2：退居日を未入力かつ、看取り日を設定
             */

            $browser
                // 入居日・退居日・看取り日に値を設定
                ->type('@facility-user-form-start-date', $startDate)
                ->type('@facility-user-form-end-date', '')
                ->type('@facility-user-form-death-date', $deathDate)
                // 保存ボタンを押下する
                ->click('@facility-user-save-button')
                // ポップアップメッセージのチェック
                ->waitFor($updateDialog, 10)
                ->assertSourceHas($checkMessagePattern3)
                ->click('#bi_dialog_no');

            /**
             * 更新の場合パターン4
             * メッセージチェック：『退居日が現在より離れていますが保存しますか』
             * 条件1：退居日が更新日+6カ月より未来日かつ、2099/12/31の範囲内か
             * 条件2：看取り日未設定
             */
            // 入居日の設定値
            $startDate = $dateTime->copy()->format('Y/m/d');
            // 退居日の設定値（入力当日から6か月後）
            $endDate = $dateTime->copy()->addMonth(6)->addDay()->format('Y/m/d');
            // 入居日・退居日・看取り日に値を設定
            $browser
                // 入居日・退居日・看取り日に値を設定
                ->type('@facility-user-form-start-date', $startDate)
                ->type('@facility-user-form-end-date', $endDate)
                ->type('@facility-user-form-death-date', '')
                // 保存ボタンを押下する
                ->click('@facility-user-save-button')
                // ポップアップメッセージのチェック
                ->waitFor($updateDialog, 10)
                ->assertSourceHas($checkMessagePattern4)
                ->click('#bi_dialog_no');

            /**
             * 更新の場合パターン5
             * メッセージチェック：『退居日が現在より離れていますが保存しますか』
             * 条件1：退居日が更新日+6カ月より未来日かつ、2099/12/31の範囲内か
             * 条件2：看取り日設定
             */
            $browser
                // 入居日・退居日・看取り日に値を設定
                ->type('@facility-user-form-start-date', $startDate)
                ->type('@facility-user-form-end-date', $endDate)
                ->type('@facility-user-form-death-date', $deathDate)
                // 保存ボタンを押下する
                ->click('@facility-user-save-button')
                // ポップアップメッセージのチェック
                ->waitFor($updateDialog, 10)
                ->assertSourceHas($checkMessagePattern4)
                ->click('#bi_dialog_no');

            /**
             * 更新の場合パターン6
             * メッセージチェック：
             * 『退居日 と 退居後の状況の入力がありませんが、
             *  このまま内容を保存してよろしいですか』
             * 条件：退居日のみ未設定
             */
            $browser
                // 入居日・退居日・看取り日に値を設定
                ->type('@facility-user-form-start-date', $startDate)
                ->type('@facility-user-form-end-date', '')
                ->type('@facility-user-form-death-date', $deathDate)
                // 保存ボタンを押下する
                ->click('@facility-user-save-button')
                // ポップアップメッセージのチェック(2行表示のため分けております。)
                ->waitFor($updateDialog, 10)
                ->assertSourceHas($checkMessagePattern6)
                ->click('#bi_dialog_no');

            /**
             * 入居の場合
             * メッセージチェック：『この内容で保存しますか』
             */
            $browser
                // 入居ボタンを押下
                ->click('@facility-user-moving-into-button')
                ->pause(1000)
                // 入居日・退居日・看取り日に値を設定
                ->type('@facility-user-form-start-date', $startDate)
                ->type('@facility-user-form-end-date', $endDate)
                ->type('@facility-user-form-death-date', $deathDate)
                // 保存ボタンを押下する
                ->click('@facility-user-save-button')
                // ポップアップメッセージのチェック
                // waitForで対象のダイアログのクラスをセレクタに指定しましたがうまくいかないため、waitForTextにしております。
                ->waitForText($insertDialog, 10)
                ->assertSourceHas($checkMessageInsert)
                ->click('@confirmation-dialog-button-no');
        });
    }

    /**
     * 利用者情報⇒基本情報のバリデーションメッセージチェック
     * バリデーションメッセージチェックの共通
     *
     * @return void
     */
    public function testValidationMessage()
    {
        $this->browse(function (Browser $browser) {

            // 入力日
            $dateTime = Carbon::now('Asia/Tokyo');
            // 入居日の設定値
            $startDate = $dateTime->copy()->format('Y/m/d');
            // 看取り日の設定値(入居日の前日)
            $deathDate = $dateTime->copy()->subDay()->format('Y/m/d');

            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')
                ->first();
            // 利用者情報画面に遷移する
            $browser->loginAs($user)
                ->visit(new FacilityUserInformation());
            // レイアウト（基本情報）が出来上がるまで待機
            $browser->waitFor('@facility-user-basic-form-label');

            /**
             * 看取り日が入居日より前に設定されている場合のエラーメッセージ確認
             * メッセージ：看取り日は入居日以降の日付を入力してください。
             */
            $checkMessage = '看取り日は入居日以降の日付を入力してください。';
            // 更新時
            $browser
                // 入居日と看取り日を入力
                ->pause(1000)
                ->type('@facility-user-form-start-date', $startDate)
                ->type('@facility-user-form-death-date', $deathDate)
                // 保存ボタンを押下する
                ->click('@facility-user-save-button')
                ->waitFor('#bi_dialog_window', 10)
                // 確認ポップアップの「はい」を押下
                ->click('#bi_dialog_yes')
                ->pause(3000)
                // メッセージのチェック（含まれているかのチェックでいいため、assertSourceHasではなく、assertSeeIn使用）
                ->assertSeeIn('#validateErrorsBasicInfo', $checkMessage);

            // 入居時
            $browser
                // 入居ボタンを押下
                ->click('@facility-user-moving-into-button')
                ->pause(1000)
                // 入居日と看取り日を入力
                ->type('@facility-user-form-start-date', $startDate)
                ->type('@facility-user-form-death-date', $deathDate)
                // 保存ボタンを押下する
                ->click('@facility-user-save-button')
                ->waitForText('この内容で保存しますか', 10)
                // 確認ポップアップの「はい」を押下
                ->click('@confirmation-dialog-button-yes')
                // メッセージのチェック（含まれているかのチェックでいいため、assertSourceHasではなく、assertSeeIn使用）
                ->pause(3000)
                ->assertSeeIn('#validateErrorsBasicInfo', $checkMessage);
        });
    }

    /**
     * 和暦表示のテスト
     * 認定情報画面遷移後、再度基本情報画面に戻った時の各和暦表示の確認
     */
    public function testDisplayedJapaneseCalendarNotApproval()
    {
        $this->browse(function (Browser $browser) {
            // ログイン、基本情報画面へ遷移する
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')
                ->first();
            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                ->pause(2000);

            // 生年月日
            $idBirthday = '#jaCalBirthday';
            $sourceBirthday = '<span id="jaCalBirthday" class="bi_date_input">明治33</span>';
            // 入居日(利用開始)
            $sourceMovingInDate = '<span id="jaCalMovingInDate" class="bi_date_input">令和4</span>';
            // 退居日(利用終了)
            $sourceMovingOutDate = '<span id="jaCalMovingOutDate" class="bi_date_input">令和4</span>';
            // 診断日
            $sourceDiagnosisDate = '<span id="jaCalDiagnosisDate" class="bi_date_input">令和4</span>';
            // 同意日
            $sourceConsentDate = '<span id="jaCalConsentDate" class="bi_date_input">令和4</span>';
            // 看取り日
            $sourceDeathDate = '<span id="jaCalDeathDate" class="bi_date_input">令和4</span>';

            // 認定情報画面遷移後、再度基本情報画面に戻った時の各和暦表示の確認
            $browser
                // 基本情報全項目入力済、認定情報なしの利用者を使用する
                ->click('@tr_facility_user_id84')
                ->pause(2000)
                // 入力済の日付に対して正しく和暦が表示されていることを確認する
                ->waitFor($idBirthday)
                ->assertSourceHas($sourceBirthday)
                ->assertSourceHas($sourceMovingInDate)
                ->assertSourceHas($sourceMovingOutDate)
                ->assertSourceHas($sourceDiagnosisDate)
                ->assertSourceHas($sourceConsentDate)
                ->assertSourceHas($sourceDeathDate)
                // 認定情報画面へ遷移する
                ->waitFor('@facility-user-care-button')
                ->click('@facility-user-care-button')
                ->pause(1000)
                ->waitFor('@facility-user-care-form-label')
                ->assertVisible('@facility-user-care-form-label')
                // 再度基本情報画面へ遷移する
                ->waitFor('@facility-user-basic-button')
                ->click('@facility-user-basic-button')
                ->pause(1000)
                ->waitFor('@facility-user-basic-form-label')
                ->assertVisible('@facility-user-basic-form-label')
                // 入力済の日付に対して正しく和暦が表示されていることを再度確認する
                ->waitFor($idBirthday)
                ->assertSourceHas($sourceBirthday)
                ->assertSourceHas($sourceMovingInDate)
                ->assertSourceHas($sourceMovingOutDate)
                ->assertSourceHas($sourceDiagnosisDate)
                ->assertSourceHas($sourceConsentDate)
                ->assertSourceHas($sourceDeathDate);
        });
    }

    /**
     * 和暦表示のテスト
     * 認定情報画面で新規登録ボタンを押下後、再度基本情報画面に戻った時の各和暦表示の確認
     */
    public function testDisplayedJapaneseCalendarApproval()
    {
        $this->browse(function (Browser $browser) {
            // ログイン、基本情報画面へ遷移する
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')
                ->first();
            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                ->pause(2000);

            // 生年月日
            $idBirthday = '#jaCalBirthday';
            $sourceBirthday = '<span id="jaCalBirthday" class="bi_date_input">明治33</span>';
            // 入居日(利用開始)
            $sourceMovingInDate = '<span id="jaCalMovingInDate" class="bi_date_input">令和4</span>';
            // 退居日(利用終了)
            $sourceMovingOutDate = '<span id="jaCalMovingOutDate" class="bi_date_input">令和4</span>';
            // 診断日
            $sourceDiagnosisDate = '<span id="jaCalDiagnosisDate" class="bi_date_input">令和4</span>';
            // 同意日
            $sourceConsentDate = '<span id="jaCalConsentDate" class="bi_date_input">令和4</span>';
            // 看取り日
            $sourceDeathDate = '<span id="jaCalDeathDate" class="bi_date_input">令和4</span>';
            // 認定年月日
            $idApprovalDate = '#jaCalApprovalDate';
            $sourceApprovalDate = '<span id="jaCalApprovalDate" class=" disabled_target_date">令和4</span>';
            // 有効開始日
            $sourceApprovalStartDate = '<span id="jaCalApprovalStartDate" class=" disabled_target_date">令和4</span>';
            // 有効終了日
            $sourceApprovalEndDate = '<span id="jaCalApprovalEndDate" class=" disabled_target_date">令和4</span>';
            // 保険証確認日
            $sourceDateCfmInsCard = '<span id="jaCalDateCfmInsCard">令和4</span>';
            // 交付年月日
            $sourceDateQualification = '<span id="jaCalDateQualification">令和4</span>';

            $deleteString = '令和4';

            // 認定情報画面で新規登録ボタンを押下後、再度基本情報画面に戻った時の各和暦表示の確認
            $browser
                // 基本情報全項目入力済、認定情報ありの利用者を使用する
                ->click('@tr_facility_user_id85')
                ->pause(2000)
                // 入力済の日付に対して正しく和暦が表示されていることを確認する
                ->waitFor($idBirthday)
                ->assertSourceHas($sourceBirthday)
                ->assertSourceHas($sourceMovingInDate)
                ->assertSourceHas($sourceMovingOutDate)
                ->assertSourceHas($sourceDiagnosisDate)
                ->assertSourceHas($sourceConsentDate)
                ->assertSourceHas($sourceDeathDate)
                // 認定情報画面へ遷移し、和暦表示を確認する
                ->waitFor('@facility-user-care-button')
                ->click('@facility-user-care-button')
                ->pause(1000)
                ->waitFor('@facility-user-care-form-label')
                ->assertVisible('@facility-user-care-form-label')
                ->waitFor($idApprovalDate)
                ->assertSourceHas($sourceApprovalDate)
                ->assertSourceHas($sourceApprovalStartDate)
                ->assertSourceHas($sourceApprovalEndDate)
                ->assertSourceHas($sourceDateCfmInsCard)
                ->assertSourceHas($sourceDateQualification)
                // 新規登録ボタン押下後、和暦表示を確認する
                ->waitFor('@clearBtn_approval')
                ->click('@clearBtn_approval')
                ->pause(1000)
                ->assertSourceHas(str_replace($deleteString, '', $sourceApprovalDate))
                ->assertSourceHas(str_replace($deleteString, '', $sourceApprovalStartDate))
                ->assertSourceHas(str_replace($deleteString, '', $sourceApprovalEndDate))
                ->assertSourceHas(str_replace($deleteString, '', $sourceDateCfmInsCard))
                ->assertSourceHas(str_replace($deleteString, '', $sourceDateQualification))
                // 再度基本情報画面へ遷移する
                ->waitFor('@facility-user-basic-button')
                ->click('@facility-user-basic-button')
                ->pause(1000)
                ->waitFor('@facility-user-basic-form-label')
                ->assertVisible('@facility-user-basic-form-label')
                // 入力済の日付に対して正しく和暦が表示されていることを再度確認する
                ->waitFor($idBirthday)
                ->assertSourceHas($sourceBirthday)
                ->assertSourceHas($sourceMovingInDate)
                ->assertSourceHas($sourceMovingOutDate)
                ->assertSourceHas($sourceDiagnosisDate)
                ->assertSourceHas($sourceConsentDate)
                ->assertSourceHas($sourceDeathDate);
        });
    }
}
