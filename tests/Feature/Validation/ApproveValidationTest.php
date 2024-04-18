<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;

use App\Http\Requests\GroupHome\UserInfo\ApprovalRequest;
use Illuminate\Support\Facades\Validator;

/**
 * 認定情報のバリデーションテスト
 */
class ApproveValidationTest extends TestCase
{
    /**
     * バリデーションが通るデータ
     */
    const VALID_DATA = [
        'data' =>  [
            'facility_user_id'                   => 1,
            'careLevel'                          => 1,
            'certificationStatus'                => 1,
            'recognitionDate'                    => '2022/01/01',
            'startDate'                          => '2022/01/01',
            'endDate'                            => '2022/02/01',
            'date_confirmation_insurance_card'   => '2022/01/01',
            'date_qualification'                 => '2022/01/01',
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
        $request = new ApprovalRequest();
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
        $data['年月日が非0埋め']['data']['recognitionDate'] = '2022/1/1';
        $data['年月日が非0埋め']['data']['startDate'] = '2021/1/1';
        $data['年月日が非0埋め']['data']['endDate'] = '2022/2/1';
        $data['年月日が非0埋め']['data']['date_confirmation_insurance_card'] = '2022/1/1';
        $data['年月日が非0埋め']['data']['date_qualification'] = '2022/1/1';

        return $data;
    }
}
