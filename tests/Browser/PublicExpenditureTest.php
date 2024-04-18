<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Carbon\Carbon;
use Tests\Browser\Pages\FacilityUserInformation;
use Tests\DuskTestCase;
use App\Models\UserFacilityServiceInformation;
use App\Models\Service;
use App\Models\UserPublicExpenseInformation;

/**
 * @group public_expenditure
 */
class PublicExpenditureTest extends DuskTestCase
{
    // テストで使用するアカウントの事業所ID
    const SERVICE_TYPE_ADD_FACILITY_NUM = 6;

    /**
     * 公費名称のテストで利用するデータを作成する
     */
    public function UserFacilityServiceInformationData()
    {
        $dateTime = Carbon::now('Asia/Tokyo');
        $dateTimeYmd = $dateTime->format('Y/m/d');
        // テスト日が開始日・終了日期間内のサービス情報を取得する
        $ufsi = UserFacilityServiceInformation::
            where('facility_id', self::SERVICE_TYPE_ADD_FACILITY_NUM)
            ->where('use_start', '<=', $dateTimeYmd)
            ->where('use_end', '>=', $dateTimeYmd)
            ->select('facility_user_id','service_id')
            ->get()
            ->toArray();

        // 各service_idが一つずつになるように重複しているものを削除する
        $tmp = array();
        $ary_result = array();
        foreach($ufsi as $key => $value){
            if( !in_array( $value['service_id'], $tmp ) ) {
                $tmp[] = $value['service_id'];
                $ary_result[] = $value;
            }
        }

        $service = Service::
            where('facility_id', self::SERVICE_TYPE_ADD_FACILITY_NUM)
            ->select('id', 'service_type_code_id')
            ->get()
            ->toArray();

        foreach ($ary_result as $key => $val) {
            foreach ($service as $value) {
                if ($value['id'] == $val['service_id']) {
                    $ary_result[$key]['service_type_code_id'] = $value['service_type_code_id'];
                }
            }
        }
        return $ary_result;
    }

    // 各サービス種類に対応する公費名称リスト
    public function dataProvider()
    {
        return [
            32 => [
                [
                    'service_type_code_id' => 1,
                    'legal_num' => [25 => '中国残留邦人等', 12 => '生活保護', 81 => '原爆助成']
                ]
            ],
            37 => [
                [
                    'service_type_code_id' => 2,
                    'legal_num' => [25 => '中国残留邦人等', 12 => '生活保護', 81 => '原爆助成']
                ]
            ],
            33 => [
                [
                    'service_type_code_id' => 3,
                    'legal_num' => [25 => '中国残留邦人等', 12 => '生活保護']
                ]
            ],
            36 => [
                [
                    'service_type_code_id' => 4,
                    'legal_num' => [25 => '中国残留邦人等', 12 => '生活保護']
                ]
            ],
            35 => [
                [
                    'service_type_code_id' => 5,
                    'legal_num' => [25 => '中国残留邦人等', 12 => '生活保護']
                ]
            ],
            55 => [
                [
                    'service_type_code_id' => 6,
                    'legal_num' => [
                        10 => '感染症37条の2',
                        15 => '自立更生',
                        19 => '原爆一般',
                        54 => '難病公費',
                        86 => '被爆体験者',
                        51 => '特定疾患・先天性血液凝固',
                        88 => '水俣病・メチル水銀',
                        87 => '有機ヒ素',
                        66 => '石綿',
                        25 => '中国残留邦人等',
                        12 => '生活保護'
                    ]
                ]
            ],
        ];
    }

    /**
     * 公費除法画面が正しく表示されるかをテストする。
     *
     */
    public function testView()
    {
        $this->browse(function (Browser $browser) {
            $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();

            $browser
                ->loginAs($user)
                ->visit(new FacilityUserInformation())
                ->waitFor('@facility-user-moving-into-button')
                ->click('@facility-user-public-expenditure-button')
                ->waitFor('#expenditure_register')
                ->assertVisible('@facility-user-public-expenditure-form-label')
                ->pause(1000);
        });
    }

