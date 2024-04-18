<?php

namespace Tests\Browser;

use App\User;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\Browser\Pages\ResultInformation;
use Tests\DuskTestCase;

/**
 * 実績登録画面
 * 
 * @group result_registration
 */
class ResultRegistrationTest extends DuskTestCase
{
    /**
     * 画面表示テスト
     * 
     * @return void
     */
    public function testView()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();
            $browser
                ->loginAs($user)
                ->visit(new ResultInformation())
                ->pause(1000);

            // 実績登録画面が表示されることを確認
            $browser
                ->waitFor('@service-result-button')
                ->click('@service-result-button')
                ->pause(2000);
            $class = $browser->attribute('#tm_contents_1', 'class');
            PHPUnit::assertTrue(
                strpos($class, 'tm_contents_hidden') === false,
                'The display is not switched.'
            );
        });
    }

    /**
     * + (プラス) ボタンテスト
     * 
     * @return void
     */
    public function testAddServiceCode()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                ->click('#table_facility_user_id31')
                ->pause(1000);

            // サービスコード選択のモーダルが表示されることを確認
            $browser
                ->waitFor('#result_registration_table_plus')
                ->click('#result_registration_table_plus')
                ->pause(1000);
            $class = $browser->attribute('#service_code_form_basic', 'class');
            PHPUnit::assertTrue(
                strpos($class, 'result_registration_hidden') === false,
                'The modal is not displayed.'
            );
            // テーブルに選択したサービスコードが追加されていることを確認
            $selectServiceCode = $browser->element('#service_code_form_basic_tbody > tr:first-child > td:nth-child(2)')->getText();
            $browser
                ->click('#service_code_form_basic_tbody > tr')
                ->waitFor('#service_code_form_register_basic')
                ->click('#service_code_form_register_basic')
                ->pause(1000);
            $Table = $browser->element('#result_registration_tbody');
            $serviceCodes = $Table->findElements(WebDriverBy::className('result_registration_table_service'));
            $setServiceCode = count($serviceCodes) != 0 ? $serviceCodes[0]->getText() : '';
            PHPUnit::assertTrue(
                $selectServiceCode === $setServiceCode,
                'Could not add service code.'
            );
    
        });
    }

    /**
     * 再集計ボタンテスト
     * 
     * @param  bool $double false: シングルクリック, true: ダブルクリック
     * @return void
     */
    public function testRecount(bool $double = false)
    {
        self::testView();
        $this->browse(function (Browser $browser) use ($double) {
            $browser
                ->click('#table_facility_user_id31')
                ->pause(1000);

            if (!$double) {
                $browser
                    ->click('@recount-button')
                    ->pause(1000);
            } else {
                $browser
                    ->click('@recount-button')
                    ->click('@recount-button')
                    ->pause(1000);
            }
            // 再集計によってテーブルにサービスコードが1つ以上追加されていることを確認
            $Table = $browser->element('#result_registration_tbody');
            $serviceCodes = $Table->findElements(WebDriverBy::className('result_registration_table_service'));
            PHPUnit::assertTrue(
                count($serviceCodes) >= 1,
                'No service code.'
            );
            // 追加されたサービスコードの重複有無を確認
            $serviceCodeList = array();
            for ($i = 0; $i < count($serviceCodes); $i++) {
                array_push($serviceCodeList, $serviceCodes[$i]->getText());
            }
            PHPUnit::assertTrue(
                count($serviceCodeList) === count(array_unique($serviceCodeList)),
                'Duplicate service codes.'
            );
        });
    }

    /**
     * 保存ボタンテスト
     * 
     * @return void
     */
    public function testSave()
    {
        // ダブルクリック時のテスト
        self::testRecount(true);
        $this->browse(function (Browser $browser) {
            // いいえ選択でモーダルが閉じることを確認
            $browser
                ->click('@result-registration-save-btn')
                ->waitFor('@confirmation-dialog-button-no')
                ->click('@confirmation-dialog-button-no')
                ->assertSourceMissing('<p class="caredaisy_confirmation_dialog_message">この内容で保存しますか</p>');
            // はい選択で実績が保存されることを確認
            $beforeTable = $browser->element('#result_registration_tbody');
            $beforeRows = $beforeTable->findElements(WebDriverBy::tagName('tr'));
            $beforeList = array();
            for ($i = 0; $i < count($beforeRows); $i++) {
                array_push($beforeList, $beforeRows[$i]->getText());
            }
            $browser
                ->click('@result-registration-save-btn')
                ->waitFor('@confirmation-dialog-button-yes')
                ->click('@confirmation-dialog-button-yes')
                ->pause(1000)
                ->visit('/group_home/result_info')
                ->pause(1000)
                ->waitFor('@service-result-button')
                ->click('@service-result-button')
                ->pause(2000);
            $afterTable = $browser->element('#result_registration_tbody');
            $afterRows = $afterTable->findElements(WebDriverBy::tagName('tr'));
            $afterList = array();
            for ($i = 0; $i < count($afterRows); $i++) {
                array_push($afterList, $afterRows[$i]->getText());
            }
            PHPUnit::assertTrue(
                $beforeList === $afterList,
                'Failed to save.'
            );
        });
    }
}