<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\GroupHome\CarePlanInfo\ServicePlan1RegisterRequest;

class ServicePlan1Test extends TestCase
{

    const VALID_DATE = [
        'data' => [
            'facility_user_id'        => 1,
            'plan_start_period'       => '2022-10-01', // 作成日
            'plan_end_period'         => '介護花子',
            'status'                  => 1,
            'certification_status'    => 2,
            'recognition_date'        => '',
            'care_period_start'       => '2022-01-01', // 認定情報有効開始日
            'care_period_end'         => '2022-12-31', // 認定情報有効終了日
            'care_level_name'         => '要介護３',
            'place'                   => '',
            'remarks'                 => '',
            'independence_level'      => '',
            'dementia_level'          => '',
            'plan_division'           => 1,
            'title1'                  => '',
            'content1'                => '',
            'title2'                  => '',
            'content2'                => '',
            'title3'                  => '',
            'content3'                => '',
            "fixed_date"              => "", // 確定日
            "delivery_date"           => "", // 交付日時 ex.2022-05-24T06=>00
            'living_alone'            => '',
            'handicapped'             => '',
            'other'                   => '',
            'other_reason'            => '',
            'start_date'              => '2022-01-01', // ケアプラン期間開始日
            'end_date'                => '2022-12-31', // ケアプラン期間終了日
            'first_plan_start_period' => '2000/04/01',
        ]
    ];

