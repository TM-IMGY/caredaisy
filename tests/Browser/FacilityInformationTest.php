<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\FacilityInformation;
use Tests\DuskTestCase;

class FacilityInformationTest extends DuskTestCase
{
    /**
     * 事業所情報画面のタブ遷移が正しく機能しているかをテストする。
     */
    public function testTabTransition()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'GH00002')->first();

            $browser
                ->loginAs($user)
                ->visit(new FacilityInformation());

            // 施設タブボタンをクリックした場合、施設フォームが表示されることをテストする。
            $browser
                ->click('@institution-button')
                ->assertVisible('@institution-form-label');

            // 事業所タブボタンをクリックした場合、事業所フォームが表示されることをテストする。
            $browser
                ->click('@facility-button')
                ->assertVisible('@facility-form-label');

            // サービス種別タブボタンをクリックした場合、サービス種別フォームが表示されることをテストする。
            $browser
                ->click('@service-type-button')
                ->assertVisible('@service-type-form-label');

            // 加算状況タブボタンをクリックした場合、加算状況フォームが表示されることをテストする。
            $browser
                ->click('@addition-status-button')
                ->assertVisible('@addition-status-form-label');

            // 保険外費用タブボタンをクリックした場合、保険外費用フォームが表示されることをテストする。
            $browser
                ->click('@uninsured-button')
                ->assertVisible('@uninsured-form-label');

            // 法人タブボタンをクリックした場合、法人フォームが表示されることをテストする。
            $browser
                ->click('@corporation-button')
                ->assertVisible('@corporation-form-label');
        });
    }
}
