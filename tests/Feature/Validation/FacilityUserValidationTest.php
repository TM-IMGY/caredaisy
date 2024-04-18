<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;

use App\Http\Requests\GroupHome\Service\FacilityUserFormRequest;
use Illuminate\Support\Facades\Validator;

/**
 * 利用者情報基礎情報のバリデーションテスト
 */
class FacilityUserValidationTest extends TestCase
{
    /**
     * バリデーションが通るデータ
     */
    const VALID_DATE = [
        'data' =>  [
            'facility_id' => 1,
            'last_name' => 'めい',
            'first_name' => 'せい',
            'last_name_kana' => 'カナメイ',
            'first_name_kana' => 'カナセイ',
            'gender' => 1,
            'birthday' => '2021/01/01',
            'insured_no' => 1234567890,
            'insurer_no' => 123456,
            'start_date' => '2021/01/01',
            'before_in_status_id' => 1,
            'end_date' => '2022/01/01',
            'after_out_status_id' => 1,
        ],
    ];

    /**
     * バリデーションが成功することをテストする
     *
     * @dataProvider validDataProvider
     * @group validation
     */
    public function testValid(array $data)
    {
        $request = new FacilityUserFormRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->passes());
    }

    /**
     * バリデーションが通るテスト用のデータプロバイダ
     */
    public function validDataProvider(): array
    {
        $data['OK'] = self::VALID_DATE;

        $data['年月日が非0埋め'] = self::VALID_DATE;
        $data['年月日が非0埋め']['data']['start_date'] = '2021/1/1';
        $data['年月日が非0埋め']['data']['start_date'] = '2022/1/1';

        return $data;
    }
}
