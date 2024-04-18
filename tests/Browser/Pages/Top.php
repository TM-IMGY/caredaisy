<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

/**
 * TODO: テストクラスに限らずアプリケーション全体でトップだったりホームだったり表記ゆれがあるので注意する。
 */
class Top extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/top';
    }

    /**
     * 当該のページに遷移した場合に自動実行される。
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        // is menu
        $browser->assertPresent('@manual')
            ->assertPresent('@inquiry')
            ->assertPresent('@logout');
        
        $browser->assertSeeLink('ホーム')
            ->assertSeeLink('利用者情報')
            ->assertSeeLink('');

        // inquiry hover
        $browser->assertMissing('@inquiryTooltip')
            ->mouseover('@inquiry')
            ->assertVisible('@inquiryTooltip');

        // logout hover
        $browser->mouseover('@logout')
            ->assertPresent('@logoutMenu');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@manual'   => 'header .header_manual',
            '@inquiry'  => 'header .header_inquiry',
            '@logout'   => 'header .header_logout',
            '@logoutMenu'   => '#application_header_logout_menu',
            '@inquiryTooltip'   => '.inquiry_tooltip',
        ];
    }
}
