<?php

namespace Tests\Browser;

use App\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\ResultInformation;
use Tests\DuskTestCase;

/**
 * @group result_information
 */
class ResultInformationTest extends DuskTestCase
{
    /**
     * 実績情報画面のタブ遷移が正しく機能しているかをテストする。
     * @return void
     */
    public function testTabTransition(): void
    {
        $this->browse(function (Browser $browser) {
            // TODO: 定数ファイル作成のタイミングでテストユーザーの情報を置き換える。
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

            $facilityUserName = '施設利用者A';

            // 非同期処理を多用しているためwaitが多めになっている。
            $browser
                ->loginAs($user)
                ->visit(new ResultInformation())
                ->waitForText($facilityUserName)
                // 外泊日登録タブボタンをクリックした場合、外泊日登録フォームが表示されることをテストする。
                ->click('@stay-out-button')
                ->waitFor('@stay-out-form-label')
                // 実績登録タブボタンをクリックした場合、実績登録フォームが表示されることをテストする。
                ->click('@service-result-button')
                ->waitFor('@result-registration-label')
                // 国保連請求タブボタンをクリックした場合、国保連請求フォームが表示されることをテストする。
                ->click('@national-health-button')
                ->waitFor('@national-health-form-label')
                // 保険外請求タブボタンをクリックした場合、保険外請求フォームが表示されることをテストする。
                ->click('@facility-user-billing-button')
                ->waitFor('@facility-user-billing-form-label');
        });
    }

    /**
     * 保険外請求画面の品目追加モーダルが正しく表示されているかのテストを行う
     * @return void
     */
    public function testItemAdditionalModal(): void
    {
        $this->browse(function (Browser $browser) {
            // TODO: 定数ファイル作成のタイミングでテストユーザーの情報を置き換える。
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

            // テストデータ作成
            $facilityUserName = '施設利用者A';

            $browser
                ->loginAs($user)
                ->visit(new ResultInformation())
                ->waitForText($facilityUserName)
                ->click('@facility-user-billing-button')
                ->waitFor('@facility-user-billing-form-label')
                ->pause(2000)
                // 保険外請求の「+」押下、保険外請求モーダルが表示されることをテスト。
                // モーダルの表示を確認する
                ->click('@un-table-button')
                ->assertVisible('@un-table-form')
                ->screenshot('uninsured-item-modal');

            $browser
                // モーダル表示時のラジオボタン初期選択のチェック、「品目リストから選ぶ」が選択されていることの確認
                ->assertRadioSelected('modal_radio', 'list')
                // 正常にプルダウンが表示されているか確認
                ->assertVisible('@select-item')
                // プルダウンの中身が未選択(初期値)になっているかの確認
                ->assertSelected('#un_table_s_item_pulldown', '')
                // 追加品目の入力項目の非表示の確認
                ->assertMissing('@add-item')

                // モーダルのラジオボタンの選択
                // 品目追加のラジオボタンの選択、モーダルの内容すべて表示されているかの確認
                ->click('@select-add-radio')
                ->assertVisible('@select-item')
                ->assertVisible('@add-item')

                // 品目リストから選ぶのラジオボタンの選択
                ->click('@select-list-radio')
                // 追加品目の入力項目の非表示の確認
                ->assertMissing('@add-item')
                // 正常にプルダウンが表示されているか確認
                ->assertVisible('@select-item')
                // プルダウンの中身が未選択(初期値)になっているかの確認
                ->assertSelected('#un_table_s_item_pulldown', '');
        });
    }

