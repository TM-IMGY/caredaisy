<?php

namespace Tests\Feature;

use Tests\TestCase;

use Carbon\Carbon;
use App\Models\OauthClient;
use App\Models\UserFacilityServiceInformation;
use App\Models\UserCareInformation;
use App\Models\ServicePlan;
use App\Models\FacilityUser;
use App\Models\UserFacilityInformation;

/**
 * 利用者同期情報取得APIのテスト
 *
 * @group api
 */
class GetUserInformationApiTest extends TestCase
{

    /**
     * アクセストークン取得API実行
     */
    public function testRequestAccessTokenApi(): array
    {
        // API実行
        $response = $this->withHeaders([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ])->postJson('/oauth/token', $this->getAccesstokenApiParams());
        $response->assertOk() // status code が200であることの確認
            ->assertJson(['access_token' => true]); // レスポンス"access_token"が存在することの確認

        return $response->decodeResponseJson();
    }

    /**
     * アクセストークン取得API用パラメータ取得
     */
    private function getAccesstokenApiParams(): array
    {
        // oauth_clients のデータを取得
        $oauthClient = OauthClient::where('name', 'like', '%Password Grant Client')
            ->select(['id', 'secret'])
            ->first();

        return [
            'grant_type'    => 'password',
            'client_id'     => $oauthClient->id,
            'client_secret' => $oauthClient->secret,
            'username'      => 'test_authority@0000000001.care-daisy.com',
            'password'      => 'password',
            'scope'         => '',
        ];
    }

    /**
     * 利用者情報取得テスト(i_user_facility_service_informations更新時)
     *
     * @depends testRequestAccessTokenApi
     */
    public function testGetUserDataAfterUpdateIufsi(array $resAccessToken): void
    {
        // i_user_facility_service_informations の updated_at を現在の日時に更新
        UserFacilityServiceInformation::where('facility_user_id', '=', '1')
            ->update(['updated_at' => now()]);

        // テスト実行
        $response = $this->requestUsersApi($resAccessToken['access_token'], '0000000001');
        $response->assertOk() // status code が200であることの確認
            ->assertJsonPath('result_info.result', 'OK') // レスポンス"result"が"OK"であることの確認
            ->assertJson(['facility_user_get' => true]); // レスポンス"facility_user_get"が存在することの確認
    }

    /**
     * 利用者情報取得テスト(i_user_care_informations更新時)
     *
     * @depends testRequestAccessTokenApi
     */
    public function testGetUserDataAfterUpdateIuci(array $resAccessToken): void
    {
        // i_user_care_informations の updated_at を現在の日時に更新
        UserCareInformation::where('facility_user_id', '=', '1')
            ->update(['updated_at' => now()]);

        // テスト実行
        $response = $this->requestUsersApi($resAccessToken['access_token'], '0000000001');
        $response->assertOk() // status code が200であることの確認
            ->assertJsonPath('result_info.result', 'OK') // レスポンス"result"が"OK"であることの確認
            ->assertJson(['facility_user_get' => true]); // レスポンス"facility_user_get"が存在することの確認
    }

    /**
     * 利用者情報取得テスト(i_service_plans更新時)
     *
     * @depends testRequestAccessTokenApi
     */
    public function testGetUserDataAfterUpdateIsp(array $resAccessToken): void
    {
        // i_service_plans の updated_at を現在の日時に更新
        ServicePlan::where('facility_user_id', '=', '1')
            ->update(['updated_at' => now()]);

        // テスト実行
        $response = $this->requestUsersApi($resAccessToken['access_token'], '0000000001');
        $response->assertOk() // status code が200であることの確認
            ->assertJsonPath('result_info.result', 'OK') // レスポンス"result"が"OK"であることの確認
            ->assertJson(['facility_user_get' => true]); // レスポンス"facility_user_get"が存在することの確認
    }

    /**
     * 利用者情報取得テスト(i_facility_user更新時)
     *
     * @depends testRequestAccessTokenApi
     */
    public function testGetUserDataAfterUpdateIfu(array $resAccessToken): void
    {
        // i_facility_users の updated_at を現在の日時に更新
        FacilityUser::where('facility_user_id', '=', '1')
            ->update(['updated_at' => now()]);

        // テスト実行
        $response = $this->requestUsersApi($resAccessToken['access_token'], '0000000001');
        $response->assertOk() // status code が200であることの確認
            ->assertJsonPath('result_info.result', 'OK') // レスポンス"result"が"OK"であることの確認
            ->assertJson(['facility_user_get' => true]); // レスポンス"facility_user_get"が存在することの確認
    }

    /**
     * 利用者情報の全件取得テスト
     *
     * @depends testRequestAccessTokenApi
     */
    public function testGetUserDataAll(array $resAccessToken): void
    {
        // 事業所に紐付く利用者の件数を取得
        $userCount = UserFacilityInformation::where('facility_id', '=', '1')->count();

        // テスト実行
        $response = $this->requestUsersApi($resAccessToken['access_token'], '0000000001', true);
        $response->assertOk() // status code が200であることの確認
            ->assertJsonPath('result_info.result', 'OK') // レスポンス"result"が"OK"であることの確認
            ->assertJsonCount($userCount, 'facility_user_get'); // 事業所に紐付く利用者が全件取得できていることの確認
    }

