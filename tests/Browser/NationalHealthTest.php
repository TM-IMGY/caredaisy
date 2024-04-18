<?php

namespace Tests\Browser;

use App\User;
use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\ResultInformation;
use Tests\DuskTestCase;

/**
 * @group national_health
 */
class NationalHealthTest extends DuskTestCase
{
    /**
     * 国保連請求画面に遷移して画面が表示されることをテストする
     * @return void
     */
    public function testTransition(): void
    {
        $this->browse(function (Browser $browser) {
            // TODO: 定数ファイル作成のタイミングでテストユーザーの情報を置き換える。
            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

            $facilityUserName = '施設利用者A';

            $browser
                ->loginAs($user)
                ->visit(new ResultInformation())
                ->waitForText($facilityUserName)
                // 国保連請求タブボタンをクリックした場合、国保連請求フォームが表示されることをテストする。
                ->click('@national-health-button')
                ->waitFor('@national-health-form-label');
        });
    }

    public function testViewPaymentDetails()
    {
        self::testTransition();
        $this->browse(function (Browser $browser) {
            $browser
                // 対象月を2022/09に変更する
                ->select('@year-month-pulldown', '2021/09')
                ->pause(5000)
                // 給付費明細欄にサービス内容が表示されていることをチェックする
                ->assertPresent('.caredaisy_table_cell')
                // 給付費明細欄に合計が表示されていることをチェックする
                ->assertPresent('.total')
                // 給付費明細欄にサービス単位数合計が表示されていることをチェックする
                ->assertPresent('.service_unit_amount_total')
                // サービス単位数合計に値が入っていることをチェックする
                ->assertInputValueIsNot('.service_unit_amount_total', '')
                // 給付費明細欄に公費対象単位合計が表示されていることをチェックする
                ->assertPresent('.public_expenditure_amount_total')
                // 公費対象単位合計に値が入っていることをチェックする
                ->assertInputValueIsNot('.public_expenditure_amount_total', '');
        });
    }

