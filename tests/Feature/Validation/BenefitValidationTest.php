<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;

use App\Http\Requests\GroupHome\UserInfo\BenefitInputRequest;
use Illuminate\Support\Facades\Validator;

/**
 * 給付率のバリデーションテスト
 */
class BenefitValidationTest extends TestCase
{
    /**
     * バリデーションが通るデータ
     */
    const VALID_DATA = [
        'data' =>  [
            'facility_user_id'         => 1,
            'benefit_type'             => 1,
            'benefit_rate'             => 1,
            'effective_start_date'     => '2021/01/01',
            'expiry_date'              => '2022/01/01',
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
        $request = new BenefitInputRequest();
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
        $data['年月日が非0埋め']['data']['effective_start_date'] = '2021/1/1';
        $data['年月日が非0埋め']['data']['expiry_date'] = '2022/1/1';

        return $data;
    }
}
