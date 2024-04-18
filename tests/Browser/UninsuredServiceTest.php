<?php

namespace Tests\Browser;

use App\User;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\Browser\Pages\FacilityInformation;
use Tests\DuskTestCase;

/**
 * @group uninsured_service
 */
class UninsuredServiceTest extends DuskTestCase
{
    /**
     * 保険外費用画面の表示テスト
     */
    public function testView()
    {
        $this->browse(function (Browser $browser) {
            // 認証して事業所情報/法人画面に遷移する
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();
            $browser
                ->loginAs($user)
                ->visit(new FacilityInformation());
            // 保険外費用タブをクリックして保険外費用画面が正常に表示されることを確認
            $browser
                ->waitFor('@uninsured-button')
                ->click('@uninsured-button')
                ->waitFor('@uninsured-form-label')
                ->assertVisible('@uninsured-form-label')
                ->pause(1000);
        });
    }

    /**
     * 品目の並び替えテスト
     */
    public function testChangeRow()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            // ドラッグ&ドロップで品目を並び替える
            $browser
                ->drag('#history_id_1', '#history_id_4');
            // 品目が並び替わっていることを確認
            $correctRows = array('history_id_5', 'history_id_2', 'history_id_3', 'history_id_1', 'history_id_4');    // 並び替え後順番
            $table = $browser->element('#table_tbody_uninsured_items');
            $actualRows = $table->findElements(WebDriverBy::tagName('tr'));
            $items = array();
            for ($i = 0; $i < count($actualRows); $i++) {
                $id = $actualRows[$i]->getAttribute('id');
                if ($id != $correctRows[$i]) {
                    PHPUnit::assertTrue(false, 'The order of items is different.');
                    break;
                }
                $item = $actualRows[$i]->findElement(WebDriverBy::className('uninsured-first-item'));
                array_push($items, $item->getText());
            }

            $browser
                // 実績情報/外泊日登録画面に遷移する
                ->visit('/group_home/result_info')
                // 保険外請求タブをクリックして保険外請求画面が正常に表示されることを確認
                ->pause(1000)
                ->waitFor('@facility-user-billing-button')
                ->click('@facility-user-billing-button')
                ->waitFor('@facility-user-billing-form-label')
                ->assertVisible('@facility-user-billing-form-label')
                // +ボタンをクリックして品目追加画面が正常に表示されることを確認
                ->pause(1000)
                ->waitFor('@un-table-button')
                ->pause(1000)
                ->click('@un-table-button')
                ->waitFor('@un-table-form')
                ->assertVisible('#item_form_lbl');

            // 品目リストの並び替えが反映されていることを確認
            // 品目リストの選択肢を取得
            $select = $browser->element('select[id="un_table_s_item_pulldown"]');
            $options = $select->findElements(WebDriverBy::cssSelector('option:not([disabled])'));
            unset($options[0]);
            $mergeOptions = array_merge($options);
            // 保険外費用画面と保険外請求画面の各品目リストの順番を比較する
            for ($i = 0; $i < count($mergeOptions); $i++) {
                $value = $mergeOptions[$i]->getText();
                if ($value != $items[$i]) {
                    PHPUnit::assertTrue(false, 'The order of items in the pull-down menu is different.');
                    break;
                }
            }
        });
    }

    /**
     * 品目の追加登録テスト
     */
    public function testAddItem()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                // 品目を追加登録する
                ->waitFor('#uninsured_table_plus')
                ->click('#uninsured_table_plus')
                ->waitFor('#add_item_name')
                ->type('#add_item_name', '間食')
                ->waitFor('#add_unit')
                ->select('#add_unit', 1)
                ->waitFor('#add_uninsured_list_poppu_ok')
                ->click('#add_uninsured_list_poppu_ok')
                // 追加登録した品目が正常に表示されることを確認
                ->pause(1000)
                ->waitFor('#table_tbody_uninsured_items')
                ->assertSourceHas('<td class="uninsured-first-item">間食</td>');
        });
    }

    /**
     * 品目の削除テスト
     */
    public function testDeleteItem()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                // 品目を削除する
                ->waitFor('#history_id_5')
                ->click('#history_id_5')
                ->waitFor('#uninsured_btn_delete')
                ->click('#uninsured_btn_delete')
                ->waitFor('#delete_uninsured_item_poppu_ok')
                ->click('#delete_uninsured_item_poppu_ok')
                ->pause(1000)
                // 削除した品目が表示されないことを確認
                ->waitFor('#table_tbody_uninsured_items')
                ->assertSourceMissing('<td class="uninsured-first-item">夜食</td>');
        });
    }
}