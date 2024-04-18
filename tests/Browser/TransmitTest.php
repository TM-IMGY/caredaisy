<?php

namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\TransmitInformation;
use Tests\DuskTestCase;

/**
 * @group transmit
 */
class TransmitTest extends DuskTestCase
{
    /**
     * 伝送請求履歴に国保連側と同様の状態ステータスが正しく反映されているかのテストを行う
     * @return void
     */
    public function testTransmitListStatusVisible(): void
    {
        $this->browse(function (Browser $browser) {
            // TODO: 定数ファイル作成のタイミングでテストユーザーの情報を置き換える
            $user = User::where('employee_number', 'transmit_test@0000000009.care-daisy.com')->first();

            // 画面側の状態ステータス名に対しての比較用の状態ステータス名を用意する
            $transmitStatusNames = [
                1 => '伝送中',
                2 => '到達完了',
                3 => '連合会到達',
                4 => '受付中',
                5 => '様式エラー有',
                6 => '受付完了',
                7 => '取消依頼中',
                8 => '取消中',
                9 => '取消完了',
                10 => '送信完了',
                11 => '返戻通知処理完了',
                12 => '支払通知処理完了',
                13 => '完了',
                14 => '到達エラー',
                15 => '伝送エラー',
                16 => '外部エラー',
            ];

            $browser
                ->loginAs($user)
                ->visit(new TransmitInformation())
                ->pause(1000)
                // 伝送請求画面へ遷移したことを確認する
                ->waitForText('伝送請求')
                // 未送信ステータスの伝送請求履歴が請求データ一覧に表示されることを確認する
                ->assertSeeIn('@transmit_list_table_top_record', '未送信');

            // 請求履歴一覧に国保連側と同様の状態ステータスを持つ履歴が表示されることを確認する
            for ($i = 1; $i <= count($transmitStatusNames); $i++) {
                $browser->assertSeeIn("#history_list tr:nth-child({$i})", $transmitStatusNames[$i]);
            }
        });
    }
    
    /**
     * 伝送請求画面 入力チェック確認
     * 入力チェックエラー発生時にエラーメッセージが表示されることを確認する
     * ※ここではエラーの出力のみ確認し、エラー詳細の確認はvalidationテストで行う
     */
    public function testTransmitListValidationError(): void
    {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        $this->login();
        
        $this->browse(function (Browser $browser) {
            // ブラウザテストの実行
            $browser
                ->waitFor('#transmit_set_filter')
                // 処理対象年月_開始月の確認
                // 処理対象年月を入力
                ->value('#transmit_from_date', '2000/03')
                ->value('#transmit_to_date', '2021/04')
                // 絞り込みボタン押下
                ->click('#transmit_set_filter')
                ->pause(1000)
                // エラーメッセージを確認する
                ->assertSeeIn('#transmitValidateErrors', '2000年4月以降の年月を入力してください')
                // 処理対象年月_終了月の確認
                // 処理対象年月を入力
                ->value('#transmit_from_date', '2021/03')
                ->value('#transmit_to_date', '2100/01')
                // 絞り込みボタン押下
                ->click('#transmit_set_filter')
                ->pause(1000)
                // エラーメッセージを確認する
                ->assertSeeIn('#transmitValidateErrors', '2099年12月以前の年月を入力してください')
                ;
        });

    }

    /**
     * 通知文書画面 入力チェック確認
     * 入力チェックエラー発生時にエラーメッセージが表示されることを確認する
     * ※ここではエラーの出力のみ確認し、エラー詳細の確認はvalidationテストで行う
     */
    public function testDocumentListValidationError(): void
    {
        // テストに使用するアカウントを設定
        $this->testAccount = 'test_authority@0000000001.care-daisy.com';
        $this->login();
        
        $this->browse(function (Browser $browser) {
            // ブラウザテストの実行
            $browser
                ->pause(1000)
                // 通通知文書を選択
                ->click('@tm-contents-document-tab')
                ->waitFor('#document_set_filter')
                // 発行日_開始日の確認
                // 発行日を入力
                ->value('#document_from_date', '2000/03/31')
                ->value('#document_to_date', '2021/04/01')
                // 絞り込みボタン押下
                ->click('#document_set_filter')
                ->pause(1000)
                // エラーメッセージを確認する
                ->assertSeeIn('#documentValidateErrors', '2000年4月以降の年月を入力してください')
                // 発行日_終了日の確認
                // 発行日を入力
                ->value('#document_from_date', '2021/03/01')
                ->value('#document_to_date', '2100/01/01')
                // 絞り込みボタン押下
                ->click('#document_set_filter')
                ->pause(1000)
                // エラーメッセージを確認する
                ->assertSeeIn('#documentValidateErrors', '2099年12月以前の年月を入力してください')
                ;
        });
    }

    /**
     * 伝送情報画面共通ログイン処理
     * 指定したユーザーでログインし、伝送情報画面へ遷移する
     */
    private function login():void
    {
        $this->browse(function (Browser $browser) {
            // テストユーザーの情報を取得
            $account = User::where('employee_number', $this->testAccount)->first();

            // ブラウザ上の動作を開始
            $browser
                // テストユーザーでログインする
                ->loginAs($account)
                // 伝送情報画面へ遷移する
                ->visit(new TransmitInformation());
        });
    }
}