    /**
     * 日付バリデーションの正常系テスト
     *
     * @dataProvider successDateValidationDataProvider
     */
    public function testSuccessDateValidation(array $data): void
    {
        $request = new ServicePlan1RegisterRequest();
        $request->merge($data);
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function successDateValidationDataProvider(): array
    {
        // ケアプラン期間の開始日に2000年4月1日が入力された場合にバリデーションが成功することを確認
        $data['ケアプラン期間の開始日に2000年4月1日を入力'] = self::VALID_DATE;
        $data['ケアプラン期間の開始日に2000年4月1日を入力']['data']['start_date'] = '2000-04-01';

        // ケアプラン期間の終了日に2099年12月31日が入力された場合にバリデーションが成功することを確認
        $data['ケアプラン期間の終了日に2099年12月31日を入力'] = self::VALID_DATE;
        $data['ケアプラン期間の終了日に2099年12月31日を入力']['data']['end_date'] = '2099-12-31';

        // ケアプラン期間の開始日・終了日ともに制限期間内の日付が入力された場合にバリデーションが成功することを確認
        $data['ケアプラン期間の開始日・終了日に制限期間内の日付を入力'] = self::VALID_DATE;
        $data['ケアプラン期間の開始日・終了日に制限期間内の日付を入力']['data']['start_date'] = '2022-10-01';
        $data['ケアプラン期間の開始日・終了日に制限期間内の日付を入力']['data']['end_date'] = '2022-10-31';

        // ケアプラン期間の開始日＜終了日 が入力された場合にバリデーションが成功することを確認
        $data['開始日＜終了日'] = self::VALID_DATE;
        $data['開始日＜終了日']['data']['start_date'] = '2022-10-01';
        $data['開始日＜終了日']['data']['end_date'] = '2022-10-02';

        // 作成日に2000年4月1日が入力された場合にバリデーションが成功することを確認
        $data['作成日に2000年4月1日を入力'] = self::VALID_DATE;
        $data['作成日に2000年4月1日を入力']['data']['plan_start_period'] = '2000-04-01';

        // 作成日に2099年12月31日が入力された場合にバリデーションが成功することを確認
        $data['作成日に2099年12月31日を入力'] = self::VALID_DATE;
        $data['作成日に2099年12月31日を入力']['data']['plan_start_period'] = '2099-12-31';

        // 初回施設サービス計画作成日に2000年4月1日が入力された場合にバリデーションが成功することを確認
        $data['初回施設サービス計画作成に2000年4月1日を入力'] = self::VALID_DATE;
        $data['初回施設サービス計画作成に2000年4月1日を入力']['data']['first_plan_start_period'] = '2000-04-01';

        // 初回施設サービス計画作成日に2099年12月31日が入力された場合にバリデーションが成功することを確認
        $data['初回施設サービス計画作成に2099年12月31日を入力'] = self::VALID_DATE;
        $data['初回施設サービス計画作成に2099年12月31日を入力']['data']['first_plan_start_period'] = '2099-12-31';

        return $data;
    }

    /**
     * 日付バリデーションエラーのテスト
     *
     * @dataProvider failureDateValidationDataProvider
     */
    public function testFailureDateValidation(array $data, array $errors): void
    {
        $request = new ServicePlan1RegisterRequest();
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
    public function failureDateValidationDataProvider(): array
    {
        // ケアプラン期間の開始日に2000年3月31日以前が入力された場合にバリデーションエラーになることを確認
        $data['ケアプラン期間開始日に2000年3月31日以前を入力'] = self::VALID_DATE;
        $data['ケアプラン期間開始日に2000年3月31日以前を入力']['data']['start_date'] = '2000-03-01';
        $data['ケアプラン期間開始日に2000年3月31日以前を入力']['errors']['start_date'] = 'ケアプラン期間開始日 は2000年4月以降の年月を入力してください。';

        // ケアプラン期間の終了日に2100年1月1日以降が入力された場合にバリデーションエラーになることを確認
        $data['ケアプラン期間終了日に2100年1月1日以降を入力'] = self::VALID_DATE;
        $data['ケアプラン期間終了日に2100年1月1日以降を入力']['data']['end_date'] = '2100-01-31';
        $data['ケアプラン期間終了日に2100年1月1日以降を入力']['errors']['end_date'] = 'ケアプラン期間終了日 は2099年12月以前の年月を入力してください。';
        $data['ケアプラン期間終了日に2100年1月1日以降を入力']['errors']['start_date'] = 'ケアプラン期間は認定情報の有効期間内で入力してください。';

        // ケアプラン期間の開始日・終了日ともに2000年3月31日以前が入力された場合にバリデーションエラーになることを確認
        $data['ケアプラン期間開始日・終了日に2000年3月31日以前を入力'] = self::VALID_DATE;
        $data['ケアプラン期間開始日・終了日に2000年3月31日以前を入力']['data']['start_date'] = '2000-03-01';
        $data['ケアプラン期間開始日・終了日に2000年3月31日以前を入力']['data']['end_date'] = '2000-03-31';
        $data['ケアプラン期間開始日・終了日に2000年3月31日以前を入力']['errors']['start_date'] = 'ケアプラン期間開始日 は2000年4月以降の年月を入力してください。';
        $data['ケアプラン期間開始日・終了日に2000年3月31日以前を入力']['errors']['end_date'] = 'ケアプラン期間終了日 は2000年4月以降の年月を入力してください。';

        // ケアプラン期間の開始日・終了日ともに2100年1月1日以降が入力された場合にバリデーションエラーになることを確認
        $data['ケアプラン期間開始日・終了日に2100年1月1日以降を入力'] = self::VALID_DATE;
        $data['ケアプラン期間開始日・終了日に2100年1月1日以降を入力']['data']['start_date'] = '2100-01-01';
        $data['ケアプラン期間開始日・終了日に2100年1月1日以降を入力']['data']['end_date'] = '2100-01-31';
        $data['ケアプラン期間開始日・終了日に2100年1月1日以降を入力']['errors']['start_date'] = 'ケアプラン期間開始日 は2099年12月以前の年月を入力してください。';
        $data['ケアプラン期間開始日・終了日に2100年1月1日以降を入力']['errors']['end_date'] = 'ケアプラン期間終了日 は2099年12月以前の年月を入力してください。';

        // ケアプラン期間の開始日＞終了日 が入力された場合にバリデーションエラーになることを確認
        $data['ケアプラン期間が開始日＞終了日'] = self::VALID_DATE;
        $data['ケアプラン期間が開始日＞終了日']['data']['start_date'] = '2022-10-01';
        $data['ケアプラン期間が開始日＞終了日']['data']['end_date'] = '2022-05-31';
        $data['ケアプラン期間が開始日＞終了日']['errors']['start_date'] = 'ケアプラン期間開始日とケアプラン期間終了日の関係性に誤りがあるので確認してください。';

        // 作成日に2000年3月31日以前が入力された場合にバリデーションエラーになることを確認
        $data['作成日に2000年3月31日以前を入力'] = self::VALID_DATE;
        $data['作成日に2000年3月31日以前を入力']['data']['plan_start_period'] = '2000-03-01';
        $data['作成日に2000年3月31日以前を入力']['errors']['plan_start_period'] = '作成日 は2000年4月以降の年月を入力してください。';

        // 作成日に2100年1月1日以降が入力された場合にバリデーションエラーになることを確認
        $data['作成日に2100年1月1日以降を入力'] = self::VALID_DATE;
        $data['作成日に2100年1月1日以降を入力']['data']['plan_start_period'] = '2100-01-01';
        $data['作成日に2100年1月1日以降を入力']['errors']['plan_start_period'] = '作成日 は2099年12月以前の年月を入力してください。';

        // 認定情報有効期間外になるようにケアプラン期間を設定
        $data['ケアプラン期間が認定情報有効期間外'] = self::VALID_DATE;
        $data['ケアプラン期間が認定情報有効期間外']['data']['care_period_start'] = '2022-02-01';
        $data['ケアプラン期間が認定情報有効期間外']['data']['care_period_end'] = '2022-12-31';
        $data['ケアプラン期間が認定情報有効期間外']['errors']['start_date'] = 'ケアプラン期間は認定情報の有効期間内で入力してください。';

        // 認定情報有効期間の開始日＞終了日が入力された場合にバリデーションエラーになることを確認
        $data['認定情報有効期間が開始日＞終了日'] = self::VALID_DATE;
        $data['認定情報有効期間が開始日＞終了日']['data']['care_period_start'] = '2022-12-31';
        $data['認定情報有効期間が開始日＞終了日']['data']['care_period_end'] = '2022-01-01';
        $data['認定情報有効期間が開始日＞終了日']['errors']['care_period_end'] = '認定情報の有効開始日と有効終了日の関係性に誤りがあるので確認してください';

        // 初回施設サービス計画作成日に2000年3月31日以前が入力された場合にバリデーションエラーになることを確認
        $data['初回施設サービス計画作成日に2000年3月31日以前を入力'] = self::VALID_DATE;
        $data['初回施設サービス計画作成日に2000年3月31日以前を入力']['data']['first_plan_start_period'] = '2000-03-31';
        $data['初回施設サービス計画作成日に2000年3月31日以前を入力']['errors']['first_plan_start_period'] = '初回施設サービス計画作成日 は2000年4月以降の年月を入力してください。';

        // 初回施設サービス計画作成日に2100年1月1日以降が入力された場合にバリデーションエラーになることを確認
        $data['初回施設サービス計画作成日に2100年1月1日以降を入力'] = self::VALID_DATE;
        $data['初回施設サービス計画作成日に2100年1月1日以降を入力']['data']['first_plan_start_period'] = '2100-01-01';
        $data['初回施設サービス計画作成日に2100年1月1日以降を入力']['errors']['first_plan_start_period'] = '初回施設サービス計画作成日 は2099年12月以前の年月を入力してください。';

        // 必須チェック
        $data['初回施設サービス計画作成日が空'] = self::VALID_DATE;
        $data['初回施設サービス計画作成日が空']['data']['first_plan_start_period'] = '';
        $data['初回施設サービス計画作成日が空']['errors']['first_plan_start_period'] = '初回施設サービス計画作成日 は必須項目です。';

        // 日付チェック
        $data['初回施設サービス計画作成日に文字を入力'] = self::VALID_DATE;
        $data['初回施設サービス計画作成日に文字を入力']['data']['first_plan_start_period'] = 'TEST';
        $data['初回施設サービス計画作成日に文字を入力']['errors']['first_plan_start_period'] = '日付を入力してください';

        return $data;
    }
}
