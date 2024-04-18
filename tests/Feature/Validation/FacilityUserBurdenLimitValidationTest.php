<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;

use App\Http\Requests\GroupHome\UserInfo\BurdenLimitRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;

/**
 * 利用者負担限度額のバリデーションテスト
 */
class FacilityUserBurdenLimitValidationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * バリデーションが通るデータ
     */
    const VALID_DATA = [
        'data' =>  [
            'facility_user_id'                         => 1,
            'food_expenses_burden_limit'               => 0,
            'living_expenses_burden_limit'             => 0,
            'start_date'                               => '2021/01/01',
            'end_date'                                 => '2022/01/01',
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
        $request = new BurdenLimitRequest();
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
        $data['年月日が非0埋め']['data']['start_date'] = '2021/1/1';
        $data['年月日が非0埋め']['data']['end_date'] = '2022/1/1';

        $data['限度額上限'] = self::VALID_DATA;
        $data['限度額上限']['data']['food_expenses_burden_limit'] = 9999;
        $data['限度額上限']['data']['living_expenses_burden_limit'] = 9999;

        return $data;
    }

    /**
     * バリデーションエラーのテスト
     *
     * @dataProvider failureValidationDataProvider
     */
    public function testFailureValidation(array $data, array $errors): void
    {
        $request = new BurdenLimitRequest();
        $request->merge($data);
        $validator = Validator::make($data, $request->rules(), $request->messages(), $request->attributes());
        $request->withValidator($validator);
        $this->assertTrue($validator->fails());

        // エラーの想定件数が1件以上の場合、エラーメッセージの内容を確認する
        $errorsCount = count($errors);
        if ($errorsCount > 0) {
            // エラー件数の確認
            $validatorErrorsMessages = $validator->errors()->messages();
            $validatorErrorsMessagesCount = count($validatorErrorsMessages);
            $this->assertEquals($errorsCount, $validatorErrorsMessagesCount);

            // エラーメッセージの内容を確認
            foreach ($errors as $index => $message) {
                $validationMessage = current($validatorErrorsMessages[$index]);
                $this->assertEquals($validationMessage, $message);
            }
        }
    }

    /**
     * データプロバイダ
     */
    public function failureValidationDataProvider(): array
    {
        // 限度額超過エラーをテスト
        $data['限度額上限超'] = self::VALID_DATA;
        $data['限度額上限超']['data']['food_expenses_burden_limit'] = 99999;
        $data['限度額上限超']['data']['living_expenses_burden_limit'] = 99999;
        $data['限度額上限超']['errors']['food_expenses_burden_limit'] = '食費（負担限度額） は 0 ～ 9999の間で入力してください。';
        $data['限度額上限超']['errors']['living_expenses_burden_limit'] = '居住費（負担限度額） は 0 ～ 9999の間で入力してください。';

        $data['適用年月_開始>終了'] = self::VALID_DATA;
        $data['適用年月_開始>終了']['data']['start_date'] = '2022/11/01';
        $data['適用年月_開始>終了']['data']['end_date'] = '2022/10/01';
        $data['適用年月_開始>終了']['errors'] = '適用終了日が適用開始日より過去の日付です。';

        // 適用開始・終了日の既存データとの重複エラーをテスト
        $data['期間重複'] = self::VALID_DATA;
        $data['期間重複']['data']['facility_user_id'] = 43;
        $data['期間重複']['data']['start_date'] = '2022/6/1';
        $data['期間重複']['data']['end_date'] = '2022/9/1';
        $data['期間重複']['errors']['DateDuplication'] = '重複している期間が登録されているため保存できません。';


        return $data;
    }
}
