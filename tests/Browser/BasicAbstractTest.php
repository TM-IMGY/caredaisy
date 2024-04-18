<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\FacilityUserInformation;
use Tests\DuskTestCase;

/**
 * @group basic_abstract
 */
class BasicAbstractTest extends DuskTestCase
{
    /**
     * 基本摘要画面が正しく表示されるかをテストする。
     */
    public function testView()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();

            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                ->waitFor('@facility-user-moving-into-button')
                ->pause(1000)
                // 基本摘要画面に遷移する
                ->click('@facility-user-basic-abstract-button')
                ->waitFor('#new_register')
                // 基本摘要画面が表示されていることのチェック
                ->assertVisible('@facility-user-basic-abstract-form-label')
                ->pause(1000);
        });
    }

    /**
     * 新規登録ボタン押下時の各フォーム初期化のテスト
     * - サービス登録あり基本摘要履歴あり利用者
     */
    public function testClearCaseHistry()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            // 利用者変更
            $browser
                ->waitForText('種類55公費あり')
                ->click('#table_facility_user_id39 > td')
                ->pause(1000);
            // 新規登録ボタンを押下し、各フォームの初期化を確認
            $browser
                ->waitFor('#new_register')
                ->click('#new_register')
                ->pause(1000)
                ->assertInputValue('#dpc_code', "")                               // DPCコード(上6桁)
                ->assertSeeIn('#user_status_code', "選択してください")             // 利用者状態等コード
                ->assertInputValue('#basic_abstract_start_date', "2024/04/01")    // 適用開始日
                ->assertInputValue('#basic_abstract_end_date', "");               // 適用終了日
        });
    }

    /**
     * 新規登録ボタン押下時の各フォーム初期化のテスト
     * - サービス登録あり基本摘要履歴なし利用者
     */
    public function testClearCaseNotHistry()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            // 利用者変更
            $browser
                ->waitForText('種別変化ユーザー')
                ->click('#table_facility_user_id26 > td')
                ->pause(1000);
            // 新規登録ボタンを押下し、各フォームの初期化を確認
            $browser
                ->waitFor('#new_register')
                ->click('#new_register')
                ->pause(1000)
                ->assertInputValue('#dpc_code', "")                               // DPCコード(上6桁)
                ->assertSeeIn('#user_status_code', "選択してください")             // 利用者状態等コード
                ->assertInputValue('#basic_abstract_start_date', "2000/12/31")    // 適用開始日
                ->assertInputValue('#basic_abstract_end_date', "");               // 適用終了日
        });
    }

    /**
     * 新規登録ボタン押下時の各フォーム初期化のテスト
     * - サービス登録なし基本摘要履歴あり利用者
     */
    public function testClearCaseNotService()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            // 利用者変更
            $browser
                ->waitForText('種類55サービスなし')
                ->click('#table_facility_user_id82 > td')
                ->pause(1000);
            // 新規登録ボタンを押下し、各フォームの初期化を確認
            $browser
                ->waitFor('#new_register')
                ->click('#new_register')
                ->pause(1000)
                ->assertInputValue('#dpc_code', "")                               // DPCコード(上6桁)
                ->assertSeeIn('#user_status_code', "選択してください")             // 利用者状態等コード
                ->assertInputValue('#basic_abstract_start_date', "2024/04/01")    // 適用開始日
                ->assertInputValue('#basic_abstract_end_date', "");               // 適用終了日
        });
    }

    /**
     * 新規登録ボタン押下時の各フォーム初期化のテスト
     * - サービス登録なし基本摘要履歴なし利用者
     */
    public function testClearCaseNotData()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            // 利用者変更
            $browser
                ->waitForText('種別なしユーザー')
                ->click('#table_facility_user_id27 > td')
                ->pause(1000);
            // 新規登録ボタンを押下し、各フォームの初期化を確認
            $browser
                ->waitFor('#new_register')
                ->click('#new_register')
                ->pause(1000)
                ->assertInputValue('#dpc_code', "")                               // DPCコード(上6桁)
                ->assertSeeIn('#user_status_code', "選択してください")             // 利用者状態等コード
                ->assertInputValue('#basic_abstract_start_date', "2000/12/31")    // 適用開始日
                ->assertInputValue('#basic_abstract_end_date', "");               // 適用終了日
        });
    }
}
