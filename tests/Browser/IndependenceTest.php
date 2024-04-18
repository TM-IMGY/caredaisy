<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\FacilityUserInformation;
use Tests\DuskTestCase;
use Carbon\Carbon;

/**
 * @group independence
 */
class IndependenceTest extends DuskTestCase
{
    /**
     * 自立度画面が正しく表示されるかをテストする。
     */
    public function testView()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();

            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                ->waitFor('@facility-user-moving-into-button')
                // 自立度画面に遷移する
                ->click('@facility-user-independence-button')
                ->waitFor('#clearBtn_independence')
                // 自立度画面が表示されていることのチェック
                ->assertVisible('@facility-user-independence-form-label')
                ->pause(1000);
        });
    }

    /**
     * 既存履歴の更新と更新後履歴が選択されているかのテスト
     */
    public function testUpdate()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                ->click('@tr_facility_user_id36')
                ->pause(2000)
                ->assertAttribute(
                    '#table_tbody_independence > tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                // 各フォームに値を設定する
                ->select('independent_list', 4)
                ->select('Dementia_list', 7)
                ->type('#text_item1_independence', '2021/10/01')
                ->type('#text_item2_independence', 'テスト次郎')
                ->click('#js-updata-popup_independence')
                ->waitFor('#updatabtn_independence')
                ->click('#updatabtn_independence')
                ->pause(1000)
                ->assertAttribute(
                    '#table_tbody_independence > tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                // 更新した値が表示されているかチェックする
                ->assertSelected('independent_list', 4)
                ->assertSelected('Dementia_list', 7)
                ->assertValue('#text_item1_independence', "2021/10/01")
                ->assertValue('#text_item2_independence', "テスト次郎");
        });
    }

    /**
     * 新規登録
     */
    public function testNewRegister()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                // 新規登録ボタン押下後、利用開始日以外のフォームが初期化されているか
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                ->assertSelected('independent_list', "")
                ->assertSelected('Dementia_list', "")
                ->assertValue('#text_item1_independence', "")
                ->assertValue('#text_item2_independence', "")
                // 各フォームに値を設定する
                ->select('independent_list', 1)
                ->select('Dementia_list', 1)
                ->value('#text_item1_independence', '2022/04/01')
                ->value('#text_item2_independence', 'テストコード太郎')
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                ->assertAttribute(
                    '#table_tbody_independence > tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                // 登録した値が表示されていることのチェック
                ->assertSelected('independent_list', 1)
                ->assertSelected('Dementia_list', 1)
                ->assertValue('#text_item1_independence', '2022/04/01')
                ->assertValue('#text_item2_independence', 'テストコード太郎');
        });
    }

    /**
     * 履歴切替及びフォーム内容が切り替わっているかのチェック
     */
    public function testSelectHistory()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                ->waitFor('#clearBtn_independence')
                // 遷移時に最上位の履歴が選択されていることのチェック
                // 新規登録のテストでの利用開始日の設定次第では挙動が変わるので注意
                ->assertAttribute(
                    '#table_tbody_independence > tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                ->click('#table_tbody_independence > tr + tr')
                // 2番目の履歴が選択されているかチェックする
                ->assertAttribute(
                    '#table_tbody_independence > tr + tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                );
        });
    }

    /**
     * 新規登録時、最新の履歴データの判断日より過去日の判断日を入力して登録した時、
     * 警告ダイアログが表示されることを確認
     * また、警告ダイアログでYESを選択して履歴データが登録されることを確認
     */
    public function testInsertJudgmentDateBeforeListDataDialogYes() {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        // テストに使用する利用者を設定
        $this->testUserId = '2';
        // 指定した利用者についての自立度情報画面まで遷移する
        $this->selectTestUser();
        $this->browse(function (Browser $browser) {
            // ブラウザテストの実行
            $browser
                // 新規登録ボタン押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して1件目の自立度を設定する
                ->select('independent_list', 1)
                ->select('Dementia_list', 1)
                ->value('#text_item1_independence', '2022/04/01')
                ->value('#text_item2_independence', 'テストコード太郎')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // 新規登録ボタンを再度押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して2件目の自立度を設定する
                ->select('independent_list', 2)
                ->select('Dementia_list', 2)
                ->value('#text_item1_independence', '2022/03/31') // 判断日に登録済みの自立度の判断日より前の日付を設定する
                ->value('#text_item2_independence', 'テストコード次郎')
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // ポップアップが表示されていることを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: block;'
                )
                // ポップアップのメッセージを確認する
                ->assertSeeIn('#popup_confirm_message', '前回の判断日より前の')
                ->assertSeeIn('#popup_confirm_message', '日付が入力されていますが')
                ->assertSeeIn('#popup_confirm_message', '保存しますか？')
                // // ポップアップで「はい」を選択する
                ->click('#updatabtn_independence')
                ->pause(1000)
                // ポップアップが表示されていないことを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: none;'
                )
                // 1行目に1件目に登録した判断日が設定されることを確認する
                ->assertSeeIn('@selectIdIndependence1', '2022/04/01')
                ->assertSeeIn('@selectIdIndependence1', 'テストコード太郎')
                // 2行目に2件目に登録した判断日が設定されることを確認する
                ->assertSeeIn('@selectIdIndependence2', '2022/03/31')
                ->assertSeeIn('@selectIdIndependence2', 'テストコード次郎');
        });
    }

    /**
     * 新規登録時、最新の履歴データの判断日より過去日の判断日を入力して登録した時、
     * 警告ダイアログが表示されることを確認
     * また、警告ダイアログでNOを選択して履歴データが登録されないことを確認
     */
    public function testInsertJudgmentDateBeforeListDataDialogNo() {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        // テストに使用する利用者を設定
        $this->testUserId = '3';
        // 指定した利用者についての自立度情報画面まで遷移する
        $this->selectTestUser();
        $this->browse(function (Browser $browser) {
            // ブラウザテストの実行
            $browser
                // 新規登録ボタン押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して1件目の自立度を設定する
                ->select('independent_list', 1)
                ->select('Dementia_list', 1)
                ->value('#text_item1_independence', '2022/04/01')
                ->value('#text_item2_independence', 'テストコード太郎')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // 新規登録ボタンを再度押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して2件目の自立度を設定する
                ->select('independent_list', 2)
                ->select('Dementia_list', 2)
                ->value('#text_item1_independence', '2022/03/31') // 判断日に登録済みの自立度の判断日より前の日付を設定する
                ->value('#text_item2_independence', 'テストコード次郎')
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // ポップアップが表示されていることを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: block;'
                )
                // ポップアップのメッセージを確認する
                ->assertSeeIn('#popup_confirm_message', '前回の判断日より前の')
                ->assertSeeIn('#popup_confirm_message', '日付が入力されていますが')
                ->assertSeeIn('#popup_confirm_message', '保存しますか？')
                // ポップアップで「いいえ」を選択する
                ->click('#cancelbtn_independence')
                ->pause(1000)
                // ポップアップが表示されていないことを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: none;'
                )
                // 1行目に1件目に登録した判断日が設定されることを確認する
                ->assertSeeIn('@selectIdIndependence1', '2022/04/01')
                ->assertSeeIn('@selectIdIndependence1', 'テストコード太郎')
                // 2行目に2件目に自立度情報が登録されていないことを確認する
                ->assertMissing('@selectIdIndependence2');
        });
    }
    
    /**
     * 新規登録時、最新の履歴データの判断日より未来日の判断日を入力して登録
     * ダイアログが表示されず、データが登録されることを確認
     */
    public function testInsertJudgmentDateAfterListData() {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        // テストに使用する利用者を設定
        $this->testUserId = '4';
        // 指定した利用者についての自立度情報画面まで遷移する
        $this->selectTestUser();
        $this->browse(function (Browser $browser) {
            // ブラウザテストの実行
            $browser
                // 新規登録ボタン押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して1件目の自立度を設定する
                ->select('independent_list', 1)
                ->select('Dementia_list', 1)
                ->value('#text_item1_independence', '2022/04/01')
                ->value('#text_item2_independence', 'テストコード太郎')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // 新規登録ボタンを再度押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して2件目の自立度を設定する
                ->select('independent_list', 2)
                ->select('Dementia_list', 2)
                ->value('#text_item1_independence', '2022/04/02')  // 判断日に登録済みの自立度の判断日より後の日付を設定する
                ->value('#text_item2_independence', 'テストコード次郎')
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // ポップアップが表示されていないことを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: none;'
                )
                // 1行目に2件目に登録した判断日が設定されることを確認する
                ->assertSeeIn('@selectIdIndependence1', '2022/04/02')
                ->assertSeeIn('@selectIdIndependence1', 'テストコード次郎')
                // 2行目に1件目に登録した判断日が設定されることを確認する
                ->assertSeeIn('@selectIdIndependence2', '2022/04/01')
                ->assertSeeIn('@selectIdIndependence2', 'テストコード太郎');
        });
    }
    
    /**
     * 新規登録時、最新の履歴データの判断日と同じ判断日を入力して登録
     * ダイアログが表示されず、データが登録されることを確認
     */
    public function testInsertJudgmentDateEqualListData() {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        // テストに使用する利用者を設定
        $this->testUserId = '5';
        // 指定した利用者についての自立度情報画面まで遷移する
        $this->selectTestUser();
        $this->browse(function (Browser $browser) {
            // ブラウザテストの実行
            $browser
                // 新規登録ボタン押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して1件目の自立度を設定する
                ->select('independent_list', 1)
                ->select('Dementia_list', 1)
                ->value('#text_item1_independence', '2022/04/01')
                ->value('#text_item2_independence', 'テストコード太郎')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // 新規登録ボタンを再度押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して2件目の自立度を設定する
                ->select('independent_list', 2)
                ->select('Dementia_list', 2)
                ->value('#text_item1_independence', '2022/04/01')  // 判断日に登録済みの自立度の判断日と同じ日付を設定する
                ->value('#text_item2_independence', 'テストコード次郎')
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // ポップアップが表示されていないことを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: none;'
                )
                // 1行目に2件目に登録した判断日が設定されることを確認する
                ->assertSeeIn('@selectIdIndependence1', '2022/04/01')
                ->assertSeeIn('@selectIdIndependence1', 'テストコード太郎')
                // 2行目に1件目に登録した判断日が設定されることを確認する
                ->assertSeeIn('@selectIdIndependence2', '2022/04/01')
                ->assertSeeIn('@selectIdIndependence2', 'テストコード次郎');
        });
    }

    /**
     * 更新時、最新の履歴データの判断日より過去日の判断日を入力して登録した時、
     * 警告ダイアログが表示されることを確認
     * また、警告ダイアログでYESを選択して履歴データが更新されることを確認
     */
    public function testUpdateJudgmentDateBeforeListData() {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        // テストに使用する利用者を設定
        $this->testUserId = '6';
        // 指定した利用者についての自立度情報画面まで遷移する
        $this->selectTestUser();
        $this->browse(function (Browser $browser) {
            // ブラウザテストの実行
            $browser
                // 新規登録ボタン押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して1件目の自立度を設定する
                ->select('independent_list', 1)
                ->select('Dementia_list', 1)
                ->value('#text_item1_independence', '2022/04/01')
                ->value('#text_item2_independence', 'テストコード太郎')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // 新規登録ボタンを再度押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して2件目の自立度を設定する
                ->select('independent_list', 2)
                ->select('Dementia_list', 2)
                ->value('#text_item1_independence', '2022/04/02')
                ->value('#text_item2_independence', 'テストコード次郎')
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // 直近の自立度情報を選択する
                ->click('@selectIdIndependence1')
                ->pause(1000)
                // 判断日に登録済みの自立度の判断日より前の日付を設定する
                ->value('#text_item1_independence', '2022/03/31')
                ->value('#text_item2_independence', 'テストコード次郎更新')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // ポップアップが表示されていることを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: block;'
                )
                // ポップアップのメッセージを確認する
                ->assertSeeIn('#popup_confirm_message', '前回の判断日より前の')
                ->assertSeeIn('#popup_confirm_message', '日付が入力されていますが')
                ->assertSeeIn('#popup_confirm_message', '保存しますか？')
                // // ポップアップで「はい」を選択する
                ->click('#updatabtn_independence')
                ->pause(1000)
                // ポップアップが表示されていないことを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: none;'
                )
                // 1行目に更新対象でないデータが表示されることを確認する
                ->assertSeeIn('@selectIdIndependence1', '2022/04/01')
                ->assertSeeIn('@selectIdIndependence1', 'テストコード太郎')
                // 2行目に更新済みデータが表示されることを確認する
                ->assertSeeIn('@selectIdIndependence2', '2022/03/31')
                ->assertSeeIn('@selectIdIndependence2', 'テストコード次郎更新')
                ;
        });
    }
    
    /**
     * 更新時、最新の履歴データの判断日より未来日の判断日を入力して登録した時、
     * 更新確認ダイアログが表示されることを確認
     * また、更新確認ダイアログでYESを選択して履歴データが更新されることを確認
     */
    public function testUpdateJudgmentDateAfterListData() {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        // テストに使用する利用者を設定
        $this->testUserId = '7';
        // 指定した利用者についての自立度情報画面まで遷移する
        $this->selectTestUser();
        $this->browse(function (Browser $browser) {
            // ブラウザテストの実行
            $browser
                // 新規登録ボタン押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して1件目の自立度を設定する
                ->select('independent_list', 1)
                ->select('Dementia_list', 1)
                ->value('#text_item1_independence', '2022/04/01')
                ->value('#text_item2_independence', 'テストコード太郎')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // 新規登録ボタンを再度押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して2件目の自立度を設定する
                ->select('independent_list', 2)
                ->select('Dementia_list', 2)
                ->value('#text_item1_independence', '2022/04/03')
                ->value('#text_item2_independence', 'テストコード次郎')
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // 直近の自立度情報を選択する
                ->click('@selectIdIndependence1')
                ->pause(1000)
                // 判断日に登録済みの自立度の判断日より後の日付を設定する
                ->value('#text_item1_independence', '2022/04/02')
                ->value('#text_item2_independence', 'テストコード次郎更新')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // ポップアップが表示されていることを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: block;'
                )
                // ポップアップのメッセージを確認する
                ->assertSeeIn('#popup_confirm_message', '変更した内容を更新しますか？')
                // // ポップアップで「はい」を選択する
                ->click('#updatabtn_independence')
                ->pause(1000)
                // ポップアップが表示されていないことを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: none;'
                )
                // 1行目に更新済みデータが表示されることを確認する
                ->assertSeeIn('@selectIdIndependence1', '2022/04/02')
                ->assertSeeIn('@selectIdIndependence1', 'テストコード次郎更新')
                // 2行目に更新対象でないデータが表示されることを確認する
                ->assertSeeIn('@selectIdIndependence2', '2022/04/01')
                ->assertSeeIn('@selectIdIndependence2', 'テストコード太郎')
                ;
        });
    }
    
    /**
     * 更新時、最新の履歴データの判断日と同じ判断日を入力して登録した時、
     * 更新確認ダイアログが表示されることを確認
     * また、更新確認ダイアログでYESを選択して履歴データが更新されることを確認
     */
    public function testUpdateJudgmentDateEqualListData() {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        // テストに使用する利用者を設定
        $this->testUserId = '8';
        // 指定した利用者についての自立度情報画面まで遷移する
        $this->selectTestUser();
        $this->browse(function (Browser $browser) {
            // ブラウザテストの実行
            $browser
                // 新規登録ボタン押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して1件目の自立度を設定する
                ->select('independent_list', 1)
                ->select('Dementia_list', 1)
                ->value('#text_item1_independence', '2022/04/01')
                ->value('#text_item2_independence', 'テストコード太郎')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // 新規登録ボタンを再度押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して2件目の自立度を設定する
                ->select('independent_list', 2)
                ->select('Dementia_list', 2)
                ->value('#text_item1_independence', '2022/04/02')
                ->value('#text_item2_independence', 'テストコード次郎')
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // 直近の自立度情報を選択する
                ->click('@selectIdIndependence1')
                ->pause(1000)
                // 判断日に登録済みの自立度の判断日より後の日付を設定する
                ->value('#text_item1_independence', '2022/04/01')
                ->value('#text_item2_independence', 'テストコード次郎更新')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // ポップアップが表示されていることを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: block;'
                )
                // ポップアップのメッセージを確認する
                ->assertSeeIn('#popup_confirm_message', '変更した内容を更新しますか？')
                // // ポップアップで「はい」を選択する
                ->click('#updatabtn_independence')
                ->pause(1000)
                // ポップアップが表示されていないことを確認する
                ->assertAttribute(
                    '#overflow_independence',
                    'style',
                    'display: none;'
                )
                // 1行目に更新対象でないデータが表示されることを確認する
                ->assertSeeIn('@selectIdIndependence1', '2022/04/01')
                ->assertSeeIn('@selectIdIndependence1', 'テストコード太郎')
                // 2行目に更新済みデータが表示されることを確認する
                ->assertSeeIn('@selectIdIndependence2', '2022/04/01')
                ->assertSeeIn('@selectIdIndependence2', 'テストコード次郎更新')
                ;
        });
    }

    /**
     * 判断日に未来日を入力した場合にエラーとなり登録されないことを確認
     */
    public function testJudgmentDateTomorrow() {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        // テストに使用する利用者を設定
        $this->testUserId = '9';
        // 指定した利用者についての自立度情報画面まで遷移する
        $this->selectTestUser();
        $this->browse(function (Browser $browser) {
            // 翌日を取得
            $systemTimestamp = Carbon::tomorrow();
            // ブラウザテストの実行
            $browser
                // 実行前の状態を確認する
                ->assertPresent('@selectIdIndependence1')
                ->assertSeeIn('@selectIdIndependence1', '2021/09/01')
                ->assertSeeIn('@selectIdIndependence1', 'テスター管理権限')
                ->assertMissing('@selectIdIndependence2')
                // 新規登録ボタン押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して自立度を設定する
                ->select('independent_list', 1)
                ->select('Dementia_list', 1)
                ->value('#text_item1_independence', $systemTimestamp->format('Y/m/d')) // 翌日を設定
                ->value('#text_item2_independence', 'テストコード太郎')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                ->assertSeeIn('#validateErrorsIndependence', '未来の日付は入力できません')
                // データが登録されていないことを確認する
                ->assertSeeIn('@selectIdIndependence1', '2021/09/01')
                ->assertSeeIn('@selectIdIndependence1', 'テスター管理権限')
                ->assertMissing('@selectIdIndependence2')
                ;
        });
    }

    /**
     * 判断日に本日日付を入力した場合にエラーとならず、登録されることを確認
     */
    public function testJudgmentDateNow() {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        // テストに使用する利用者を設定
        $this->testUserId = '10';
        // 指定した利用者についての自立度情報画面まで遷移する
        $this->selectTestUser();
        $this->browse(function (Browser $browser) {
            // 本日を取得
            $systemTimestamp = Carbon::now();
            // ブラウザテストの実行
            $browser
                // 実行前の状態を確認する
                ->assertPresent('@selectIdIndependence1')
                ->assertSeeIn('@selectIdIndependence1', '2021/09/01')
                ->assertSeeIn('@selectIdIndependence1', 'テスター管理権限')
                ->assertMissing('@selectIdIndependence2')
                // 新規登録ボタン押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して自立度を設定する
                ->select('independent_list', 1)
                ->select('Dementia_list', 1)
                ->value('#text_item1_independence', $systemTimestamp->format('Y/m/d')) // 本日を設定
                ->value('#text_item2_independence', 'テストコード太郎')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                ->assertDontSeeIn('#validateErrorsIndependence', '未来の日付は入力できません')
                // データが登録されていることを確認する
                ->assertSeeIn('@selectIdIndependence1', $systemTimestamp->format('Y/m/d'))
                ->assertSeeIn('@selectIdIndependence1', 'テストコード太郎')
                ->assertSeeIn('@selectIdIndependence2', '2021/09/01')
                ->assertSeeIn('@selectIdIndependence2', 'テスター管理権限')
                ;
        });
    }

    /**
     * 判断日に本日日付を入力した場合にエラーとならず、登録されることを確認
     */
    public function testJudgmentDateYesterday() {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        // テストに使用する利用者を設定
        $this->testUserId = '11';
        // 指定した利用者についての自立度情報画面まで遷移する
        $this->selectTestUser();
        $this->browse(function (Browser $browser) {
            // 昨日を取得
            $systemTimestamp = Carbon::yesterday();
            // ブラウザテストの実行
            $browser
                // 実行前の状態を確認する
                ->assertPresent('@selectIdIndependence1')
                ->assertSeeIn('@selectIdIndependence1', '2021/09/01')
                ->assertSeeIn('@selectIdIndependence1', 'テスター管理権限')
                ->assertMissing('@selectIdIndependence2')
                // 新規登録ボタン押下
                ->waitFor('#clearBtn_independence')
                ->click('#clearBtn_independence')
                // 各フォームに値を設定して自立度を設定する
                ->select('independent_list', 1)
                ->select('Dementia_list', 1)
                ->value('#text_item1_independence', $systemTimestamp->format('Y/m/d')) // 昨日を設定
                ->value('#text_item2_independence', 'テストコード太郎')
                // 保存ボタン押下
                ->click('#js-updata-popup_independence')
                ->pause(1000)
                // エラーメッセージが表示されないことを確認する
                ->assertDontSeeIn('#validateErrorsIndependence', '未来の日付は入力できません')
                // データが登録されていることを確認する
                ->assertSeeIn('@selectIdIndependence1', $systemTimestamp->format('Y/m/d'))
                ->assertSeeIn('@selectIdIndependence1', 'テストコード太郎')
                ->assertSeeIn('@selectIdIndependence2', '2021/09/01')
                ->assertSeeIn('@selectIdIndependence2', 'テスター管理権限')
                ;
        });
    }

    /**
     * テスト用共通処理
     */
    private function selectTestUser() {
        $this->browse(function (Browser $browser) {
            // テストユーザーの情報を取得
            $account = User::where('employee_number', $this->testAccount)->first();

            // ブラウザ上の動作を開始
            $browser
                // テストユーザーでログインする
                ->loginAs($account)
                ->visit(new FacilityUserInformation())
                ->waitFor('@facility-user-moving-into-button')
                // 自立度画面に遷移する
                ->click('@facility-user-independence-button')
                ->waitFor('#clearBtn_independence')
                ->pause(1000)
                // テスト対象のユーザーを選択する
                ->click('@tr_facility_user_id'.$this->testUserId)
                ->pause(1000);
        });
    }
}
