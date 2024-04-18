<?php

namespace Tests\Feature\AutoServiceCode;

use App\User;
use Tests\TestCase;

/**
 * 基本のサービスコードの取得機能のテスト(異常系)
 */
class AbnormalityTest extends TestCase
{
    /**
     * データプロバイダ。
     * @return array
     */
    public function abnormalityDataProvider(): array
    {
        return [
            // サービスの提供を受けていない施設利用者
            'not_receiving_services' => [
                [
                    'facility_id' => 6,
                    'facility_user_id' => 27,
                    'year' => 2022,
                    'month' => 6
                ],
                [
                    'status_code' => 422
                ]
            ],
            // サービスが有効期間外の施設利用者
            'receiving_expired_services' => [
                [
                    'facility_id' => 6,
                    'facility_user_id' => 26,
                    'year' => 2020,
                    'month' => 12
                ],
                [
                    'status_code' => 422
                ]
            ],
            // 認定情報がない施設利用者の場合エラー
            'no_care_information' => [
                [
                    'facility_id' => 6,
                    'facility_user_id' => 35,
                    'year' => 2022,
                    'month' => 6
                ],
                [
                    'status_code' => 422
                ]
            ],
            // 認定情報が有効期間外の施設利用者
            'receiving_expired_care_information' => [
                [
                    'facility_id' => 6,
                    'facility_user_id' => 34,
                    'year' => 2020,
                    'month' => 12
                ],
                [
                    'status_code' => 422
                ]
            ],
            // アカウントが権限を持たない施設利用者
            'unaccessible' => [
                [
                    // 事業所のID自体は正しい
                    'facility_id' => 6,
                    // 施設利用者だけ不正
                    'facility_user_id' => 1,
                    'year' => 2020,
                    'month' => 12
                ],
                [
                    'status_code' => 400
                ]
            ]
        ];
    }

    /**
     * 異常系のテスト。
     * @dataProvider abnormalityDataProvider
     * @param $requestData リクエストデータ
     * @param $expectedResult 期待される結果データ
     * @return void
     */
    public function testGetBasicServiceCode(array $requestData, array $expectedResult): void
    {
        // テストユーザーが認証する。
        $user = User::where('employee_number', 'service_type_addition_tester@0000000006.care-daisy.com')->first();
        $this->actingAs($user);

        // 自動サービスコード機能をリクエストする。
        $urlAutoServiceCode = '/group_home/service/auto_service_code/get?'
            .http_build_query([
                'facility_id' => $requestData['facility_id'],
                'facility_user_id' => $requestData['facility_user_id'],
                'year' => $requestData['year'],
                'month' => $requestData['month']
            ]);
        $responseAutoServiceCode = $this->get($urlAutoServiceCode);
        $responseAutoServiceCode->assertStatus($expectedResult['status_code']);
    }
}