    /**
     * 履歴の更新テスト
     */
    public function testUpdate()
    {
        self::testView();
        $this->browse(function (Browser $browser) {

            $bearerNumber = '12046038'; // 負担者番号
            $recipientNumber = '0000016'; // 受給者番号
            $startDate = '2020/01/01'; // 有効開始日
            $endDate = '2022/03/31'; // 有効終了日

            $browser
                ->click('@tr_facility_user_id36')
                ->pause(2000)
                // 最新の履歴が選択されているか
                ->assertAttribute(
                    '#public_expenditure_history_table_body > tr',
                    'class',
                    'public_expenditure_select_record'
                )
                // 各フォームに値を設定
                // 負担者番号
                ->type('#bearer_number', $bearerNumber)
                // 受給者番号
                ->type('#recipient_number', $recipientNumber)
                // 有効開始日
                ->type('#public_expense_effective_start_date', $startDate)
                // 有効終了日
                ->type('#public_expense_expiry_date', $endDate)
                // 保存ボタンを押下する
                ->click('#expenditure_update')
                ->waitFor('#updatabtn_public_expenditure_yearpopup')
                // 年差確認ポップアップの「はい」を押下する
                ->click('#updatabtn_public_expenditure_yearpopup')
                ->pause(1000)
                // 更新した値が表示されていることのチェック
                ->assertInputValue('#bearer_number', $bearerNumber)
                ->assertInputValue('#recipient_number', $recipientNumber)
                ->assertInputValue('#public_expense_effective_start_date', $startDate)
                ->assertInputValue('#public_expense_expiry_date', $endDate);
        });
    }

    /**
     * 新規登録のテスト
     */
    public function testNewRegister()
    {
        self::testView();
        $this->browse(function (Browser $browser) {

            $bearerNumber = '12046038'; // 負担者番号
            $recipientNumber = '0000016'; // 受給者番号
            $startDate = '2022/04/01'; // 有効開始日
            $endDate = '2024/03/31'; // 有効終了日

            $browser
                ->click('#expenditure_register')
                // 新規登録ボタン押下後各フォームが初期化されていることの確認
                // 負担者番号
                ->assertInputValue('#bearer_number', "")
                // 受給者番号
                ->assertInputValue('#recipient_number', "")
                // 有効開始日
                ->assertInputValue('#public_expense_effective_start_date', "")
                // 有効終了日
                ->assertInputValue('#public_expense_expiry_date', "")
                // 公費情報確認日
                ->assertInputValue('#confirmation_medical_insurance_date', "")
                // 各種値の設定
                ->type('#bearer_number', $bearerNumber)
                ->type('#recipient_number', $recipientNumber)
                ->type('#public_expense_effective_start_date', $startDate)
                ->type('#public_expense_expiry_date', $endDate)
                ->click('#expenditure_update')
                ->waitFor('#updatabtn_public_expenditure_yearpopup')
                ->click('#updatabtn_public_expenditure_yearpopup')
                ->pause(1000)
                // 登録した値が表示されているか
                ->assertInputValue('#bearer_number', $bearerNumber)
                ->assertInputValue('#recipient_number', $recipientNumber)
                ->assertInputValue('#public_expense_effective_start_date', $startDate)
                ->assertInputValue('#public_expense_expiry_date', $endDate)
                ->screenshot('public_register');
        });
    }

    /**
     * 履歴選択時の挙動テスト
     */
    public function testSelectHistory()
    {
        self::testView();
        $this->browse(function (Browser $browser) {
            $browser
                // 遷移時に最上位の履歴が選択されていることのチェック
                // 新規登録のテストでの利用開始日の設定次第では挙動が変わるので注意
                ->assertAttribute(
                    '#public_expenditure_history_table_body > tr',
                    'class',
                    'public_expenditure_select_record'
                )
                // 2番目の履歴を選択
                ->click('#public_expenditure_history_table_body > tr:nth-child(2)')
                // 2番目の履歴が選択されハイライトされていることのチェック
                ->assertAttribute(
                    '#public_expenditure_history_table_body > tr:nth-child(2)',
                    'class',
                    'public_expenditure_select_record'
                );
        });
    }