    /**
     * 再請求ボタン押下時に要介護度が非該当の利用者が非表示になっていることをテストする
     * @return void
     */
    public function testNotApplicableUserVisible(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')
                ->first();

            $facilityUserName = '種別35非該当';

            $browser
                ->loginAs($user)
                ->visit(new ResultInformation())
                ->waitForText($facilityUserName)
                // 再請求モードにする
                ->click('@rb-btn')
                ->waitForDialog()
                ->acceptDialog()
                ->pause(2000)
                // 再請求モードになっているかチェック
                ->assertSeeIn('@rb-btn', '通常')
                // 非該当の利用者が表示されていないことをチェック
                ->assertDontSee($facilityUserName)
                // 通常モードに戻す
                ->click('@rb-btn')
                ->waitForDialog()
                ->acceptDialog()
                ->pause(2000)
                // 通常モードになっているかチェック
                ->assertSeeIn('@rb-btn', '再請求')
                // 非該当の利用者が表示されているかチェック
                ->assertSee($facilityUserName);
        });
    }

    /**
     * 発行日入力モーダルの確定ボタンを押した際に、
     * モーダルが自動で閉じることをテストする。
     *
     * @return void
     */
    public function testClosingIssueDateModal(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

            // ログインして実績情報画面へ遷移
            $browser
                ->loginAs($user)
                ->visit(new ResultInformation());

            // 利用者全員分の請求書を発行する
            $browser
                // ウィンドウ最大化
                ->maximize()
                ->pause(2000)
                ->click('@facility-user-billing-button')
                ->pause(1000);
                
            if (preg_match('/承認する/', $browser->text('#add_agreement') )) {
                // 保険外請求を承認する
                $browser
                    ->click('#add_agreement')
                    ->waitForDialog()
                    ->acceptDialog()
                    ->pause(1000);
            }

            $browser
                ->click('@stay-out-button')
                // サイドバーのデータ出力ボタンにマウスオーバー
                ->waitForText('外泊情報一覧')
                ->mouseover('@show-output-dropdown-list')
                // 利用者全員の請求書出力ボタンにマウスオーバー
                ->waitFor('@show-usage-fee-modal-buttton')
                ->mouseover('@show-usage-fee-modal-buttton')
                // モーダル表示ボタンをクリック
                ->waitFor('#dep_usage_fee_invoice')
                ->click('#dep_usage_fee_invoice')
                // 確定ボタンをクリック
                ->waitFor('#dep_form_submit')
                ->click('#dep_form_submit')
                // モーダルが閉じられていることを確認
                ->pause(1000)
                ->assertMissing('#dep_table_s_item_form');
        });
    }

    /**
     * 承認・承認解除ボタン押下後に実績登録タブと保険外請求タブに表示される
     * 利用者情報ヘッダの承認・非承認が更新されることをテストする
     * テストが安定しないので一旦コメントアウトする
     */
    // public function testFacilityUserInfoHeaderReloadByApproval(): void
    // {
    //     $this->browse(function (Browser $browser) {
    //         $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

    //         $facilityUserName = '施設利用者A';
    //         $careLevel = '要介護４';
    //         $approvalSource = '<div class="facility_user_info_text facility_user_info_header_aggrement">承認</div>';
    //         $unApprovalSource = '<div class="facility_user_info_text facility_user_info_header_aggrement">未承認</div>';

    //         $browser
    //             ->loginAs($user)
    //             ->visit(new ResultInformation())
    //             ->waitForText($facilityUserName)
    //             // 対象月を2021/09に変更する
    //             ->select('@year-month-pulldown', '2021/09')
    //             ->pause(1000)
    //             // 実績登録タブに遷移する。
    //             ->click('@service-result-button')
    //             ->waitFor('@result-registration-label')
    //             // 利用者情報ヘッダの【承認状況】が「承認」になっていることをチェックする
    //             ->assertSourceHas($approvalSource)
    //             // 国保連請求タブに遷移する。
    //             ->click('@national-health-button')
    //             ->waitFor('@national-health-form-label')
    //             // 承認解除ボタンを押下する
    //             ->click('#nh_agreement_cancel_btn')
    //             ->pause(1000)
    //             // 実績登録タブに遷移する。
    //             ->click('@service-result-button')
    //             ->waitFor('@result-registration-label')
    //             // 利用者情報ヘッダの【承認状況】が「未承認」になっていることをチェックする
    //             ->waitForText($careLevel)
    //             ->assertSourceHas($unApprovalSource)
    //             // 国保連請求タブに遷移する。
    //             ->click('@national-health-button')
    //             ->waitFor('@national-health-form-label')
    //             // 承認ボタンを押下する
    //             ->click('@national-helath-agreement-ok')
    //             ->pause(1000)
    //             // 保険外請求タブタブに遷移する。
    //             ->click('@facility-user-billing-button')
    //             ->waitFor('@facility-user-billing-form-label')
    //             ->waitForText($careLevel)
    //             // 利用者情報ヘッダの【承認状況】が「承認」になっていることをチェックする
    //             ->assertSourceHas($approvalSource);
    //     });
    // }

    /**
     * 対象月を変更した際に利用者情報ヘッダの【承認状況】がリロードされることをテストする
     * テストが安定しないので一旦コメントアウトする
     */
    // public function testFacilityUserInfoHeaderReloadByChangeMonth(): void
    // {
    //     $this->browse(function (Browser $browser) {
    //         $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

    //         $facilityUserName = '施設利用者A';
    //         $careLevel = '要介護４';
    //         $approvalSource = '<div class="facility_user_info_text facility_user_info_header_aggrement">承認</div>';
    //         $unApprovalSource = '<div class="facility_user_info_text facility_user_info_header_aggrement">未承認</div>';

    //         $browser
    //             ->loginAs($user)
    //             ->visit(new ResultInformation())
    //             ->waitForText($facilityUserName)
    //             // 実績登録タブに遷移する。
    //             ->click('@service-result-button')
    //             ->waitFor('@result-registration-label')
    //             // 利用者情報ヘッダの【承認状況】が「未承認」になっていることをチェックする
    //             ->waitForText($careLevel)
    //             ->assertSourceHas($unApprovalSource)
    //             // 対象月を2021/09に変更する
    //             ->select('@year-month-pulldown', '2021/09')
    //             ->pause(1000)
    //             // 利用者情報ヘッダの【承認状況】が「承認」になっていることをチェックする
    //             ->assertSourceHas($approvalSource);
    //     });
    // }

    /**
     * データ出力をマウスオーバーし
     * 請求書・領収書出力の項目が正しく表示されるかをテストする。
     * @return void
     */
    public function testExtractBillsAndReceipts(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();

            // ログインして実績情報画面へ遷移
            $browser
                ->loginAs($user)
                ->visit(new ResultInformation());

            $browser
                // ウィンドウ最大化
                ->maximize()
                ->pause(2000)
                ->click('@facility-user-billing-button')
                ->pause(1000);

            if (preg_match('/承認する/', $browser->text('#add_agreement') )) {
                // 保険外請求を承認する
                $browser
                    ->click('#add_agreement')
                    ->waitForDialog()
                    ->acceptDialog();
            }

            $browser
                // 外泊日登録画面
                ->pause(1000)
                ->click('@stay-out-button')
                ->waitForText('外泊情報一覧')

                // 利用料請求書全員
                // サイドバーのデータ出力ボタンにマウスオーバー
                ->mouseover('@show-output-dropdown-list')
                // 利用料請求書出力ボタンにマウスオーバー
                ->waitFor('@show-usage-fee-modal-buttton')
                ->mouseover('@show-usage-fee-modal-buttton')
                ->pause(1000)
                // 利用者全員を出力ボタンをクリック
                ->waitFor('@dep_usage_fee_invoice')
                ->click('@dep_usage_fee_invoice')
                // 確定ボタンをクリック
                ->waitFor('#dep_form_submit')
                ->assertVisible('#dep_form_submit')
                ->click('#dep_form_submit')
                // モーダルが閉じられていることを確認
                ->pause(1000)
                ->assertMissing('#dep_table_s_item_form')

                // 利用者請求書個別
                // サイドバーのデータ出力ボタンにマウスオーバー
                ->mouseover('@show-output-dropdown-list')
                // 利用料請求書出力ボタンにマウスオーバー
                ->waitFor('@show-usage-fee-modal-buttton')
                ->mouseover('@show-usage-fee-modal-buttton')
                ->pause(1000)
                // 選択中の利用者出力ボタンをクリック
                ->waitFor('@dep_usage_fee_invoice_individual')
                ->click('@dep_usage_fee_invoice_individual')
                // 確定ボタンをクリック
                ->waitFor('#dep_form_submit')
                ->assertVisible('#dep_form_submit')
                ->click('#dep_form_submit')
                // モーダルが閉じられていることを確認
                ->pause(1000)
                ->assertMissing('#dep_table_s_item_form')

                // 利用料領収書全員
                // サイドバーのデータ出力ボタンにマウスオーバー
                ->mouseover('@show-output-dropdown-list')
                // 利用料領収書出力ボタンにマウスオーバー
                ->waitFor('@show-usage-receipt-modal-buttton')
                ->mouseover('@show-usage-receipt-modal-buttton')
                ->pause(1000)
                // 利用者全員を出力ボタンをクリック
                ->waitFor('@dep_usage_fee_receipt')
                ->click('@dep_usage_fee_receipt')
                // 確定ボタンをクリック
                ->waitFor('#dep_form_submit')
                ->assertVisible('#dep_form_submit')
                ->click('#dep_form_submit')
                // モーダルが閉じられていることを確認
                ->pause(1000)
                ->assertMissing('#dep_table_s_item_form')

                // 利用料領収書個別
                // サイドバーのデータ出力ボタンにマウスオーバー
                ->mouseover('@show-output-dropdown-list')
                // 利用者全員の請求書出力ボタンにマウスオーバー
                ->waitFor('@show-usage-receipt-modal-buttton')
                ->mouseover('@show-usage-receipt-modal-buttton')
                ->pause(1000)
                // 選択中の利用者出力ボタンをクリック
                ->waitFor('@dep_usage_fee_receipt_individual')
                ->click('@dep_usage_fee_receipt_individual')
                // 確定ボタンをクリック
                ->waitFor('#dep_form_submit')
                ->assertVisible('#dep_form_submit')
                ->click('#dep_form_submit')
                // モーダルが閉じられていることを確認
                ->pause(1000)
                ->assertMissing('#dep_table_s_item_form');
        });
    }

    /**
     * 保険外請求画面の品目追加バリデーションが正しく動作しているかのテストを行う
     * @return void
     */
    public function testItemAdditionalValidation(): void
    {
        $this->browse(function (Browser $browser) {
            // TODO: 定数ファイル作成のタイミングでテストユーザーの情報を置き換える。
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

            // テストデータ作成
            $facilityUserName = '施設利用者A';

            $browser
                ->loginAs($user)
                ->visit(new ResultInformation())
                ->waitForText($facilityUserName)
                ->click('@facility-user-billing-button')
                ->waitFor('@facility-user-billing-form-label')
                ->pause(2000)
                ->click('@un-table-button') // 保険外請求の「+」押下
                ->assertSeeIn('.item_pulldown_container .item_titles:first-child label', '*必須')
                ->click('@select-add-radio') // 品目の追加を選択
                ->assertDontSeeIn('.item_pulldown_container .item_titles:first-child label', '*必須')
                ->assertSeeIn('#add_item div:first-child', '*必須');

            $browser
                ->click('#item_form_register') // 登録
                ->assertSee('品目名は必須項目です。');
        });
    }

    /**
     * 外泊日登録、外泊日理由のラジオボタンの表示テストを行う。
     * @return void
     */
    public function testStayOutRadio(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

            $facilityUserName = '施設利用者A';

            $browser
                ->loginAs($user)
                ->visit(new ResultInformation())
                ->waitForText($facilityUserName)
                // 外泊日登録タブボタンをクリックした場合、外泊日登録フォームが表示されることをテストする。
                ->click('@stay-out-button')
                ->waitFor('@stay-out-form-label')
                ->pause(2000)
                // 外泊日登録、外泊日理由のラジオボタンの表示を確認する。
                ->assertPresent('@stay-out-radio1')
                ->assertPresent('@stay-out-radio2')
                ->assertPresent('@stay-out-radio3')
                ->assertPresent('@stay-out-radio4')
                ->assertPresent('@stay-out-radio5');
        });
    }
    
    /**
     * 外泊日登録、備考の表示テストを行う。
     * @return void
     */
    public function testStayOutRemarks(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

            $browser
                ->loginAs($user)
                ->visit(new ResultInformation())
                ->pause(2000)
                // 外泊日登録タブボタンをクリックした場合、外泊日登録フォームが表示されることをテストする。
                ->click('@stay-out-button')
                ->pause(1000)
                ->click('#so_new_btn') // 新規登録ボタンを押下
                ->pause(1000)
                // 備考：11文字入力時の確認
                ->click('@stay-out-radio5') // 外泊理由を入力
                ->type('#so_start_date', '2023/01/01') // 開始日を入力
                ->type('#so_start_time', '00:00') // 開始時間を入力
                ->type('#so_end_date', '2023/01/02') // 終了日を入力
                ->type('#so_end_time', '00:00') // 終了時間を入力
                ->type('remarks', '１２３４５６７８９０１') // 備考（11文字）を入力
                ->pause(1000)
                ->click('#so_save_btn') // 保存ボタンを押下
                ->pause(2000)
                ->assertAttribute('@stayout_row1', 'class', 'stay_select_record') // 1行目が選択されていることを確認
                ->assertSeeIn('@stayout_row1', '１２３４５６７８９０') // 1行目の備考に10文字設定されていることを確認
                ->assertDontSeeIn('@stayout_row1', '１２３４５６７８９０１') // 1行目の備考に11文字設定されていないことを確認
                ->assertInputValue('*[name=remarks]', '１２３４５６７８９０１') // 入力エリアの備考に11文字設定されていることを確認
                // 備考：10文字入力時の確認
                ->type('remarks', '１２３４５６７８９０') // 備考（10文字）を入力
                ->pause(1000)
                ->click('#so_save_btn') // 保存ボタンを押下
                ->pause(2000)
                ->assertAttribute('@stayout_row1', 'class', 'stay_select_record') // 1行目が選択されていることを確認
                ->assertSeeIn('@stayout_row1', '１２３４５６７８９０') // 1行目の備考に10文字設定されていることを確認
                ->assertInputValue('*[name=remarks]', '１２３４５６７８９０') // 入力エリアの備考に10文字設定されていることを確認
                // 備考：9文字入力時の確認
                ->type('remarks', '１２３４５６７８９') // 備考（9文字）を入力
                ->pause(1000)
                ->click('#so_save_btn') // 保存ボタンを押下
                ->pause(2000)
                ->assertAttribute('@stayout_row1', 'class', 'stay_select_record') // 1行目が選択されていることを確認
                ->assertSeeIn('@stayout_row1', '１２３４５６７８９') // 1行目の備考に9文字設定されていることを確認
                ->assertInputValue('*[name=remarks]', '１２３４５６７８９') // 入力エリアの備考に9文字設定されていることを確認
            ;
        });
    }
}
