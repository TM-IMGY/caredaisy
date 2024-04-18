<?php

namespace App\Http\Requests\GroupHome\Service;

use App\Http\Requests\CareDaisyBaseFormRequest;
use App\Service\GroupHome\AdditionStatusService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\CarbonImmutable;

class FacilityAdditionUpdateCareRewardHistoryRequest extends CareDaisyBaseFormRequest
{
    const VALID_START_DATE = '2021-04-01';
    const VALID_END_DATE = '2024-03-31';
    const LIMIT_END_DATE = '2100-01-01';
    const ADDITION_STATUS_1 = 1; // 加算状況の「なし」
    const ADDITION_STATUS_2 = 2; // 加算状況の「あり」又は「加算Ⅰ」
    const MODIFICATION_DAY = '2022-10-01';

    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize()
    {
        if (
            $this->has('facility_id') && $this->facility_id != null &&
            $this->has('care_reward_history') && $this->care_reward_history['care_reward_histories_id'] != null
        ) {
            return
                $this->authorizeFacilityId($this->facility_id) &&
                $this->authorizeCareRewardHistoryId($this->care_reward_history['care_reward_histories_id'], $this->facility_id);
        }
        return false;
    }

    /**
     * 権限がなかった場合の処理を上書きする
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('この操作は許可されていません。'),
        ], 400);
        throw new HttpResponseException($res);
    }

    /**
     * @param Validator $validator
     * @see FormRequest::failedValidation()
     */
    protected function failedValidation(Validator $validator)
    {
        $res = response()->json([
            'errors' => $validator->errors(),
        ], 400);
        throw new HttpResponseException($res);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'care_reward_history'             => ['bail', 'required', 'array'],
            'facility_id'                     => 'bail|integer|required',
            'service_type_code_id'            => 'bail|integer|required',
            'start_date'                      => 'bail|date|required',
            'end_date'                        => 'bail|date|required',
            'care_reward_history.start_month' => 'bail|before_or_equal:care_reward_history.end_month',
            'care_reward_history.end_month'   => 'bail|before:'.self::LIMIT_END_DATE,
        ];
    }

    /**
     * バリデータインスタンスの設定
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startMonth = new CarbonImmutable($this->care_reward_history['start_month']); // 開始月
            $endMonth   = new CarbonImmutable($this->care_reward_history['end_month']); // 終了月

            if ( ! $validator->errors()->has('care_reward_history')) {
                // ベースアップ等支援加算が「あり」の場合
                if ($this->care_reward_history['baseup'] == self::ADDITION_STATUS_2) {
                    // 2022/10 の臨時の報酬改定対応
                    $modificationDate = new CarbonImmutable(self::MODIFICATION_DAY);

                    // 開始月が改定月前の場合はエラー
                    if ($startMonth->lt($modificationDate)) {
                        $validator->errors()->add('care_reward_history', 'ベースアップ等支援加算を「あり」にする場合は、<br>新規作成ボタン押下後、開始月を2022年10月以降に設定してください。');
                    }

                    // 処遇改善加算が「なし」の場合はエラー
                    if ($this->care_reward_history['treatment_improvement'] == self::ADDITION_STATUS_1) {
                        $validator->errors()->add('care_reward_history', '処遇体制加算未取得のためベースアップ等支援加算は設定出来ません。');
                    }
                }
            }

            // 有効期間チェック
            if ( ! $validator->errors()->has('care_reward_history')) {
                $validStartDate = new CarbonImmutable(self::VALID_START_DATE);
                $validEndDate   = new CarbonImmutable(self::VALID_END_DATE);

                if ( ! $startMonth->between($validStartDate, $validEndDate)
                || ! $endMonth->between($validStartDate, $validEndDate)) {
                    $validator->errors()->add('care_reward_history', '開始月、終了月を' . $validStartDate->format('Y/n') . '~' . $validEndDate->format('Y/n') . 'の範囲で指定してください');
                }
            }

            // 期間重複チェック
            if ( ! $validator->errors()->has('care_reward_history')) {
                $params = [
                    'facility_id'          => $this->facility_id,
                    'service_type_code_id' => $this->service_type_code_id,
                ];

                $additionStatusService = new AdditionStatusService();
                $careRewardHistories = $additionStatusService->getCareRewardHistories($params);
                foreach($careRewardHistories as $data)
                {
                    if ($data['id'] == $this->care_reward_history['care_reward_histories_id']) continue;
                    $historyStartMonth = new CarbonImmutable($data['start_month']);
                    $historyEndMonth   = new CarbonImmutable($data['end_month']);

                    if ($startMonth->between($historyStartMonth, $historyEndMonth)
                    || $endMonth->between($historyStartMonth, $historyEndMonth)
                    || ($historyStartMonth->gte($startMonth) && $historyEndMonth->lte($endMonth))) {
                        $validator->errors()->add('care_reward_history', '重複しているデータがあるので保存できません');
                        break;
                    }
                }
            }
        });
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'array'           => ':attribute の形式が不正です。',
            'date'            => ':attribute の形式が不正です。',
            'integer'         => ':attribute の形式が不正です。',
            'required'        => ':attribute は必須項目です。',
            'before'          => '2099年12月以前の年月を入力してください',
            'before_or_equal' => '開始月と終了月の関係性に誤りがあるので確認してください',
        ];
    }
}
