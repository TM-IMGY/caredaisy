<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;

/**
 * 公費の次回分のリクエストのテスト。
 */
class PublicExpenseNextTest extends TestCase
{
    /**
     * TODO: インメモリリポジトリをDIする方が見やすい。
     * データプロバイダ。
     */
    public function dataProvider(): array
    {
        return [
            // テスト目的: アカウントがアクセス可能な施設利用者の公費をリクエストした場合の動きを確認する。
            'accessible' => [
                // publicExpenseInformationId
                10,
                // statusCode
                200
            ],
            // テスト目的: アカウントがアクセス不可能な施設利用者の公費をリクエストした場合の動きを確認する。
            'not_accessible' => [
                // publicExpenseInformationId
                1,
                // statusCode
                400
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param int $publicExpenseInformationId 公費のID
     * @param int $statusCode ステータスコード
     */
    public function test(
        int $publicExpenseInformationId,
        int $statusCode
    ): void {
        // テストユーザーを取得する。
        $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();

        // テストユーザーでログインする。
        $this->actingAs($user);

        // 公費の取得のリクエストのパラメーターを作成する。
        $parameter = http_build_query([
            'public_expense_information_id' => $publicExpenseInformationId
        ]);

        // リクエストURLを作成する。
        $url = '/group_home/service/public_expense_next/get?'.$parameter;

        $result = $this->get($url);

        $result->assertStatus($statusCode);
    }
}
