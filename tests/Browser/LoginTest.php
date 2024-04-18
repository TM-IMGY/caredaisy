<?php

namespace Tests\Browser;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    /**
     * ログイン画面でログインに失敗した場合に、元のログイン画面へリダイレクトされることをテストする。
     */
    public function testLoginFailed()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->type('@id', 'Dummy')
                ->type('@password', 'undefinedPassword')
                ->click('@submit')
                ->assertSee('IDまたはパスワードに誤りがあります')
                ->screenshot('top-page-screenshot');// スクリーンショットもとれる
        });
    }

    /**
     * ログイン画面でログインに成功した場合に「/top」へリダイレクトされることをテストする。
     * @return void
     */
    public function testLoginSucceeded()
    {
        // can login &
        // redirect /top after loggedin
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->type('@id', 'GH00002')
                ->type('@password', 'GH00002!')
                ->click('@submit')
                ->assertPathIs('/top')
                // TODO: トップメッセージの変更に弱くなるので換装する。
                ->assertSee('■ケアデイジー機能追加のお知らせ')
                ->assertSee('グループホーム テスト2');
        });
    }

    /**
     * 未認証のユーザーが「/」を訪れた場合に「/login」へリダイレクトされることをテストする。
     * @return void
     */
    public function testRootRedirect()
    {
        // redirect /login if un-authorized user visit /
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertPathIs('/login');
        });
    }
}
