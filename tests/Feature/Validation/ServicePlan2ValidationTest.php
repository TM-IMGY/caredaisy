<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;

use App\Http\Requests\GroupHome\CarePlanInfo\ServicePlan2RegisterRequest;
use Illuminate\Support\Facades\Validator;

/**
 * 介護計画書2のバリデーションテスト
 */
class ServicePlan2ValidationTest extends TestCase
{
    /**
     * バリデーションが通るデータ
     */
    const VALID_DATA = [
        'data' =>  [
            'service_plan_id'       => 1,
            'care_plan_period_start'=> '2000-01-01',
            'care_plan_period_end'  => '2100-12-31',
            'service_plan2'         => [
                'second_service_plan_id'=> 1,
                'need_list'             => [
                    [
                        'second_service_plan_id' => 1,
                        'service_plan_need_id'   => 94,
                        'needs'                  => 'テスト',
                        'task_start'             => null,
                        'task_end'               => null,
                        'sort'                   => 1,
                        'long_plan_list'         => [
                            [
                                'service_plan_need_id' => 94,
                                'service_long_plan_id' => 94,
                                'goal'                 => 'テスト',
                                'task_start'           => '2022-10-01',
                                'task_end'             => '2022-10-31',
                                'sort'                 => 1,
                                'short_plan_list'      => [
                                    [
                                        'service_long_plan_id'  => 94,
                                        'service_short_plan_id' => 134,
                                        'goal'                  => 'テスト',
                                        'task_start'            => '2022-10-01',
                                        'task_end'              => '2022-10-31',
                                        'sort'                  => 1,
                                        'support_list'          => [
                                            [
                                                'service_short_plan_id'   => 134,
                                                'service_plan_support_id' => 134,
                                                'task_start'              => '2022-10-01',
                                                'task_end'                => '2022-10-31',
                                                'service'                 => null,
                                                'staff'                   => null,
                                                'frequency'               => null,
                                                'sort'                    => 1
                                            ],
                                        ]
                                    ],
                                ]
                            ],
                        ]
                    ],
                ]
            ]
        ],
        'errors' => []
    ];

    /**
     * バリデーションが成功することをテストする
     *
     * @dataProvider validDataProvider
     * @group validation
     */
    public function testValidSuccess(array $data)
    {
        // リクエストを作成
        $request = new ServicePlan2RegisterRequest();
        // リクエストにデータを設定
        $request->merge($data);
        // rulesに設定した制約でvalidationを実行
        $validator = Validator::make(
            $request->validationData(),
            $request->rules(),
            $request->messages(),
            $request->attributes()
        );
        // withValidatorに記述したvalidationを実行
        $request->withValidator($validator);
        // validationでfailureが発生しないことを確認する
        $this->assertTrue($validator->passes());
    }

    /**
     * バリデーションが失敗することをテストする
     *
     * @dataProvider validDataProviderFailure
     * @group validation
     */
    public function testValidFailure(array $dataFailure, array $errors)
    {
        // リクエストを作成
        $request = new ServicePlan2RegisterRequest();
        // リクエストにデータを設定
        $request->merge($dataFailure);
        // rulesに設定した制約でvalidationを実行
        $validator = Validator::make(
            $request->validationData(),
            $request->rules(),
            $request->messages(),
            $request->attributes()
        );
        // withValidatorに記述したvalidationを実行
        $request->withValidator($validator);
        // validationでfailureが発生することを確認する
        $this->assertFalse($validator->passes());

        // エラーの件数が想定と一致することを確認
        $this->assertEquals(count($errors), count($validator->errors()->messages()));

        // エラーの想定件数が1件以上ある場合、エラーメッセージのチェックを行う
        if (count($errors) > 0 && count($errors) === count($validator->errors()->messages())) {
            $errorCnt = 0;
            foreach ($validator->errors()->messages() as $messageData) {
                foreach($messageData as $key=>$message) {
                    // エラー内容が一致することを確認する
                    $this->assertEquals($errors[$errorCnt], $message);
                }
                $errorCnt++;
            }
        }
    }

