<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class Login extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/login';
    }

    /**
     * 当該のページに遷移した場合に自動実行される。
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        // check is elements & default value
        // IDの入力欄とパスワード入力欄が空欄で表示されおり、
        // かつ、ログインボタンが表示されている場合にログイン画面への遷移が成功したと処理する。
        // 結果のスクリーンショットも保存する。
        $browser
            ->screenshot('login page')
            ->assertPresent('@id')
            ->assertInputValue('@id', '')
            ->assertPresent('@password')
            ->assertInputValue('@password', '')
            ->assertPresent('@submit');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@id'       => 'input[name="employee_number"]',
            '@password' => 'input[name="password"]',
            '@submit'   => 'button#loging_btn'
        ];
    }
}
