<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\FacilityUserInformation;
use Tests\DuskTestCase;

/**
 * @group service
 */
class ServiceTest extends DuskTestCase
{
    /**
     * サービス画面が正しく表示されるかをテストする。
     */
    public function testServiceView()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();

            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                ->waitFor('@facility-user-moving-into-button')
                // サービス画面に遷移する
                ->click('@facility-user-service-button')
                ->waitFor('#clearBtn_service')
                // サービス画面が表示されていることのチェック
                ->assertVisible('@facility-user-service-form-label')
                ->pause(1000);
        });
    }

    /**
     * 履歴更新のテスト
     */
    public function testUpdate()
    {
        self::testServiceView();
        $this->browse(function (Browser $browser) {
            // 利用状況を「未利用」に、利用開始日・終了日を変更する
            $browser
                ->click('@tr_facility_user_id36')
                ->pause(2000)
            // 履歴が選択されていることのチェック
                ->assertAttribute(
                    '#table_tbody_service > tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                // 各フォームに値を設定する
                ->select('#select_list3_service', 2)
                ->type('#text_item1_service', '2021/10/01')
                ->type('#text_item2_service', '2022/03/31')
                ->click('#js-updata-popup_service')
                ->waitFor('#updatabtn_service')
                ->click('#updatabtn_service')
                ->pause(1000)
                // 更新した値が表示されていることのチェック
                ->assertSelected('#select_list3_service', 2)
                ->assertInputValue('#text_item1_service', "2021/10/01")
                ->assertInputValue('#text_item2_service', "2022/03/31")
                // 後続テストのために値を戻しておく
                ->type('#text_item1_service', '2021/01/01')
                ->type('#text_item2_service', '2025/01/31')
                ->click('#js-updata-popup_service')
                ->waitFor('#updatabtn_service')
                ->click('#updatabtn_service')
                ->pause(1000)
                // 念のため更新されたかチェックしておく
                ->assertInputValue('#text_item1_service', "2021/01/01")
                ->assertInputValue('#text_item2_service', "2025/01/31");
        });
    }

    /**
     * 新規登録のテスト
     */
    public function testNewRegister()
    {
        self::testServiceView();
        $this->browse(function (Browser $browser) {
            $browser
                // 新規登録ボタン押下後、利用開始日以外のフォームが初期化されているか
                ->waitFor('#clearBtn_service')
                ->click('#clearBtn_service')
                ->pause(1000)
                ->assertSelected('#select_list1_service', "")
                ->assertSelected('#select_list2_service', "")
                ->assertSelected('#select_list3_service', "")
                ->assertInputValue('#text_item1_service', "2000/12/31")
                ->assertInputValue('#text_item2_service', "")
                // 各フォームに値を設定する
                // todo 選択対象をハードコーディングしてるのでどうにかしたい
                ->select('select_item_service', 6)
                ->pause(1000)
                ->select('#select_list2_service', 1)
                ->select('#select_list3_service', 1)
                ->type('#text_item1_service', '1999/03/01')
                ->type('#text_item2_service', '1999/03/31')
                ->click('#js-updata-popup_service')
                ->pause(1000)
                // 登録した値が表示されていることのチェック
                ->assertSelected('select_item_service', 6)
                ->assertSelected('#select_list2_service', 1)
                ->assertSelected('#select_list3_service', 1)
                ->assertInputValue('#text_item1_service', "1999/03/01")
                ->assertInputValue('#text_item2_service', "1999/03/31");
        });
    }

    /**
     * 履歴切り替えのテスト
     */
    public function testSelectHistory()
    {
        self::testServiceView();
        $this->browse(function (Browser $browser) {
            $browser
                //最新の履歴が選択されているか
                ->assertAttribute(
                    '#table_tbody_service > tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                // 2番目の履歴を選択
                ->click('#table_tbody_service > tr + tr')
                ->pause(1000)
                // 2番目の履歴が選択されているか
                ->assertAttribute(
                    '#table_tbody_service > tr + tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                );
        });
    }
}