    /**
     * バリデーションが成功するテスト用のデータプロバイダ
     */
    public function validDataProvider(): array
    {
        // ベースデータの確認
        $data['OK'] = self::VALID_DATA;

        // 援助目標 長期の期間の検証
        // 援助目標 長期の期間について開始日が終了日より過去日の場合、エラーにならないことを確認
        $data['長期_期間From<期間To'] = self::VALID_DATA;
        $data['長期_期間From<期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2022/11/01';
        $data['長期_期間From<期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2022/11/02';

        // 援助目標 長期の期間について開始日と終了日が同日の場合、エラーにならないことを確認
        $data['長期_期間From=期間To'] = self::VALID_DATA;
        $data['長期_期間From=期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2022/11/01';
        $data['長期_期間From=期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2022/11/01';

        // 援助目標 長期の期間の開始日が2000年4月以降の場合、エラーにならないことを確認
        $data['長期_期間From_2000年4月以降'] = self::VALID_DATA;
        $data['長期_期間From_2000年4月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2000/04/01';

        // 援助目標 長期の期間の終了日が2000年4月以降の場合、エラーにならないことを確認
        $data['長期_期間To_2000年4月以降'] = self::VALID_DATA;
        $data['長期_期間To_2000年4月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '';
        $data['長期_期間To_2000年4月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2000/04/01';

        // 援助目標 長期の期間の開始日が2100年1月以前の場合、エラーにならないことを確認
        $data['長期_期間From_2100年1月以前'] = self::VALID_DATA;
        $data['長期_期間From_2100年1月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2099/12/31';
        $data['長期_期間From_2100年1月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '';

        // 援助目標 長期の期間の終了日が2100年1月以前の場合、エラーにならないことを確認
        $data['長期_期間To_2100年1月以前'] = self::VALID_DATA;
        $data['長期_期間To_2100年1月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2099/12/31';

        // 援助目標 長期の期間の開始日がケアプラン期間の開始日と同日の場合、エラーにならないことを確認
        $data['長期_期間From=ケアプラン期間From'] = self::VALID_DATA;
        $data['長期_期間From=ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2022/09/01';
        $data['長期_期間From=ケアプラン期間From']['data']['care_plan_period_start'] = '2022/09/01';

        // 援助目標 長期の期間の開始日がケアプラン期間の開始日より未来日の場合、エラーにならないことを確認
        $data['長期_期間From>ケアプラン期間From'] = self::VALID_DATA;
        $data['長期_期間From>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2022/09/01';
        $data['長期_期間From>ケアプラン期間From']['data']['care_plan_period_start'] = '2022/08/31';

        // 援助目標 長期の期間の開始日がケアプラン期間の終了日より過去日の場合、エラーにならないことを確認
        $data['長期_期間From<ケアプラン期間To'] = self::VALID_DATA;
        $data['長期_期間From<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2022/11/30';
        $data['長期_期間From<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '';
        $data['長期_期間From<ケアプラン期間To']['data']['care_plan_period_end'] = '2022/12/01';

        // 援助目標 長期の期間の開始日がケアプラン期間の終了日と同日の場合、エラーにならないことを確認
        $data['長期_期間From=ケアプラン期間To'] = self::VALID_DATA;
        $data['長期_期間From=ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2022/11/30';
        $data['長期_期間From=ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '';
        $data['長期_期間From=ケアプラン期間To']['data']['care_plan_period_end'] = '2022/11/30';

        // 援助目標 長期の期間の終了日がケアプラン期間の開始日と同日の場合、エラーにならないことを確認
        $data['長期_期間To=ケアプラン期間From'] = self::VALID_DATA;
        $data['長期_期間To=ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '';
        $data['長期_期間To=ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2022/09/01';
        $data['長期_期間To=ケアプラン期間From']['data']['care_plan_period_start'] = '2022/09/01';

        // 援助目標 長期の期間の終了日がケアプラン期間の開始日より未来日の場合、エラーにならないことを確認
        $data['長期_期間To>ケアプラン期間From'] = self::VALID_DATA;
        $data['長期_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '';
        $data['長期_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2022/09/01';
        $data['長期_期間To>ケアプラン期間From']['data']['care_plan_period_start'] = '2022/08/31';

        // 援助目標 長期の期間の終了日がケアプラン期間の終了日より過去日の場合、エラーにならないことを確認
        $data['長期_期間To<ケアプラン期間To'] = self::VALID_DATA;
        $data['長期_期間To<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2022/11/01';
        $data['長期_期間To<ケアプラン期間To']['data']['care_plan_period_end'] = '2022/11/02';

        // 援助目標 長期の期間の終了日がケアプラン期間の終了日と同日の場合、エラーにならないことを確認
        $data['長期_期間To=ケアプラン期間To'] = self::VALID_DATA;
        $data['長期_期間To=ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2022/11/01';
        $data['長期_期間To=ケアプラン期間To']['data']['care_plan_period_end'] = '2022/11/01';

        // 援助目標 短期の期間の検証
        // 援助目標 短期の期間について開始日が終了日より過去日の場合、エラーにならないことを確認
        $data['短期_期間From<期間To'] = self::VALID_DATA;
        $data['短期_期間From<期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '2022/11/01';
        $data['短期_期間From<期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2022/11/02';

        // 援助目標 短期の期間について開始日と終了日が同日の場合、エラーにならないことを確認
        $data['短期_期間From=期間To'] = self::VALID_DATA;
        $data['短期_期間From=期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '2022/11/01';
        $data['短期_期間From=期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2022/11/01';

        // 援助目標 短期の期間の開始日が2000年4月以降の場合、エラーにならないことを確認
        $data['短期_期間From_2000年4月以降'] = self::VALID_DATA;
        $data['短期_期間From_2000年4月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '2000/04/01';

        // 援助目標 短期の期間の終了日が2000年4月以降の場合、エラーにならないことを確認
        $data['短期_期間To_2000年4月以降'] = self::VALID_DATA;
        $data['短期_期間To_2000年4月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '';
        $data['短期_期間To_2000年4月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2000/04/01';

        // 援助目標 短期の期間の開始日が2100年1月以前の場合、エラーにならないことを確認
        $data['短期_期間From_2100年1月以前'] = self::VALID_DATA;
        $data['短期_期間From_2100年1月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '2099/12/31';
        $data['短期_期間From_2100年1月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '';

        // 援助目標 短期の期間の終了日が2100年1月以前の場合、エラーにならないことを確認
        $data['短期_期間To_2100年1月以前'] = self::VALID_DATA;
        $data['短期_期間To_2100年1月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2099/12/31';

        // 援助目標 短期の期間の終了日がケアプラン期間の開始日と同日の場合、エラーにならないことを確認
        $data['短期_期間To=ケアプラン期間From'] = self::VALID_DATA;
        $data['短期_期間To=ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '';
        $data['短期_期間To=ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2022/09/01';
        $data['短期_期間To=ケアプラン期間From']['data']['care_plan_period_start'] = '2022/09/01';

        // 援助目標 短期の期間の終了日がケアプラン期間の開始日より未来日の場合、エラーにならないことを確認
        $data['短期_期間To>ケアプラン期間From'] = self::VALID_DATA;
        $data['短期_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '';
        $data['短期_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2022/09/01';
        $data['短期_期間To>ケアプラン期間From']['data']['care_plan_period_start'] = '2022/08/31';

        // 援助目標 短期の期間の終了日がケアプラン期間の終了日より過去日の場合、エラーにならないことを確認
        $data['短期_期間To<ケアプラン期間To'] = self::VALID_DATA;
        $data['短期_期間To<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2022/11/01';
        $data['短期_期間To<ケアプラン期間To']['data']['care_plan_period_end'] = '2022/11/02';

        // 援助目標 短期の期間の終了日がケアプラン期間の終了日と同日の場合、エラーにならないことを確認
        $data['短期_期間To=ケアプラン期間To'] = self::VALID_DATA;
        $data['短期_期間To=ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2022/11/01';
        $data['短期_期間To=ケアプラン期間To']['data']['care_plan_period_end'] = '2022/11/01';

        // 援助内容の期間の検証
        // 援助内容の期間について開始日が終了日より過去日の場合、エラーにならないことを確認
        $data['援助内容_期間From<期間To'] = self::VALID_DATA;
        $data['援助内容_期間From<期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '2022/11/01';
        $data['援助内容_期間From<期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2022/11/02';

        // 援助内容の期間について開始日と終了日が同日の場合、エラーにならないことを確認
        $data['援助内容_期間From=期間To'] = self::VALID_DATA;
        $data['援助内容_期間From=期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '2022/11/01';
        $data['援助内容_期間From=期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2022/11/01';

        // 援助内容の期間の開始日が2000年4月以降の場合、エラーにならないことを確認
        $data['援助内容_期間From_2000年4月以降'] = self::VALID_DATA;
        $data['援助内容_期間From_2000年4月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '2000/04/01';

        // 援助内容の期間の終了日が2000年4月以降の場合、エラーにならないことを確認
        $data['援助内容_期間To_2000年4月以降'] = self::VALID_DATA;
        $data['援助内容_期間To_2000年4月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '';
        $data['援助内容_期間To_2000年4月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2000/04/01';

        // 援助内容の期間の開始日が2100年1月以前の場合、エラーにならないことを確認
        $data['援助内容_期間From_2100年1月以前'] = self::VALID_DATA;
        $data['援助内容_期間From_2100年1月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '2099/12/31';
        $data['援助内容_期間From_2100年1月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '';

        // 援助内容の期間の終了日が2100年1月以前の場合、エラーにならないことを確認
        $data['援助内容_期間To_2100年1月以前'] = self::VALID_DATA;
        $data['援助内容_期間To_2100年1月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2099/12/31';

        // 援助内容の期間の終了日がケアプラン期間の開始日と同日の場合、エラーにならないことを確認
        $data['援助内容_期間To=ケアプラン期間From'] = self::VALID_DATA;
        $data['援助内容_期間To=ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '';
        $data['援助内容_期間To=ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2022/09/01';
        $data['援助内容_期間To=ケアプラン期間From']['data']['care_plan_period_start'] = '2022/09/01';

        // 援助内容の期間の終了日がケアプラン期間の開始日より未来日の場合、エラーにならないことを確認
        $data['援助内容_期間To>ケアプラン期間From'] = self::VALID_DATA;
        $data['援助内容_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '';
        $data['援助内容_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2022/09/01';
        $data['援助内容_期間To>ケアプラン期間From']['data']['care_plan_period_start'] = '2022/08/31';

        // 援助内容の期間の終了日がケアプラン期間の終了日より過去日の場合、エラーにならないことを確認
        $data['援助内容_期間To<ケアプラン期間To'] = self::VALID_DATA;
        $data['援助内容_期間To<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2022/11/01';
        $data['援助内容_期間To<ケアプラン期間To']['data']['care_plan_period_end'] = '2022/11/02';

        // 援助内容の期間の終了日がケアプラン期間の終了日と同日の場合、エラーにならないことを確認
        $data['援助内容_期間To=ケアプラン期間To'] = self::VALID_DATA;
        $data['援助内容_期間To=ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2022/11/01';
        $data['援助内容_期間To=ケアプラン期間To']['data']['care_plan_period_end'] = '2022/11/01';

        return $data;
    }

    /**
     * バリデーションが失敗するテスト用のデータプロバイダ
     */
    public function validDataProviderFailure(): array
    {
        // 援助目標 長期の期間の検証
        // 援助目標 長期の期間について開始日が終了日より未来日の場合、エラーになることを確認
        $dataFailure['長期_期間From>期間To'] = self::VALID_DATA;
        $dataFailure['長期_期間From>期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2022/11/01';
        $dataFailure['長期_期間From>期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2022/10/31';
        $dataFailure['長期_期間From>期間To']['errors'][0] = '開始日と終了日の関係性に誤りがあるので確認してください';

        // 援助目標 長期の期間の開始日が2000年4月以前の場合、エラーになることを確認
        $dataFailure['長期_期間From_2000年4月以前'] = self::VALID_DATA;
        $dataFailure['長期_期間From_2000年4月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2000/03/31';
        $dataFailure['長期_期間From_2000年4月以前']['errors'][0] = '2000年4月以降の年月を入力してください';

        // 援助目標 長期の期間の終了日が2000年4月以前の場合、エラーになることを確認
        $dataFailure['長期_期間To_2000年4月以前'] = self::VALID_DATA;
        $dataFailure['長期_期間To_2000年4月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '';
        $dataFailure['長期_期間To_2000年4月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2000/03/31';
        $dataFailure['長期_期間To_2000年4月以前']['errors'][0] = '2000年4月以降の年月を入力してください';

        // 援助目標 長期の期間の開始日が2100年1月以降の場合、エラーになることを確認
        $dataFailure['長期_期間From_2100年1月以降'] = self::VALID_DATA;
        $dataFailure['長期_期間From_2100年1月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '';
        $dataFailure['長期_期間From_2100年1月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2100/01/01';
        $dataFailure['長期_期間From_2100年1月以降']['errors'][0] = '2099年12月以前の年月を入力してください';

        // 援助目標 長期の期間の終了日が2100年1月以降の場合、エラーになることを確認
        $dataFailure['長期_期間To_2100年1月以降'] = self::VALID_DATA;
        $dataFailure['長期_期間To_2100年1月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2100/01/01';
        $dataFailure['長期_期間To_2100年1月以降']['errors'][0] = '2099年12月以前の年月を入力してください';

        // 援助目標 長期の期間の開始日がケアプラン期間の開始日より過去日の場合、エラーになることを確認
        $dataFailure['長期_期間From>ケアプラン期間From'] = self::VALID_DATA;
        $dataFailure['長期_期間From>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2022/09/01';
        $dataFailure['長期_期間From>ケアプラン期間From']['data']['care_plan_period_start'] = '2022/09/02';
        $dataFailure['長期_期間From>ケアプラン期間From']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        // 援助目標 長期の期間の開始日がケアプラン期間の終了日より未来日の場合、エラーになることを確認
        $dataFailure['長期_期間From<ケアプラン期間To'] = self::VALID_DATA;
        $dataFailure['長期_期間From<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '2022/11/01';
        $dataFailure['長期_期間From<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '';
        $dataFailure['長期_期間From<ケアプラン期間To']['data']['care_plan_period_end'] = '2022/10/31';
        $dataFailure['長期_期間From<ケアプラン期間To']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        // 援助目標 長期の期間の終了日がケアプラン期間の開始日より過去日の場合、エラーになることを確認
        $dataFailure['長期_期間To>ケアプラン期間From'] = self::VALID_DATA;
        $dataFailure['長期_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_start'] = '';
        $dataFailure['長期_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2022/09/01';
        $dataFailure['長期_期間To>ケアプラン期間From']['data']['care_plan_period_start'] = '2022/09/02';
        $dataFailure['長期_期間To>ケアプラン期間From']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        // 援助目標 長期の期間の終了日がケアプラン期間の終了日より未来日の場合、エラーになることを確認
        $dataFailure['長期_期間To<ケアプラン期間To'] = self::VALID_DATA;
        $dataFailure['長期_期間To<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['task_end'] = '2022/11/01';
        $dataFailure['長期_期間To<ケアプラン期間To']['data']['care_plan_period_end'] = '2022/10/31';
        $dataFailure['長期_期間To<ケアプラン期間To']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        // 援助目標 短期の期間の検証
        // 援助目標 短期の期間について開始日が終了日より未来日の場合、エラーになることを確認
        $dataFailure['短期_期間From>期間To'] = self::VALID_DATA;
        $dataFailure['短期_期間From>期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '2022/11/01';
        $dataFailure['短期_期間From>期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2022/10/31';
        $dataFailure['短期_期間From>期間To']['errors'][0] = '開始日と終了日の関係性に誤りがあるので確認してください';

        // 援助目標 短期の期間の開始日が2000年4月以前の場合、エラーになることを確認
        $dataFailure['短期_期間From_2000年4月以前'] = self::VALID_DATA;
        $dataFailure['短期_期間From_2000年4月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '2000/03/31';
        $dataFailure['短期_期間From_2000年4月以前']['errors'][0] = '2000年4月以降の年月を入力してください';

        // 援助目標 短期の期間の終了日が2000年4月以前の場合、エラーになることを確認
        $dataFailure['短期_期間To_2000年4月以前'] = self::VALID_DATA;
        $dataFailure['短期_期間To_2000年4月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '';
        $dataFailure['短期_期間To_2000年4月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2000/03/31';
        $dataFailure['短期_期間To_2000年4月以前']['errors'][0] = '2000年4月以降の年月を入力してください';

        // 援助目標 短期の期間の開始日が2100年1月以降の場合、エラーになることを確認
        $dataFailure['短期_期間From_2100年1月以降'] = self::VALID_DATA;
        $dataFailure['短期_期間From_2100年1月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '2100/01/01';
        $dataFailure['短期_期間From_2100年1月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '';
        $dataFailure['短期_期間From_2100年1月以降']['errors'][0] = '2099年12月以前の年月を入力してください';

        // 援助目標 短期の期間の終了日が2100年1月以降の場合、エラーになることを確認
        $dataFailure['短期_期間To_2100年1月以降'] = self::VALID_DATA;
        $dataFailure['短期_期間To_2100年1月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2100/01/01';
        $dataFailure['短期_期間To_2100年1月以降']['errors'][0] = '2099年12月以前の年月を入力してください';

        // 援助目標 短期の期間の開始日がケアプラン期間の開始日より過去日の場合、エラーになることを確認
        $dataFailure['短期_期間From>ケアプラン期間From'] = self::VALID_DATA;
        $dataFailure['短期_期間From>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '2022/09/01';
        $dataFailure['短期_期間From>ケアプラン期間From']['data']['care_plan_period_start'] = '2022/09/02';
        $dataFailure['短期_期間From>ケアプラン期間From']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        // 援助目標 短期の期間の開始日がケアプラン期間の終了日より未来日の場合、エラーになることを確認
        $dataFailure['短期_期間From<ケアプラン期間To'] = self::VALID_DATA;
        $dataFailure['短期_期間From<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '2022/11/01';
        $dataFailure['短期_期間From<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '';
        $dataFailure['短期_期間From<ケアプラン期間To']['data']['care_plan_period_end'] = '2022/10/31';
        $dataFailure['短期_期間From<ケアプラン期間To']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        // 援助目標 短期の期間の終了日がケアプラン期間の開始日より過去日の場合、エラーになることを確認
        $dataFailure['短期_期間To>ケアプラン期間From'] = self::VALID_DATA;
        $dataFailure['短期_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_start'] = '';
        $dataFailure['短期_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2022/09/01';
        $dataFailure['短期_期間To>ケアプラン期間From']['data']['care_plan_period_start'] = '2022/09/02';
        $dataFailure['短期_期間To>ケアプラン期間From']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        // 援助目標 短期の期間の終了日がケアプラン期間の終了日より未来日の場合、エラーになることを確認
        $dataFailure['短期_期間To<ケアプラン期間To'] = self::VALID_DATA;
        $dataFailure['短期_期間To<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['task_end'] = '2022/11/01';
        $dataFailure['短期_期間To<ケアプラン期間To']['data']['care_plan_period_end'] = '2022/10/31';
        $dataFailure['短期_期間To<ケアプラン期間To']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        // 援助内容の期間の検証
        // 援助内容の期間について開始日が終了日より未来日の場合、エラーになることを確認
        $dataFailure['援助内容_期間From>期間To'] = self::VALID_DATA;
        $dataFailure['援助内容_期間From>期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '2022/11/01';
        $dataFailure['援助内容_期間From>期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2022/10/31';
        $dataFailure['援助内容_期間From>期間To']['errors'][0] = '開始日と終了日の関係性に誤りがあるので確認してください';

        // 援助内容の期間の開始日が2000年4月以前の場合、エラーになることを確認
        $dataFailure['援助内容_期間From_2000年4月以前'] = self::VALID_DATA;
        $dataFailure['援助内容_期間From_2000年4月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '2000/03/31';
        $dataFailure['援助内容_期間From_2000年4月以前']['errors'][0] = '2000年4月以降の年月を入力してください';

        // 援助内容の期間の終了日が2000年4月以前の場合、エラーになることを確認
        $dataFailure['援助内容_期間To_2000年4月以前'] = self::VALID_DATA;
        $dataFailure['援助内容_期間To_2000年4月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '';
        $dataFailure['援助内容_期間To_2000年4月以前']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2000/03/31';
        $dataFailure['援助内容_期間To_2000年4月以前']['errors'][0] = '2000年4月以降の年月を入力してください';

        // 援助内容の期間の開始日が2100年1月以降の場合、エラーになることを確認
        $dataFailure['援助内容_期間From_2100年1月以降'] = self::VALID_DATA;
        $dataFailure['援助内容_期間From_2100年1月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '2100/01/01';
        $dataFailure['援助内容_期間From_2100年1月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '';
        $dataFailure['援助内容_期間From_2100年1月以降']['errors'][0] = '2099年12月以前の年月を入力してください';

        // 援助内容の期間の終了日が2100年1月以降の場合、エラーになることを確認
        $dataFailure['援助内容_期間To_2100年1月以降'] = self::VALID_DATA;
        $dataFailure['援助内容_期間To_2100年1月以降']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2100/01/01';
        $dataFailure['援助内容_期間To_2100年1月以降']['errors'][0] = '2099年12月以前の年月を入力してください';

        // 援助内容の期間の開始日がケアプラン期間の開始日より過去日の場合、エラーになることを確認
        $dataFailure['援助内容_期間From>ケアプラン期間From'] = self::VALID_DATA;
        $dataFailure['援助内容_期間From>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '2022/09/01';
        $dataFailure['援助内容_期間From>ケアプラン期間From']['data']['care_plan_period_start'] = '2022/09/02';
        $dataFailure['援助内容_期間From>ケアプラン期間From']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        // 援助内容の期間の開始日がケアプラン期間の終了日より未来日の場合、エラーになることを確認
        $dataFailure['援助内容_期間From<ケアプラン期間To'] = self::VALID_DATA;
        $dataFailure['援助内容_期間From<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '2022/11/01';
        $dataFailure['援助内容_期間From<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '';
        $dataFailure['援助内容_期間From<ケアプラン期間To']['data']['care_plan_period_end'] = '2022/10/31';
        $dataFailure['援助内容_期間From<ケアプラン期間To']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        // 援助内容の期間の終了日がケアプラン期間の開始日より過去日の場合、エラーになることを確認
        $dataFailure['援助内容_期間To>ケアプラン期間From'] = self::VALID_DATA;
        $dataFailure['援助内容_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_start'] = '';
        $dataFailure['援助内容_期間To>ケアプラン期間From']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2022/09/01';
        $dataFailure['援助内容_期間To>ケアプラン期間From']['data']['care_plan_period_start'] = '2022/09/02';
        $dataFailure['援助内容_期間To>ケアプラン期間From']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        // 援助内容の期間の終了日がケアプラン期間の終了日より未来日の場合、エラーになることを確認
        $dataFailure['援助内容_期間To<ケアプラン期間To'] = self::VALID_DATA;
        $dataFailure['援助内容_期間To<ケアプラン期間To']['data']['service_plan2']['need_list'][0]['long_plan_list'][0]['short_plan_list'][0]['support_list'][0]['task_end'] = '2022/11/01';
        $dataFailure['援助内容_期間To<ケアプラン期間To']['data']['care_plan_period_end'] = '2022/10/31';
        $dataFailure['援助内容_期間To<ケアプラン期間To']['errors'][0] = 'ケアプラン期間内の日付を入力してください';

        return $dataFailure;
    }
}
