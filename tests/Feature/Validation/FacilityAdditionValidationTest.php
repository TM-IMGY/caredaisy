<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\GroupHome\Service\FacilityAdditionInsertCareRewardHistoryRequest;
use App\Http\Requests\GroupHome\Service\FacilityAdditionUpdateCareRewardHistoryRequest;

/**
 * 加算状況のバリデーションテスト
 */
class FacilityAdditionValidationTest extends TestCase
{
    /**
     * バリデーションが通るデータ
     */
    const VALID_DATE = [
        'data' =>  [
            'care_reward_history' => ['bail', 'required', 'array'],
            'facility_id' => 1,
            'service_type_code_id' => 1,
            'start_date' => '2021/01/01',
            'end_date' => '2022/01/01',
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
        $request = new FacilityAdditionUpdateCareRewardHistoryRequest();
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

    /**
     * 新規登録時の日付バリデーションエラーをテスト
     *
     * @dataProvider failureDataProviderForInsert
     */
    public function testFailureForInsert(array $data): void
    {
        $request = new FacilityAdditionInsertCareRewardHistoryRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);
        $request->merge($data);
        $request->withValidator($validator);
        $this->assertTrue($validator->fails());
    }

    /**
     * 新規登録時の日付バリデーションエラーテスト用データプロバイダ
     */
    public function failureDataProviderForInsert(): array
    {
        $default = [
            'data' => [
                'facility_id'          => 1,
                'service_id'           => 30,
                'service_type_code_id' => 5,
                'care_reward_history' => [
                    'baseup'                => 1,
                    'treatment_improvement' => 1,
                    'start_month'           => '2021/04/01',
                    'end_month'             => '2024/03/31',
                ],
            ]
        ];

        // 終了月に2100年1月以降が入力された場合にバリデーションエラーになることを確認
        $data['終了月に2100年1月以降を入力'] = $default;
        $data['終了月に2100年1月以降を入力']['data']['care_reward_history']['end_month'] = '2100/01/31';

        // 開始月＞終了月 が入力された場合にバリデーションエラーになることを確認
        $data['開始月＞終了月'] = $default;
        $data['開始月＞終了月']['data']['care_reward_history']['start_month'] = '2022/10/01';
        $data['開始月＞終了月']['data']['care_reward_history']['end_month'] = '2022/09/30';

        // 開始月に2021年3月以前が入力された場合にバリデーションエラーになることを確認
        $data['開始月に2021年3月以前を入力'] = $default;
        $data['開始月に2021年3月以前を入力']['data']['care_reward_history']['start_month'] = '2021/03/01';

        // 終了月に2024年4月以降が入力された場合にバリデーションエラーになることを確認
        $data['終了月に2024年4月以降を入力'] = $default;
        $data['終了月に2024年4月以降を入力']['data']['care_reward_history']['end_month'] = '2024/04/30';

        // 開始月～終了月の期間が登録済みの履歴の期間と重複している場合にバリデーションエラーになることを確認
        $data['開始月が履歴の期間内'] = $default;
        $data['開始月が履歴の期間内']['data']['service_type_code_id'] = 2;
        $data['開始月が履歴の期間内']['data']['care_reward_history']['start_month'] = '2022/07/01';
        $data['終了月が履歴の期間内'] = $default;
        $data['終了月が履歴の期間内']['data']['service_type_code_id'] = 2;
        $data['終了月が履歴の期間内']['data']['care_reward_history']['end_month'] = '2022/07/31';
        $data['新規登録データの期間が履歴の期間内'] = $default;
        $data['新規登録データの期間が履歴の期間内']['data']['service_type_code_id'] = 2;
        $data['新規登録データの期間が履歴の期間内']['data']['care_reward_history']['start_month'] = '2022/02/01';
        $data['新規登録データの期間が履歴の期間内']['data']['care_reward_history']['end_month'] = '2022/11/30';
        $data['履歴の期間が新規登録データの期間内'] = $default;
        $data['履歴の期間が新規登録データの期間内']['data']['service_type_code_id'] = 2;
        $data['履歴の期間が新規登録データの期間内']['data']['care_reward_history']['start_month'] = '2021/12/01';
        $data['履歴の期間が新規登録データの期間内']['data']['care_reward_history']['end_month'] = '2023/01/31';

        return $data;
    }

    /**
     * 更新時の日付バリデーションエラーをテスト
     *
     * @dataProvider failureDataProviderForUpdate
     */
    public function testFailureForUpdate(array $data): void
    {
        $request = new FacilityAdditionUpdateCareRewardHistoryRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);
        $request->merge($data);
        $request->withValidator($validator);
        $this->assertTrue($validator->fails());
    }

