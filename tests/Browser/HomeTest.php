<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Top;
use Tests\DuskTestCase;

class HomeTest extends DuskTestCase
{
    /**
     * ホーム画面が正しく表示されるかをテストする。
     */
    public function testVisitTop()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'GH00002')->first();
            $browser
                ->loginAs($user)
                ->visit(new Top)
                ->pause(2000);
        });
    }

    /**
     * 操作・伝送マニュアルのリンクが正しく表示されるかをテストする。
     */
    public function testManualLinkCheck()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->mouseover('@header-manual')
                ->assertVisible('@operation-manual-download')
                ->assertVisible('@transmission-manual-download');
        });
    }
}
