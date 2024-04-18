<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;

use App\Http\Requests\GroupHome\UserInfo\ServiceRequest;
use Illuminate\Support\Facades\Validator;

/**
 * サービスのバリデーションテスト
 */
class ServiceValidationTest extends TestCase
{
    /**
     * バリデーションが通るデータ
     */
    const VALID_DATA = [
        'data' =>  [
            'facility_id'                   => 1,
            'facility_user_id'              => 1,
            'serviceTypeCodeId'             => 1,
            'usageSituation'                => 1,
            'useStart'                      => '2021/01/01',
            'useEnd'                        => '2022/01/01',
        ],
    ];

    /**
     * バリデーションが成功することをテストする
     *
     * @test
     * @dataProvider validDataProvider
     * @group validation
     */
    public function validationSuccess(array $data)
    {
        $request = new ServiceRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->passes());
    }

    /**
     * バリデーションが成功するテスト用のデータプロバイダ
     */
    public function validDataProvider(): array
    {
        $data['OK'] = self::VALID_DATA;

        $data['年月日が非0埋め'] = self::VALID_DATA;
        $data['年月日が非0埋め']['data']['useStart'] = '2021/1/1';
        $data['年月日が非0埋め']['data']['useENd'] = '2022/1/1';

        return $data;
    }
}
