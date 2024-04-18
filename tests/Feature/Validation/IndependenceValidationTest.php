<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;

use App\Http\Requests\GroupHome\UserInfo\IndependenceRequest;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * 自立度のバリデーションテスト
 */
class IndependenceValidationTest extends TestCase
{
    /**
     * バリデーションが通るデータ
     */
    const VALID_DATA = [
        'data' =>  [
            'independentIndependence'       => 1,
            'dementiaIndependence'          => 1,
            'judgmentDateIndependence'      => '2021/01/01',
            'judgeIndependence'             => 1,
            'facility_user_id'              => 1,
            'saveGetIdIndependence'         => 1,
        ],
    ];

    /**
     * バリデーションが成功することをテストする
     *
     * @dataProvider validDataProvider
     * @group validation
     */
    public function testValidSuccess(array $data)
    {
        $request = new IndependenceRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->passes());
    }

    /**
     * バリデーションが失敗することをテストする
     *
     * @dataProvider validDataProviderFailure
     * @group validation
     */
    public function testValidFailure(array $dataFailure)
    {
        $request = new IndependenceRequest();
        $rules = $request->rules();
        $validator = Validator::make($dataFailure, $rules);
        $this->assertFalse($validator->passes());
    }

    /**
     * バリデーションが成功するテスト用のデータプロバイダ
     */
    public function validDataProvider(): array
    {
        $data['OK'] = self::VALID_DATA;

        $data['年月日が非0埋め'] = self::VALID_DATA;
        $data['年月日が非0埋め']['data']['judgmentDateIndependence'] = '2021/1/1';

        // 2000年4月以降のデータを確認
        $data['2000年4月以降'] = self::VALID_DATA;
        // 判断日に日付：「2000/4/1」を設定して入力チェックを行い、チェックエラーにならないことを確認する
        $data['2000年4月以降']['data']['judgmentDateIndependence'] = '2000/4/1';

        // 2100年1月以前のデータを確認
        $data['2100年1月以前'] = self::VALID_DATA;
        // 判断日に日付：「2099/12/31」を設定して入力チェックを行い、チェックエラーにならないことを確認する
        $data['2100年1月以前']['data']['judgmentDateIndependence'] = '2099/12/31';

        return $data;
    }

    /**
     * バリデーションが失敗するテスト用のデータプロバイダ
     */
    public function validDataProviderFailure(): array
    {
        // 2000年4月以前のデータを確認
        $dataFailure['2000年4月以前'] = self::VALID_DATA;
        // 判断日に日付：「2000/3/31」を設定して入力チェックを行い、チェックエラーになることを確認する
        $dataFailure['2000年4月以前']['data']['judgmentDateIndependence'] = '2000/3/31';

        // 2100年1月以降のデータを確認
        $data['2100年1月以降'] = self::VALID_DATA;
        // 判断日に日付：「2100/01/01」を設定して入力チェックを行い、チェックエラーになることを確認する
        $data['2100年1月以降']['data']['judgmentDateIndependence'] = '2100/01/01';

        return $dataFailure;
    }
}