    /**
     * @dataProvider dataProvider
     * @param $requestParam リクエストデータ
     * 負担者番号に紐づく公費略称が正しく表示されているかテストする
     */
    public function testLegalName($requestParam)
    {
        $data = self::UserFacilityServiceInformationData();
        self::testView();
        $this->browse(function (Browser $browser) use($data, $requestParam) {
            foreach ($data as $key => $value) {
                if ($requestParam['service_type_code_id'] !== $value['service_type_code_id']) {
                    continue;
                }
                // 利用者を選択する
                $browser
                    ->click('#table_facility_user_id'.$value['facility_user_id'])
                    ->pause(1000);

                foreach ($requestParam['legal_num'] as $num => $legalName) {
                    $browser
                        // 負担者番号を入力する
                        // 公費名称は負担者番号の上2桁で判断する
                        ->type('#bearer_number', $num.'000000')
                        // 負担者番号からフォーカスを外すため受給者番号を選択する
                        ->type('recipient_number', '')
                        ->pause(1000)
                        // 公費名称に負担者番号に対応する名称が表示されているかチェックする
                        ->assertInputValue('legal_name', $legalName);
                }
            }

            // 存在しない負担者番号を入力した場合の挙動をテストする
            $browser
                // 負担者番号に存在しない番号を入力する
                ->type('#bearer_number', '00000000')
                // 負担者番号からフォーカスを外すため受給者番号を選択する
                ->type('recipient_number', '')
                ->pause(1000)
                // 画面にバリデーションメッセージが表示されていることをチェックする
                ->assertSee('該当する公費がありません。負担者番号を見直してください')
                // 公費名称が空欄であることをチェックする
                ->assertInputValue('legal_name', '');
        });
    }

    /**
     * 作成日が同じ履歴選択時の挙動を確認
     * ※障害の詳細はKAIGO_RECEIPT-1560参照
     */
    public function testSelectSameCreatedAtHistory () {

        $this->browse(function (Browser $browser) {

            $user = User::where('employee_number', 'test_authority@0000000001.care-daisy.com')->first();

            // 共通の作成日を定義
            $testCreatedDate = '2022/01/01 00:00:00';
            // 利用者毎の公費情報ID：1のデータの作成日を更新
            UserPublicExpenseInformation::Where('public_expense_information_id', '=', '1')
                ->update(['created_at' => $testCreatedDate]);
            // 利用者毎の公費情報ID：2のデータの作成日を更新
            UserPublicExpenseInformation::Where('public_expense_information_id', '=', '2')
                ->update(['created_at' => $testCreatedDate]);

            $browser
                ->loginAs($user)
                // 利用者情報を開く
                ->visit(new FacilityUserInformation())
                ->pause(1000)
                // 公費情報を選択
                ->click('@facility-user-public-expenditure-button')
                ->pause(1000)
                // 利用者毎の公費情報ID：1のデータが登録された利用者を選択
                ->click('@tr_facility_user_id1')
                ->pause(1000)
                // 先頭の履歴を選択
                ->click('#public_expenditure_history_table_body > tr:nth-child(1)')
                ->pause(1000)
                // 入力欄に選択した履歴の値が設定されることを確認
                ->assertValue('#bearer_number', '12000000')
                ->assertValue('#recipient_number', '0000001')
                ->pause(1000)
                // 利用者毎の公費情報ID：2のデータが登録された利用者を選択
                ->click('@tr_facility_user_id4')
                ->pause(1000)
                // 先頭の履歴を選択
                ->click('#public_expenditure_history_table_body > tr:nth-child(1)')
                ->pause(1000)
                // 入力欄に選択した履歴の値が設定されることを確認
                ->assertValue('#bearer_number', '12000000')
                ->assertValue('#recipient_number', '0000002');
        });
    }
}
