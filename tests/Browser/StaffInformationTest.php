<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\StaffInformation;
use Tests\DuskTestCase;

class StaffInformationTest extends DuskTestCase
{
    /**
     * スタッフ情報画面のタブ遷移が正しく機能しているかをテストする。
     * @return void
     */
    public function testTabTransition()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'GH00002')->first();

            $browser
                ->loginAs($user)
                ->visit(new StaffInformation());

            // 基本情報タブボタンをクリックした場合、基本情報フォームが表示されることをテストする。
            $browser
                ->click('@staff-basic-button')
                ->assertVisible('@staff-basic-form-label');

            // 権限設定タブボタンをクリックした場合、権限設定フォームが表示されることをテストする。
            $browser
                ->click('@staff-auth-button')
                ->assertVisible('@staff-auth-form-label');
        });
    }
}