    /**
     * 更新時の日付バリデーションエラーテスト用データプロバイダ
     */
    public function failureDataProviderForUpdate(): array
    {
        $default = [
            'data' => [
                'facility_id'          => 1,
                'service_type_code_id' => 2,
                'start_date'           => '2022/01/01',
                'end_date'             => '2022/12/31',
                'care_reward_history' => [
                    'care_reward_histories_id' => 2,
                    'baseup'                   => 1,
                    'treatment_improvement'    => 1,
                    'start_month'              => '2021/04/01',
                    'end_month'                => '2024/03/31',
                ],
            ]
        ];

        // 終了月に2100年1月以降が入力された場合にバリデーションエラーになることを確認
        $data['終了月に2100年1月以降を入力'] = $default;
        $data['終了月に2100年1月以降を入力']['data']['care_reward_history']['end_month'] = '2100/01/31';

        // 開始月＞終了月 が入力された場合にバリデーションエラーになることを確認
        $data['開始月＞終了月'] = $default;
        $data['開始月＞終了月']['data']['care_reward_history']['start_month'] = '2022/10/01';
        $data['開始月＞終了月']['data']['care_reward_history']['end_month'] = '2022/09/30';

        // 開始月に2021年3月以前が入力された場合にバリデーションエラーになることを確認
        $data['開始月に2021年3月以前を入力'] = $default;
        $data['開始月に2021年3月以前を入力']['data']['care_reward_history']['start_month'] = '2021/03/01';

        // 終了月に2024年4月以降が入力された場合にバリデーションエラーになることを確認
        $data['終了月に2024年4月以降を入力'] = $default;
        $data['終了月に2024年4月以降を入力']['data']['care_reward_history']['end_month'] = '2024/04/30';

        // 開始月～終了月の期間が登録済みの履歴の期間と重複している場合にバリデーションエラーになることを確認
        $data['開始月が履歴の期間内'] = $default;
        $data['開始月が履歴の期間内']['data']['service_type_code_id'] = 1;
        $data['開始月が履歴の期間内']['data']['care_reward_history']['care_reward_histories_id'] = 1;
        $data['開始月が履歴の期間内']['data']['care_reward_history']['start_month'] = '2022/07/01';
        $data['終了月が履歴の期間内'] = $default;
        $data['終了月が履歴の期間内']['data']['service_type_code_id'] = 1;
        $data['終了月が履歴の期間内']['data']['care_reward_history']['care_reward_histories_id'] = 1;
        $data['終了月が履歴の期間内']['data']['care_reward_history']['end_month'] = '2022/07/31';
        $data['新規登録データの期間が履歴の期間内'] = $default;
        $data['新規登録データの期間が履歴の期間内']['data']['service_type_code_id'] = 1;
        $data['新規登録データの期間が履歴の期間内']['data']['care_reward_history']['care_reward_histories_id'] = 1;
        $data['新規登録データの期間が履歴の期間内']['data']['care_reward_history']['start_month'] = '2022/02/01';
        $data['新規登録データの期間が履歴の期間内']['data']['care_reward_history']['end_month'] = '2022/11/30';
        $data['履歴の期間が新規登録データの期間内'] = $default;
        $data['履歴の期間が新規登録データの期間内']['data']['service_type_code_id'] = 1;
        $data['履歴の期間が新規登録データの期間内']['data']['care_reward_history']['care_reward_histories_id'] = 1;
        $data['履歴の期間が新規登録データの期間内']['data']['care_reward_history']['start_month'] = '2021/12/01';
        $data['履歴の期間が新規登録データの期間内']['data']['care_reward_history']['end_month'] = '2023/01/31';

        return $data;
    }
}
