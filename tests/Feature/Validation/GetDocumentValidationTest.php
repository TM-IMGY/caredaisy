<?php

namespace Tests\Feature\Validation;

use Tests\TestCase;

use App\Http\Requests\GroupHome\TransmitInfo\GetDocumentRequest;
use Illuminate\Support\Facades\Validator;

/**
 * 通知文書のバリデーションテスト
 */
class GetDocumentValidationTest extends TestCase
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
        $request = new GetDocumentRequest();
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
        $request = new GetDocumentRequest();
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

        // 発行日の期間の検証
        // 発行日の期間について開始日が終了日より過去日の場合、エラーにならないことを確認
        $data['発行日_期間From<期間To'] = self::VALID_DATA;
        $data['発行日_期間From<期間To']['data']['from_date'] = '2022/11/01';
        $data['発行日_期間From<期間To']['data']['to_date'] = '2022/11/02';

        // 発行日の期間について開始日と終了日が同日の場合、エラーにならないことを確認
        $data['発行日_期間From=期間To'] = self::VALID_DATA;
        $data['発行日_期間From=期間To']['data']['from_date'] = '2022/11/01';
        $data['発行日_期間From=期間To']['data']['to_date'] = '2022/11/01';

        // 発行日の期間の開始日が2000年4月以降の場合、エラーにならないことを確認
        $data['発行日_期間From_2000年4月以降'] = self::VALID_DATA;
        $data['発行日_期間From_2000年4月以降']['data']['from_date'] = '2000/04/01';

        // 発行日の期間の終了日が2000年4月以降の場合、エラーにならないことを確認
        $data['発行日_期間To_2000年4月以降'] = self::VALID_DATA;
        $data['発行日_期間To_2000年4月以降']['data']['from_date'] = '';
        $data['発行日_期間To_2000年4月以降']['data']['to_date'] = '2000/04/01';

        // 発行日の期間の開始日が2100年1月以前の場合、エラーにならないことを確認
        $data['発行日_期間From_2100年1月以前'] = self::VALID_DATA;
        $data['発行日_期間From_2100年1月以前']['data']['from_date'] = '2099/12/31';
        $data['発行日_期間From_2100年1月以前']['data']['to_date'] = '';

        // 発行日の期間の終了日が2100年1月以前の場合、エラーにならないことを確認
        $data['発行日_期間To_2100年1月以前'] = self::VALID_DATA;
        $data['発行日_期間To_2100年1月以前']['data']['to_date'] = '2099/12/31';

        return $data;
    }

    /**
     * バリデーションが失敗するテスト用のデータプロバイダ
     */
    public function validDataProviderFailure(): array
    {
        // 発行日の期間の検証
        // 発行日の期間について開始日が終了日より未来日の場合、エラーになることを確認
        $dataFailure['発行日_期間From>期間To'] = self::VALID_DATA;
        $dataFailure['発行日_期間From>期間To']['data']['from_date'] = '2022/11/01';
        $dataFailure['発行日_期間From>期間To']['data']['to_date'] = '2022/10/31';
        $dataFailure['発行日_期間From>期間To']['errors'][0] = '開始月と終了月の関係性に誤りがあるので確認してください';

        // 発行日の期間の開始日が2000年4月以前の場合、エラーになることを確認
        $dataFailure['発行日_期間From_2000年4月以前'] = self::VALID_DATA;
        $dataFailure['発行日_期間From_2000年4月以前']['data']['from_date'] = '2000/03/31';
        $dataFailure['発行日_期間From_2000年4月以前']['errors'][0] = '2000年4月以降の年月を入力してください';

        // 発行日の期間の終了日が2000年4月以前の場合、エラーになることを確認
        $dataFailure['発行日_期間To_2000年4月以前'] = self::VALID_DATA;
        $dataFailure['発行日_期間To_2000年4月以前']['data']['from_date'] = '';
        $dataFailure['発行日_期間To_2000年4月以前']['data']['to_date'] = '2000/03/31';
        $dataFailure['発行日_期間To_2000年4月以前']['errors'][0] = '2000年4月以降の年月を入力してください';

        // 発行日の期間の開始日が2100年1月以降の場合、エラーになることを確認
        $dataFailure['発行日_期間From_2100年1月以降'] = self::VALID_DATA;
        $dataFailure['発行日_期間From_2100年1月以降']['data']['from_date'] = '';
        $dataFailure['発行日_期間From_2100年1月以降']['data']['to_date'] = '2100/01/01';
        $dataFailure['発行日_期間From_2100年1月以降']['errors'][0] = '2099年12月以前の年月を入力してください';

        // 発行日の期間の終了日が2100年1月以降の場合、エラーになることを確認
        $dataFailure['発行日_期間To_2100年1月以降'] = self::VALID_DATA;
        $dataFailure['発行日_期間To_2100年1月以降']['data']['to_date'] = '2100/01/01';
        $dataFailure['発行日_期間To_2100年1月以降']['errors'][0] = '2099年12月以前の年月を入力してください';

        return $dataFailure;
    }
}
