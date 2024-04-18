<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;

use App\Http\Requests\GroupHome\ResultInfo\StayOutManagementRequest;
use Illuminate\Support\Facades\Validator;

/**
 * 外泊日登録のバリデーションテスト
 */
class StayOutValidationTest extends TestCase
{
    /**
     * バリデーションが通るデータ
     */
    const VALID_DATE = [
        'data' =>  [
            'facility_user_id'                  => 1,
            'start_date'                        => '2021/01/01',
            'start_time'                        => '00:00',
            'meal_of_the_day_start_morning'     => true,
            'meal_of_the_day_start_lunch'       => true,
            'meal_of_the_day_start_snack'       => true,
            'meal_of_the_day_start_dinner'      => true,
            'end_date'                          => '2022/01/01',
            'end_time'                          => '00:00',
            'meal_of_the_day_end_morning'       => true,
            'meal_of_the_day_end_lunch'         => true,
            'meal_of_the_day_end_snack'         => true,
            'meal_of_the_day_end_dinner'        => true,
            'reason_for_stay_out'               => 1,
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
        $request = new StayOutManagementRequest();
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
        $data['年月日が非0埋め']['data']['end_date'] = '2022/1/1';

        return $data;
    }
}
