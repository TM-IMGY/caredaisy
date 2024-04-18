<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Models\Facility;
use App\Models\FacilityUser;
use App\Models\FirstServicePlan;
use App\Models\OauthClient;
use App\Models\ServicePlan;
use App\User;
use Illuminate\Support\Facades\Storage;

/**
 * 介護計画書PDF取得APIのテスト
 *
 * パイプラインのテスト環境からS3への接続ができないため、該当箇所はモックしている
 * レスポンス内のPDFはダミーであることに注意
 *
 * @group api
 */
class GeneratePlanPDFApiTest extends TestCase
{
    /**
     * テストに利用するアカウント
     */
    private $testAccount;

    /**
     * 利用者
     */
    private $facilityUser;

    /**
     * 施設
     */
    private $facility;

    /**
     * テスト対象施設利用者の介護計画書
     *
     * @var Collection
     */
    private $servicePlans;

    /**
     * API実行時のパラメータ
     *
     * @var array
     */
    private $params = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->testAccount = User::find(1);

        $this->facilityUser = FacilityUser::find(1);

        $this->facility = Facility::find(1);

        $this->servicePlans = ServicePlan::where('facility_user_id', $this->facilityUser->facility_user_id)->get();

        $this->params = [
            'facility_number' => $this->facility->facility_number,
            'care_daisy_facility_user_id' => $this->facilityUser->facility_user_id,
            'service_plan_id' => $this->servicePlans[0]->id,
        ];
    }

    /**
     * アクセストークンが取得できることをテストする
     */
    public function testRequestAccessTokenApi(): array
    {
        // oauth_clients のデータを取得
        $oauthClient = OauthClient::where('name', 'like', '%Password Grant Client')
            ->select(['id', 'secret'])
            ->first();

        $data = [
            'grant_type'    => 'password',
            'client_id'     => $oauthClient->id,
            'client_secret' => $oauthClient->secret,
            'username'      => $this->testAccount->employee_number,
            'password'      => 'password',
            'scope'         => '',
        ];

        // API実行
        $response = $this->postJson('/oauth/token', $data);
        $response->assertOk() // status code が200であることの確認
            ->assertJson(['access_token' => true]); // レスポンス"access_token"が存在することの確認

        return $response->decodeResponseJson();
    }

    /**
     * facility_numberが空でエラーが返ることをテストする
     *
     * @depends testRequestAccessTokenApi
     */
    public function testEmptyFacilityNumber(array $token): void
    {
        // facility_numberを削除
        unset($this->params['facility_number']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token['access_token']])
            ->getJson(route('api.service_plan_pdf', $this->params));

        $response->assertStatus(400)
            ->assertExactJson([
            "result_info" => [
                "result" => "NG",
                "result_code" => "E00002",
                "message" => "facility number は必須項目です。",
            ],
        ]);
    }

    /**
     * 交付済みの計画書が存在しない場合にwarningが返ることをテストする
     *
     * @depends testRequestAccessTokenApi
     */
    public function testDoesntExistWarning(array $token): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token['access_token']])
            ->getJson(route('api.service_plan_pdf', $this->params));

        $response->assertOk()
            ->assertExactJson([
                "result_info" => [
                    "result" => "OK",
                    "result_code" => "W00001",
                    "message" => "条件に一致する介護計画書が存在しません。",
                ],
            ]);
    }

    /**
     * facility_numberが11桁以上でエラーが返ることをテストする
     *
     * @depends testRequestAccessTokenApi
     */
    public function testInvalidFacilityNumber(array $token): void
    {
        // facility_numberを11桁にする
        $this->params['facility_number'] = 12345678901;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token['access_token']])
            ->getJson(route('api.service_plan_pdf', $this->params));

        $response->assertStatus(400)
            ->assertExactJson([
            "result_info" => [
                "result" => "NG",
                "result_code" => "E00003",
                "message" => "facility number は10文字で入力してください。",
            ],
        ]);
    }

    /**
     * facility_numberが文字列でエラーが返ることをテストする
     *
     * @depends testRequestAccessTokenApi
     */
    public function testStringFacilityNumber(array $token): void
    {
        // facility_numberを文字列にする
        $this->params['facility_number'] = '1472301abc';

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token['access_token']])
            ->getJson(route('api.service_plan_pdf', $this->params));

        $response->assertStatus(400)
            ->assertExactJson([
            "result_info" => [
                "result" => "NG",
                "result_code" => "E00004",
                "message" => "facility number を正しい形式で指定してください。",
            ],
        ]);
    }

    /**
     * service_plan_idが21文字以上でエラーが返ることをテストする
     *
     * @depends testRequestAccessTokenApi
     */
    public function testInvalidServicePlanId(array $token): void
    {
        // facility_numberを21文字以上にする
        $this->params['service_plan_id'] = '123456789012345678901';

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token['access_token']])
            ->getJson(route('api.service_plan_pdf', $this->params));

        $response->assertStatus(400)
            ->assertExactJson([
            "result_info" => [
                "result" => "NG",
                "result_code" => "E00007",
                "message" => "service plan id は20文字以内で入力してください。",
            ],
        ]);
    }

    /**
     * 存在しないfacility_numberでエラーが返ることをテストする
     *
     * @depends testRequestAccessTokenApi
     */
    public function testFacilityDoesntExist(array $token): void
    {
        // facility_numberを存在しない値にする
        $this->params['facility_number'] = '1472301ABC';

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token['access_token']])
            ->getJson(route('api.service_plan_pdf', $this->params));

        $response->assertStatus(400)
            ->assertExactJson([
            "result_info" => [
                "result" => "NG",
                "result_code" => "E00005",
                "message" => "指定した事業所が見つかりません。",
            ],
        ]);
    }

    /**
     * 不正なアクセストークンでエラーが返ることをテストする
     */
    public function testInvalidToken(): void
    {
        // 適当なアクセストークンにする
        $response = $this->withHeaders(['Authorization' => 'Bearer abc'])
            ->getJson(route('api.service_plan_pdf', $this->params));

        $response->assertStatus(401)
            ->assertExactJson([
            "result_info" => [
                "result" => "NG",
                "result_code" => "E00009",
                "message" => "認証エラーが発生しました。",
            ],
        ]);
    }

    /**
     * 計画書IDを指定せずにリクエストが成功することをテストする
     *
     * @depends testRequestAccessTokenApi
     */
    public function testSuccessWithoutServicePlanId(array $token): void
    {
        $this->mockGettingPdfByS3();

        // service_plan_idを削除
        unset($this->params['service_plan_id']);

        // 介護計画書を交付済みにする
        $this->servicePlans[0]
            ->fill(['status' => $this->servicePlans[0]::STATUS_ISSUED])
            ->save();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token['access_token']])
            ->getJson(route('api.service_plan_pdf', $this->params));

        $response->assertOk()
            ->assertJson([
            "result_info" => [
                "result" => "OK",
                "result_code" => "",
                "message" => "",
            ],
            'latest_oldest_status' => '3',
        ]);
    }

    /**
     * 介護計画書を2件追加する
     *
     * @return void
     */
    private function addTwoServicePlans(): void
    {
        // 介護計画書を2件追加
        $servicePlan2 = $this->servicePlans[0]->replicate();
        $servicePlan2->save();
        $servicePlan3 = $this->servicePlans[0]->replicate();
        $servicePlan3->save();

        // 介護計画書1を2件追加
        $firstServicePlan = FirstServicePlan::first();
        $firstServicePlan2 = $firstServicePlan->replicate();
        $firstServicePlan2->service_plan_id = $servicePlan2->id;
        $firstServicePlan2->save();
        $firstServicePlan3 = $firstServicePlan->replicate();
        $firstServicePlan3->service_plan_id = $servicePlan3->id;
        $firstServicePlan3->save();
    }

    /**
     * ページ番号を指定してリクエストが成功することをテストする
     *
     * @depends testRequestAccessTokenApi
     */
    public function testSuccessWithPaging(array $token): void
    {
        $this->mockGettingPdfByS3();

        // 介護計画書を2件追加する
        $this->addTwoServicePlans();

        $this->servicePlans = ServicePlan::where('facility_user_id', $this->facilityUser->facility_user_id)->get();

        // パラメータ変更
        $this->params['service_plan_id'] = $this->servicePlans[2]->id;
        $this->params['paging_no'] = -1;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token['access_token']])
            ->getJson(route('api.service_plan_pdf', $this->params));

        $response->assertOk()
            ->assertJson([
            "result_info" => [
                "result" => "OK",
                "result_code" => "",
                "message" => "",
            ],
            'latest_oldest_status' => '4',
        ]);
    }

    /**
     * 最新の計画書IDでリクエストが成功することをテストする
     *
     * @depends testRequestAccessTokenApi
     */
    public function testSuccessWithLatestServicePlanId(array $token): void
    {
        $this->mockGettingPdfByS3();

        // 最新の介護計画書IDセット
        $this->params['service_plan_id'] = $this->servicePlans[2]->id;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token['access_token']])
            ->getJson(route('api.service_plan_pdf', $this->params));

        $response->assertOk()
            ->assertJson([
            "result_info" => [
                "result" => "OK",
                "result_code" => "",
                "message" => "",
            ],
            'latest_oldest_status' => '1',
        ]);
    }

    /**
     * 最古の計画書IDでリクエストが成功することをテストする
     *
     * @depends testRequestAccessTokenApi
     */
    public function testSuccessWithOldestServicePlanId(array $token): void
    {
        $this->mockGettingPdfByS3();

        // 最新の介護計画書IDセット
        $this->params['service_plan_id'] = $this->servicePlans[0]->id;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token['access_token']])
            ->getJson(route('api.service_plan_pdf', $this->params));

        $response->assertOk()
            ->assertJson([
            "result_info" => [
                "result" => "OK",
                "result_code" => "",
                "message" => "",
            ],
            'latest_oldest_status' => '2',
        ]);

        $this->restoreTestData();
    }

    /**
     * テストデータを元に戻す
     *
     * 本来はtearDownAfterClass()に記載したいが、当該メソッド内でDB接続ができないため仕方なくこちらに実装
     *
     * @return void
     */
    private function restoreTestData(): void
    {
        // 追加した介護計画書
        $addedPlans = $this->servicePlans->where('id', '<>', 1);

        // 追加した介護計画書1を削除する
        $addedFirstPlans = FirstServicePlan::whereIn('service_plan_id', $addedPlans->pluck('id'))->get();
        foreach ($addedFirstPlans as $plan) {
            $plan->delete();
        }

        // 追加した介護計画書を削除する
        foreach ($addedPlans as $plan) {
            $plan->delete();
        }

        // 交付済みにした介護計画書のステータスを戻す
        $this->servicePlans[0]
            ->fill(['status' => $this->servicePlans[0]::STATUS_SAVED])
            ->save();
    }

    /**
     * S3からPDFを取得する処理をモックする

     * モック対象はapp\Utility\S3.phpのgetRawData()内の取得処理
     *
     * @return void
     */
    private static function mockGettingPdfByS3(): void
    {
        Storage::shouldReceive('disk')
        ->andReturnSelf();

        Storage::shouldReceive('get')
        ->andReturn('dummy pdf data');
    }
}
