<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\FacilityUserInformation;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;

class FirstUsersBasicInfoSaveFromModalTest extends DuskTestCase
{
    /**
     * 課題：「利用者情報＞基本情報画面」の「保存」ボタンを連打すると複数同じ情報が作成される
     * 
     * 施設利用者が0人のアカウント(new_account@0000000007.care-daisy.com)を使用する。
     * 利用者情報タブ内の基本情報タブにて必要項目を入力し、保存ボタンを押下。
     * 修正後に追加されるモーダルでいいえボタンを押下し、施設利用者が登録されないことを確認。
     * 再度保存ボタンを押下し、はいボタンを押下。このタイミングで最初の施設利用者が登録される。
     * 表示されたモーダルのボタンを押せる=連打ができないと判断するため、連打テストは省略する。
     * 最後に他URLへ遷移後、利用者情報タブに戻り登録した施設利用者が存在していることを確認する。
     * @return void
     */
    public function testCheckConsecutiveness(): void
    {
        $this->browse(function (Browser $browser) {

            // 利用者姓
            $lastName = '伊藤';
            // 利用者名
            $firstName = '博文';
            // セイ（フリガナ）
            $lastName_kana = 'イトウ';
            // メイ（フリガナ）
            $firstName_kana = 'ヒロブミ';
            // 生年月日
            $birthday = '1950/01/01';
            // 被保険者番号
            $insured_no = '0000000033';
            // 保険者番号
            $insurer_no = '142067';
            // 入居日(利用開始)
            $start_date = '2022/09/14';
            // 入居前の状況
            $before_status = 1;

            // 登録されている施設利用者が0人のアカウントでログインし、利用者情報画面へ遷移する。
            $user = User::where('employee_number', 'new_account@0000000007.care-daisy.com')->first();

            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                ->pause(2000);

            // 基本情報タブを押下し、基本情報が表示される。
            $browser
                ->click('@facility-user-basic-button')
                ->pause(2000);

            // 基本情報の必須項目を入力する。
            $browser
                // 利用者姓
                ->type('@facility-user-form-last-name', $lastName)
                // 利用者名
                ->type('@facility-user-form-first-name', $firstName)
                // セイ（フリガナ）
                ->type('@facility-user-form-last-name-kana', $lastName_kana)
                // メイ（フリガナ）
                ->type('@facility-user-form-first-name-kana', $firstName_kana)
                // 性別
                ->click('@facility-user-form-gender-male')
                // 生年月日
                ->type('@facility-user-form-birthday', $birthday)
                // 被保険者番号
                ->type('@facility-user-form-insured-no', $insured_no)
                // 保険者番号
                ->type('@facility-user-form-insurer-no', $insurer_no)
                // 入居日(利用開始)
                ->type('@facility-user-form-start-date', $start_date)
                // 入居前の状況
                ->select('@facility-user-form-before-status', $before_status)
                ->pause(2000);

            // 保存ボタンを押下し、修正で追加した確認モーダルが表示される。
            $browser
                ->click('@facility-user-save-button')
                ->pause(2000);

            // いいえボタンを押下し、入力した基本情報が登録されないことを確認する。
            $browser
                ->click('@confirmation-dialog-button-no')
                ->pause(2000)
                ->assertDontSeeIn('@user_info_fu_table', $lastName . $firstName);

            // 再度保存ボタンを押下し、確認モーダルが表示される。
            $browser
                ->click('@facility-user-save-button')
                ->pause(2000);

            // はいボタンを押下し、入力した基本情報が反映された1人目の施設利用者データが作成される。
            // 画面左のリストにデータが登録されているかを確認する。
            $browser
                ->click('@confirmation-dialog-button-yes')
                ->pause(3000)
                ->assertSeeIn('@user_info_fu_table', $lastName . $firstName);

            // 他ページに遷移後、利用者情報タブ内の基本情報タブに戻り登録したデータが存在しているかを確認する。
            $browser
                ->visit(new HomePage())
                ->pause(2000)
                ->visit(new FacilityUserInformation())
                ->pause(2000)
                ->click('@facility-user-basic-button')
                ->pause(2000);
            
            // 画面左のリストにデータが登録されたままかを確認する。
            $browser
                ->assertSeeIn('@user_info_fu_table', $lastName . $firstName);
        });
    }
}