    /**
     * WARNINGテスト
     *
     * @depends testRequestAccessTokenApi
     */
    public function testWarningGetUserData(array $resAccessToken): void
    {
        // 利用者情報の更新日時を前日に更新
        $yesterday = Carbon::parse('yesterday')->format('Y/m/d H:i:s');
        $facilityUserIds = UserFacilityInformation::where('facility_id', '=', '1')->pluck('facility_user_id');
        UserFacilityServiceInformation::where('facility_id', '=', '1')
            ->update(['updated_at' => $yesterday]);
        UserCareInformation::whereIn('facility_user_id', $facilityUserIds)
            ->update(['updated_at' => $yesterday]);
        ServicePlan::whereIn('facility_user_id', $facilityUserIds)
            ->update(['updated_at' => $yesterday]);
        FacilityUser::whereIn('facility_user_id', $facilityUserIds)
            ->update(['updated_at' => $yesterday]);

        // テスト実行
        $response = $this->requestUsersApi($resAccessToken['access_token'], '0000000001');
        $response->assertOk() // status code が200であることの確認
            ->assertJsonPath('result_info', [
                'result'      => 'OK', // レスポンス"result"が"OK"であることの確認
                'result_code' => 'W00001', // レスポンス"result_code"が"W00001"であることの確認
                'message'     => '条件に一致する利用者情報が存在しません。', // レスポンス"message"が指定した内容であることの確認
            ]);
    }

    /**
     * 必須チェックエラーのテスト
     *
     * @depends testRequestAccessTokenApi
     */
    public function testErrorRequired(array $resAccessToken): void
    {
        // テスト実行
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$resAccessToken['access_token'],
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ])->getJson('/api/users');
        $response->assertJsonPath('result_info', [
            'result'      => 'NG', // レスポンス"result"が"NG"であることの確認
            'result_code' => 'E00002', // レスポンス"result_code"が"E00002"であることの確認
            'message'     => 'facility number は必須項目です。', // レスポンス"message"が指定した内容であることの確認
        ]);
    }

    /**
     * 文字数エラーのテスト
     *
     * @depends testRequestAccessTokenApi
     */
    public function testErrorNumberOfChar(array $resAccessToken): void
    {
        // テスト実行
        $response = $this->requestUsersApi($resAccessToken['access_token'], '000000000199');
        $response->assertJsonPath('result_info', [
            'result'      => 'NG', // レスポンス"result"が"NG"であることの確認
            'result_code' => 'E00003', // レスポンス"result_code"が"E00003"であることの確認
            'message'     => 'facility number は10文字で入力してください。', // レスポンス"message"が指定した内容であることの確認
        ]);
    }

    /**
     * フォーマットエラーのテスト
     *
     * @depends testRequestAccessTokenApi
     */
    public function testErrorFormat(array $resAccessToken): void
    {
        // テスト実行
        $response = $this->requestUsersApi($resAccessToken['access_token'], 'abc0000001');
        $response->assertJsonPath('result_info', [
            'result'      => 'NG', // レスポンス"result"が"NG"であることの確認
            'result_code' => 'E00004', // レスポンス"result_code"が"E00004"であることの確認
            'message'     => 'facility number を正しい形式で指定してください。', // レスポンス"message"が指定した内容であることの確認
        ]);
    }

    /**
     * データ取得エラーのテスト
     *
     * @depends testRequestAccessTokenApi
     */
    public function testErrorGetData(array $resAccessToken): void
    {
        // テスト実行
        $response = $this->requestUsersApi($resAccessToken['access_token'], 'ABC0000001');
        $response->assertJsonPath('result_info', [
            'result'      => 'NG', // レスポンス"result"が"NG"であることの確認
            'result_code' => 'E00005', // レスポンス"result_code"が"E00005"であることの確認
            'message'     => '指定した事業所が見つかりません。', // レスポンス"message"が指定した内容であることの確認
        ]);
    }

    /**
     * 認証エラーのテスト
     */
    public function testErrorCertification(): void
    {
        // テスト実行
        $response = $this->requestUsersApi('abc', '0000000001');
        $response->assertJsonPath('result_info', [
            'result'      => 'NG', // レスポンス"result"が"NG"であることの確認
            'result_code' => 'E00009', // レスポンス"result_code"が"E00009"であることの確認
            'message'     => '認証エラーが発生しました。', // レスポンス"message"が指定した内容であることの確認
        ]);
    }

    /**
     * 利用者同期情報取得API実行
     *
     * @param string $accessToken アクセストークン
     * @param string $facilityNumber 事業所番号
     * @param boolean $all 全件取得フラグ
     */
    private function requestUsersApi($accessToken, $facilityNumber, $all = false): object
    {
        // 全件取得の場合、パラメータ"all_get_flg"に1をセットしてURLに付与
        if ($all)
        {
            $url = '/api/users?facility_number='.$facilityNumber.'&all_get_flg=1';
        }
        else
        {
            $url = '/api/users?facility_number='.$facilityNumber;
        }

        // API実行
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$accessToken,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ])->getJson($url);

        return $response;
    }
}
