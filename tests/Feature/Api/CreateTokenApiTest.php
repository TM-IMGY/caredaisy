<?php

namespace Tests\Feature\Api;

use App\Models\OauthClient;
use Tests\TestCase;

/**
 * アクセストークン取得APIのフィーチャーテスト
 *
 * @group api
 */
class CreateTokenApiTest extends TestCase
{
    /**
     * テストに使用するクライアントシークレット情報
     */
    private $oauthClient;

    /**
     * リクエストパラメータ
     *
     * @var array
     */
    private $data = [];

    protected function setUp(): void
    {
        parent::setUp();

        // oauth_clientsの必要なレコードをセット
        $this->oauthClient = OauthClient::where('name', 'like', '%Password Grant Client')
            ->first();

        $this->data = [
            'grant_type' => 'password',
            'client_id' => $this->oauthClient->id,
            'client_secret' => $this->oauthClient->secret,
            'username' => 'test_authority@0000000001.care-daisy.com',
            'password' => 'password',
            'scope' => '',
        ];
    }

    /**
     * リクエストが成功しトークンが取得できることをテストする
     */
    public function testValid()
    {
        $response = $this->postJson('/oauth/token', $this->data);

        // ステータスが200か
        $response->assertOk();
        // レスポンスのフォーマットが正しいか
        $response->assertJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token',
        ]);
        // token_typeがBearerか
        $response->assertJson([
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * 不正なパスワードで認証エラーが返ることをテストする
     */
    public function testInvalidPassword()
    {
        $invalidPasswordData = $this->data;
        $invalidPasswordData['password'] = 'hoge';
        $response = $this->postJson('/oauth/token', $invalidPasswordData);

        // ステータスが400か
        $response->assertStatus(400);
        // result_info内が正しいか
        $response->assertExactJson([
            "result_info" => [
                "result" => "NG",
                "result_code" => "E00001",
                "message" => "認証に失敗しました。",
            ],
        ]);
    }

    /**
     * client_idが空でエラーが返ることをテストする
     */
    public function testClientIdEmpty()
    {
        $clientIdEmptyData = $this->data;
        unset($clientIdEmptyData['client_id']);
        $response = $this->postJson('/oauth/token', $clientIdEmptyData);

        // ステータスが400か
        $response->assertStatus(400);
        // result_info内が正しいか
        $response->assertExactJson([
            "result_info" => [
                "result" => "NG",
                "result_code" => "E00002",
                "message" => "client id は必須項目です。",
            ],
        ]);
    }

    /**
     * client_idが適当な文字列でエラーが返ることをテストする
     */
    public function testInvalidClientId()
    {
        $invalidClientIdData = $this->data;
        $invalidClientIdData['client_id'] = 'hoge';
        $response = $this->postJson('/oauth/token', $invalidClientIdData);

        // ステータスが400か
        $response->assertStatus(400);
        // result_info内が正しいか
        $response->assertExactJson([
            "result_info" => [
                "result" => "NG",
                "result_code" => "E00004",
                "message" => "client id を正しい形式で指定してください。",
            ],
        ]);
    }
}
