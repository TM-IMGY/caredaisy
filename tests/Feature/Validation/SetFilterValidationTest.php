<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;

use App\Http\Requests\GroupHome\TransmitInfo\SetFilterRequest;
use Illuminate\Support\Facades\Validator;

/**
 * 伝送請求のバリデーションテスト
 */
class SetFilterValidationTest extends TestCase
{
    /**
     * バリデーションが通るデータ
     */
    const VALID_DATA = [
        'data' =>  [
            'facility_id'       => 1,
            'from_date'=> '2000-04-01',
            'to_date'  => '2099-01-01',
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
        $request = new SetFilterRequest();
        // リクエストにデータを設定
        $request->merge($data);
        // rulesに設定した制約でvalidationを実行
        $validator = Validator::make(
            $request->validationData(),
            $request->rules(),
            $request->messages(),
            $request->attributes()
        );
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
        $request = new SetFilterRequest();
        // リクエストにデータを設定
        $request->merge($dataFailure);
        // rulesに設定した制約でvalidationを実行
        $validator = Validator::make(
            $request->validationData(),
            $request->rules(),
            $request->messages(),
            $request->attributes()
        );
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

        // 処理対象年月の期間の検証
        // 処理対象年月の期間について開始月が終了月より過去月の場合、エラーにならないことを確認
        $data['処理対象年月_期間From<期間To'] = self::VALID_DATA;
        $data['処理対象年月_期間From<期間To']['data']['from_date'] = '2022/11/01';
        $data['処理対象年月_期間From<期間To']['data']['to_date'] = '2022/12/01';

        // 処理対象年月の期間について開始月と終了月が同月の場合、エラーにならないことを確認
        $data['処理対象年月_期間From=期間To'] = self::VALID_DATA;
        $data['処理対象年月_期間From=期間To']['data']['from_date'] = '2022/11/01';
        $data['処理対象年月_期間From=期間To']['data']['to_date'] = '2022/11/01';

        // 処理対象年月の期間の開始月が2000年4月以降の場合、エラーにならないことを確認
        $data['処理対象年月_期間From_2000年4月以降'] = self::VALID_DATA;
        $data['処理対象年月_期間From_2000年4月以降']['data']['from_date'] = '2000/04/01';

        // 処理対象年月の期間の終了月が2000年4月以降の場合、エラーにならないことを確認
        $data['処理対象年月_期間To_2000年4月以降'] = self::VALID_DATA;
        $data['処理対象年月_期間To_2000年4月以降']['data']['from_date'] = '';
        $data['処理対象年月_期間To_2000年4月以降']['data']['to_date'] = '2000/04/01';

        // 処理対象年月の期間の開始月が2100年1月以前の場合、エラーにならないことを確認
        $data['処理対象年月_期間From_2100年1月以前'] = self::VALID_DATA;
        $data['処理対象年月_期間From_2100年1月以前']['data']['from_date'] = '2099/12/01';
        $data['処理対象年月_期間From_2100年1月以前']['data']['to_date'] = '';

        // 処理対象年月の期間の終了月が2100年1月以前の場合、エラーにならないことを確認
        $data['処理対象年月_期間To_2100年1月以前'] = self::VALID_DATA;
        $data['処理対象年月_期間To_2100年1月以前']['data']['to_date'] = '2099/12/01';

        return $data;
    }

    /**
     * バリデーションが失敗するテスト用のデータプロバイダ
     */
    public function validDataProviderFailure(): array
    {
        // 処理対象年月の期間の検証
        // 処理対象年月の期間について開始月が終了月より未来月の場合、エラーになることを確認
        $dataFailure['処理対象年月_期間From>期間To'] = self::VALID_DATA;
        $dataFailure['処理対象年月_期間From>期間To']['data']['from_date'] = '2022/11/01';
        $dataFailure['処理対象年月_期間From>期間To']['data']['to_date'] = '2022/10/01';
        $dataFailure['処理対象年月_期間From>期間To']['errors'][0] = '開始月と終了月の関係性に誤りがあるので確認してください';

        // 処理対象年月の期間の開始月が2000年4月以前の場合、エラーになることを確認
        $dataFailure['処理対象年月_期間From_2000年4月以前'] = self::VALID_DATA;
        $dataFailure['処理対象年月_期間From_2000年4月以前']['data']['from_date'] = '2000/03/01';
        $dataFailure['処理対象年月_期間From_2000年4月以前']['errors'][0] = '2000年4月以降の年月を入力してください';

        // 処理対象年月の期間の終了月が2000年4月以前の場合、エラーになることを確認
        $dataFailure['処理対象年月_期間To_2000年4月以前'] = self::VALID_DATA;
        $dataFailure['処理対象年月_期間To_2000年4月以前']['data']['from_date'] = '';
        $dataFailure['処理対象年月_期間To_2000年4月以前']['data']['to_date'] = '2000/03/01';
        $dataFailure['処理対象年月_期間To_2000年4月以前']['errors'][0] = '2000年4月以降の年月を入力してください';

        // 処理対象年月の期間の開始月が2100年1月以降の場合、エラーになることを確認
        $dataFailure['処理対象年月_期間From_2100年1月以降'] = self::VALID_DATA;
        $dataFailure['処理対象年月_期間From_2100年1月以降']['data']['from_date'] = '';
        $dataFailure['処理対象年月_期間From_2100年1月以降']['data']['to_date'] = '2100/01/01';
        $dataFailure['処理対象年月_期間From_2100年1月以降']['errors'][0] = '2099年12月以前の年月を入力してください';

        // 処理対象年月の期間の終了月が2100年1月以降の場合、エラーになることを確認
        $dataFailure['処理対象年月_期間To_2100年1月以降'] = self::VALID_DATA;
        $dataFailure['処理対象年月_期間To_2100年1月以降']['data']['to_date'] = '2100/01/01';
        $dataFailure['処理対象年月_期間To_2100年1月以降']['errors'][0] = '2099年12月以前の年月を入力してください';

        return $dataFailure;
    }
}
