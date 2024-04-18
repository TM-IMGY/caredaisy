<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\FacilityUserInformation;
use Tests\DuskTestCase;

/**
 * @group approval
 */
class ApprovalTest extends DuskTestCase
{
    /**
     * 認定情報画面が正しく表示されるかをテストする。
     */
    public function testView()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();

            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                // 認定情報画面に遷移する
                ->waitFor('@facility-user-moving-into-button')
                ->click('@facility-user-care-button')
                ->waitFor('@clearBtn_approval')
                // 認定情報画面が表示されていることのチェック
                ->assertVisible('@facility-user-care-form-label')
                ->pause(1000);
        });
    }

    /**
     * 履歴更新のテスト
     */
    public function testUpdate()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                ->click('@tr_facility_user_id36')
                ->pause(2000)
                // 履歴が選択されていることのチェック
                ->assertAttribute(
                    '#table_tbody_approval > tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                // 各フォームに値を設定する
                ->select('@select_list1_approval', 21)
                ->select('@select_list2_approval', 2)
                ->type('@text-item1-approval', '2023/01/01')
                ->type('@expiration_start_date', '2023/01/01')
                ->type('@expiration_end_date', '2023/02/01')
                ->click('@js-updata-popup_approval')
                ->waitFor('#updatabtn_approval')
                ->click('#updatabtn_approval')
                ->pause(1000)
                ->assertAttribute(
                    '#table_tbody_approval > tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                // 更新した値が表示されていることのチェック
                ->assertSelected('@select_list1_approval', 21)
                ->assertSelected('@select_list2_approval', 2)
                ->assertValue('@text-item1-approval', '2023/01/01')
                ->assertValue('@expiration_start_date', '2023/01/01')
                ->assertValue('@expiration_end_date', '2023/02/01');
        });
    }

    /**
     * 新規登録のテスト
     */
    public function testNewRegister()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                ->waitFor('@clearBtn_approval')
                // 新規登録ボタンを押下する
                ->click('@clearBtn_approval')
                ->pause(1000)
                // 履歴が選択されていないことのチェック
                ->assertAttribute(
                    '#table_tbody_approval > tr',
                    'style',
                    'background-color: rgb(250, 250, 250);'
                )
                // 各フォームに値を設定する
                ->select('@select_list1_approval', 25)
                ->select('@select_list2_approval', 2)
                ->type('#text_item1_approval', '2023/03/01')
                ->type('#text_item2_approval', '2023/03/01')
                ->type('#text_item3_approval', '2023/04/01')
                ->click('@js-updata-popup_approval')
                ->pause(1000)
                ->assertAttribute(
                    '#table_tbody_approval > tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                // 登録した値が表示されていることのチェック
                ->assertSelected('@select_list1_approval', 25)
                ->assertSelected('@select_list2_approval', 2)
                ->assertInputValueIsNot('#text_item1_approval', "")
                ->assertInputValueIsNot('#text_item2_approval', "")
                ->assertInputValueIsNot('#text_item3_approval', "");
        });
    }

    /**
     * 履歴選択のテスト
     */
    public function testSelectHistory()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                ->waitFor('@clearBtn_approval')
                // 遷移時に最上位の履歴が選択されていることのチェック
                // 新規登録のテストでの利用開始日の設定次第では挙動が変わるので注意
                ->assertAttribute(
                    '#table_tbody_approval > tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                ->click('#table_tbody_approval > tr + tr')
                // 2番目の履歴を選択
                ->assertAttribute(
                    '#table_tbody_approval > tr + tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                );
        });
    }

    /**
     * 有効期間終了日自動入力のテスト
     */
    public function testAutofillEndDate()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();

            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                // 認定情報画面に遷移する
                ->pause(5000)
                ->click('@facility-user-care-button')
                ->pause(5000)
                // 有効期間終了日の自動入力の動作チェック
                ->type('@expiration_start_date', '2022/01/01') // 開始日を入力
                ->click('#jaCalApprovalStartDate') // カレンダーを閉じるために別の場所をクリック
                ->pause(1000)
                ->click('@expiration_end_date-btn1') // 1つ目の自動入力ボタンをクリック
                ->pause(1000)
                ->assertInputValue('@expiration_end_date', '2022/06/30') // 開始日からの期間が指定した期間になる終了日がセットされているかどうか確認(半年)
                ->click('@expiration_end_date-btn2') // 2つ目の自動入力ボタンをクリック
                ->pause(1000)
                ->assertInputValue('@expiration_end_date', '2022/12/31') // 開始日からの期間が指定した期間になる終了日がセットされているかどうか確認(1年)
                ->click('@expiration_end_date-btn3') // 3つ目の自動入力ボタンをクリック
                ->pause(1000)
                ->assertInputValue('@expiration_end_date', '2024/12/31') // 開始日からの期間が指定した期間になる終了日がセットされているかどうか確認(3年)
                ->click('@expiration_end_date-btn4') // 4つ目の自動入力ボタンをクリック
                ->pause(1000)
                ->assertInputValue('@expiration_end_date', '2025/12/31'); // 開始日からの期間が指定した期間になる終了日がセットされているかどうか確認(4年)
        });
    }

    /**
     * 申請中の挙動テスト
     */
    public function testRecordBehavior(): void
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $approvalProcessed = '申請中';

            // テーブルの1番上に申請中レコードを表示させること、
            // 2つ目の申請中レコードを登録すると、登録できない旨のモーダルが表示されることを確認する。
            $browser
                // 認定情報に認定済と申請中が存在する利用者を使用する。
                ->click('@tr_facility_user_id34')
                ->pause(2000)
                // 認定情報テーブルの1番上に申請中レコードが存在しているかを確認する。
                ->assertSeeIn('#table_tbody_approval > tr', $approvalProcessed)
                // 新規作成ボタンをクリックする。
                ->click('@clearBtn_approval')
                // 要介護度で、要介護5を選択する。
                ->select('@select_list1_approval', 25)
                // 認定状況で、申請中を選択する。
                ->select('@select_list2_approval', 1)
                ->pause(2000)
                // 保存ボタンを押下する。
                ->click('@js-updata-popup_approval')
                // 警告モーダルの文章表示を確認し、閉じるボタンをクリックする。
                ->assertSee('申請中の情報が既に登録されているため保存できません')
                ->click('@errorbtn2_approval')
                ->pause(2000);

            // 存在する認定済レコードを申請中として上書きし、登録できない旨のモーダルが表示されることを確認する。
            $browser
                // 上から2番目のレコードをクリックする。
                ->click('#table_tbody_approval > tr + tr')
                ->pause(2000)
                // 認定情報で、申請中を選択する。
                ->select('@select_list2_approval', 1)
                ->pause(2000)
                // 保存ボタンを押下する。
                ->click('@js-updata-popup_approval')
                // 警告モーダルの文章表示を確認し、閉じるボタンをクリックする。
                ->assertSee('申請中の情報が既に登録されているため保存できません')
                ->click('@errorbtn2_approval')
                ->pause(2000);

            // 申請中のレコードが存在する場合、認定済みのデータが新規登録できないことを確認する。
            $browser
                // 新規作成ボタンをクリックする。
                ->click('@clearBtn_approval')
                // 要介護度で、要介護5を選択する。
                ->select('@select_list1_approval', 25)
                // 認定状況で、認定済みを選択する。
                ->select('@select_list2_approval', 2)
                ->pause(2000)
                // 保存ボタンを押下する。
                ->click('@js-updata-popup_approval')
                // 警告モーダルの文章表示を確認し、閉じるボタンをクリックする。
                ->assertSee('申請中の情報が既に登録されているため保存できません')
                ->click('@errorbtn2_approval')
                ->pause(2000);

            // 存在する認定済レコードの介護度を上書きし、更新が実行できることを確認する。
            $browser
                // 上から2番目のレコードをクリックする。
                ->click('#table_tbody_approval > tr + tr')
                ->pause(2000)
                // 要介護度で、要支援1を選択する。
                ->select('@select_list1_approval', 12)
                ->pause(2000)
                // 保存ボタンを押下する。
                ->click('@js-updata-popup_approval')
                ->pause(1000)
                // 申請中以外のデータ更新用のダイアログであることを確認
                ->assertSee('登録していた認定情報が')
                ->assertSee('上書かれてしまいますが')
                ->assertSee('よろしいですか？')
                ->assertSee('認定更新・区分変更の場合は')
                ->assertSee('新規登録にて登録してください')
                // 確認ダイアログでOKを押下する
                ->click('#updatabtn_approval')
                ->pause(1000)
                // 再度2番目のレコードをクリックする
                ->click('#table_tbody_approval > tr + tr')
                ->pause(1000)
                ->assertAttribute(
                    '#table_tbody_approval > tr + tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                // 更新した値が表示されていることのチェック
                ->assertSelected('@select_list1_approval', 12);

            // 申請中のレコードの認定状況以外を上書きし、更新が実行できることを確認する。
            $browser
                // 先頭のレコードをクリックする。
                ->click('#table_tbody_approval > tr')
                ->pause(2000)
                // 要介護度で、要支援1を選択する。
                ->select('@select_list1_approval', 12)
                ->pause(2000)
                // 保存ボタンを押下する。
                ->click('@js-updata-popup_approval')
                ->pause(1000)
                // 申請中のデータ更新用のダイアログであることを確認
                ->assertSee('変更した内容を更新しますか？')
                // 確認ダイアログでOKを押下する
                ->click('#updatebtn5_approval')
                ->pause(1000)
                // 再度先頭のレコードをクリックする
                ->click('#table_tbody_approval > tr')
                ->pause(1000)
                ->assertAttribute(
                    '#table_tbody_approval > tr',
                    'style',
                    'background-color: rgb(255, 255, 238);'
                )
                // 更新した値が表示されていることのチェック
                ->assertSelected('@select_list1_approval', 12)
                // 認定状況が申請中のままであることのチェック。
                ->assertSelected('@select_list2_approval', 1);

                // 申請中のレコードの認定状況を認定済みに上書きし、更新が実行できることを確認する。
                $browser
                    // 先頭のレコードをクリックする。
                    ->click('#table_tbody_approval > tr')
                    ->pause(2000)
                    // 認定状況に認定済みを選択する。
                    ->select('@select_list2_approval', 2)
                    ->pause(2000)
                    // 必須項目を入力
                    ->type('#text_item1_approval', '2023/07/01')
                    ->type('#text_item2_approval', '2023/07/01')
                    ->type('#text_item3_approval', '2023/07/31')
                    // 保存ボタンを押下する。
                    ->click('@js-updata-popup_approval')
                    ->pause(1000)
                    // 申請中のデータ更新用のダイアログであることを確認
                    ->assertSee('変更した内容を更新しますか？')
                    // 確認ダイアログでOKを押下する
                    ->click('#updatebtn5_approval')
                    ->pause(1000)
                    // 再度先頭のレコードをクリックする
                    ->click('#table_tbody_approval > tr')
                    ->pause(1000)
                    ->assertAttribute(
                        '#table_tbody_approval > tr',
                        'style',
                        'background-color: rgb(255, 255, 238);'
                    )
                    // 認定状況が認定済みに更新されていることのチェック。
                    ->assertSelected('@select_list2_approval', 2);
        });
    }
}
