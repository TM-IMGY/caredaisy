<?php

namespace Tests\Browser;

use App\User;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\Browser\Pages\ResultInformation;
use Tests\DuskTestCase;

/**
 * @group uninsured
 */
class UninsuredTest extends DuskTestCase
{
    const ITEM_NAME = '朝食';

    public function testView()
    {
        $this->browse(function (Browser $browser) {
            // 認証して実績情報/外泊日登録画面に遷移する
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();
            $browser
                ->loginAs($user)
                ->visit(new ResultInformation());
            // 保険外請求タブをクリックして保険外請求画面が正常に表示されることを確認
            $browser
                ->waitFor('@facility-user-billing-button')
                ->pause(1000)
                ->click('@facility-user-billing-button')
                ->waitFor('@facility-user-billing-form-label')
                ->assertVisible('@facility-user-billing-form-label')
                ->pause(1000);
        });
    }

    public function testAddItem()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                // 品目追加で品目リストから登録
                ->waitFor('@un-table-button')
                ->pause(1000)
                ->click('@un-table-button')
                ->waitFor('@un-table-form')
                ->select('@select-item', 1)
                ->click('#item_form_register')
                // 登録した品目が保険外請求画面上に表示されることを確認
                ->waitFor('#calendar')
                ->pause(1000)
                ->assertSeeIn('#calendar', self::ITEM_NAME)
                // 再度品目リストから同品目を登録しようとして、エラーメッセージが表示されることを確認
                ->waitFor('@un-table-button')
                ->click('@un-table-button')
                ->waitFor('@un-table-form')
                ->select('@select-item', 1)
                ->click('#item_form_register')
                ->pause(1000)
                ->assertSeeIn('@un-table-form', 'この項目は既に追加済です')
                ->click('#item_form_cancel')
                ->pause(1000);
        });
    }

    public function testPopupMessage()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                // testAddItem()で追加したレコードのゴミ箱アイコンをクリックする。
                ->click('.caredaisy_table > #row_1 > td.caredaisy_table_cell.un_col2 > img')
                ->pause(1000)
                // ポップアップの文言を確認する。
                ->assertDialogOpened(self::ITEM_NAME . 'を削除してよろしいですか？')
                ->acceptDialog()
                ->pause(1000);
        });
    }
}