    /**
     * 種類55利用中利用者を選択した際に、専用の要素が表示されているかテストする
     */
    public function testType55Display()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'hospital@0000000008.care-daisy.com')->first();

            $facilityUserName = '医A';
            // assertSee/assertSeeIn だと部分一致でしかみないようなのでassertSourceHas でアサートする
            $totalCreditsLabel = '<td class="nh_table_cell nh_billing_td" id="nh_claim_for_benefits_table_total_credits_label">①点数・給付単位数</td>';
            $unitCreditsLabel = '<td class="nh_table_cell nh_billing_td" id="nh_claim_for_benefits_table_unit_credits_label">②点数・単位数単価</td>';
            $benefitBillingLabel = '請求額集計欄';

            $browser
                ->loginAs($user)
                ->visit(new ResultInformation())
                ->waitForText($facilityUserName)
                // 国保連請求タブボタンをクリックした場合、国保連請求フォームが表示されることをテストする。
                ->click('@national-health-button')
                ->waitFor('@national-health-form-label');

            $browser
                ->waitFor('@special_medical_label')
                // 特別診療費が表示されていることをチェックする
                ->assertVisible('@special_medical_label')
                // 「給付額請求欄」が「請求額集計欄」に変更されているかチェックする
                ->assertSee($benefitBillingLabel)
                // 給付額請求欄の➀➁がそれぞれ
                //「点数・給付単位数」「点数・単位数単価」となっていることをチェックする
                ->assertSourceHas($totalCreditsLabel)
                ->assertSourceHas($unitCreditsLabel)
                //「保険分特定治療・特別診療費」が表示されているかチェックする
                ->assertVisible('@nh_benefit_billing_sp_medical_expenses_label')
                //「公費分特定治療・特別診療費」が表示されているかチェックする
                ->assertVisible('@nh_benefit_billing_sp_public_medical_expenses_label')
                //「特定入所者介護サービス費」が表示されているかチェックする
                ->assertVisible('@incompetent_resident_label');
        });
    }

    public function dataProvider()
    {
        return [
            [
                [
                    'type' => 32,
                    // 利用者名
                    'user_name' => '種別32外泊した人',
                    // 利用者を選択する際に必要な情報
                    'element_id' => '#table_facility_user_id36',
                    // 対象種類でのみ表示される要素のリスト
                    'assert_source' => [
                        '<td class="nh_table_cell nh_billing_td" id="nh_claim_for_benefits_table_total_credits_label">①単位数合計</td>',
                        '<td class="nh_table_cell nh_billing_td" id="nh_claim_for_benefits_table_unit_credits_label">②単位数単価</td>'
                    ],
                ]
            ],
            [
                [
                    'type' => 33,
                    'user_name' => '種別33要介護5',
                    'element_id' => '#table_facility_user_id21',
                    'assert_source' => [
                        '<td class="nh_billing_td nh_table_cell">①外部利用型外給付単位数</td>',
                        '<td class="nh_billing_td nh_table_cell">②外部利用型上限管理対象単位数</td>',
                        '<td class="nh_billing_td nh_table_cell">③外部利用型給付上限単位数</td>'
                    ]
                ]
            ],
            [
                [
                    'type' => 55,
                    'user_name' => '種類55公費あり',
                    'element_id' => '#table_facility_user_id39',
                    'assert_source' => [
                        '<td class="nh_table_cell nh_billing_td" id="nh_claim_for_benefits_table_total_credits_label">①点数・給付単位数</td>',
                        '<td class="nh_table_cell nh_billing_td" id="nh_claim_for_benefits_table_unit_credits_label">②点数・単位数単価</td>',
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param $requestParam リクエストデータ
     */
    public function testChangeDisplay($requestParam)
    {
        $this->browse(function (Browser $browser) use ($requestParam) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();

            $browser
                ->loginAs($user)
                ->visit(new ResultInformation())
                ->waitForText($requestParam['user_name'])
                // 国保連請求タブボタンをクリックした場合、国保連請求フォームが表示されることをテストする。
                ->click('@national-health-button')
                ->waitFor('@national-health-form-label');
        });

        $this->createBrowsersFor(function (Browser $browser) use ($requestParam) {
            $browser
                // ユーザーを選択する
                ->click($requestParam['element_id'])
                // 選択されているか(ハイライトされているか)チェックする
                ->assertAttribute(
                    $requestParam['element_id'],
                    'class',
                    'facility_user_tr fu_table_selected_record'
                )
                ->pause(500);

            // 対象種類でのみ表示される要素がページ上に存在するかチェックする
            foreach($requestParam['assert_source'] as $source){
                $browser->assertSourceHas($source);
            }

            // 種類55特有の情報が表示されていないことをチェックする
            if ($requestParam['type'] !== 55) {
                $browser
                    //「特別診療費」が表示されていないことをチェックする
                    ->assertMissing('@special_medical_label')
                    //「保険分特定治療・特別診療費」が表示されていないことをチェックする
                    ->assertMissing('@nh_benefit_billing_sp_medical_expenses_label')
                    //「公費分特定治療・特別診療費」が表示されていないことをチェックする
                    ->assertMissing('@nh_benefit_billing_sp_public_medical_expenses_label')
                    //「特定入所者介護サービス費」が表示されていないことをチェックする
                    ->assertMissing('@incompetent_resident_label')->screenshot($requestParam['type']);
            } else {
                // 種類55特有の情報が表示されていることをチェックする
                $browser
                    //「特別診療費」が表示されていることをチェックする
                    ->assertVisible('@special_medical_label')
                    //「保険分特定治療・特別診療費」が表示されているかチェックする
                    ->assertVisible('@nh_benefit_billing_sp_medical_expenses_label')
                    //「公費分特定治療・特別診療費」が表示されているかチェックする
                    ->assertVisible('@nh_benefit_billing_sp_public_medical_expenses_label')
                    //「特定入所者介護サービス費」が表示されているかチェックする
                    ->assertVisible('@incompetent_resident_label')->screenshot($requestParam['type']);
            }
        });
        // ログアウトしてセッションデータを削除する
        foreach (static::$browsers as $browser) {
            $browser->driver->manage()->deleteAllCookies();
        }
    }


}
