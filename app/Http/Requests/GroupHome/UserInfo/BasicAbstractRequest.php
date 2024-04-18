<?php

namespace App\Http\Requests\GroupHome\UserInfo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\CareDaisyBaseFormRequest;
use App\Models\BasicRemarks;

class BasicAbstractRequest extends CareDaisyBaseFormRequest
{

    const VALID_START_DATE = '2000-03-31';
    const VALID_END_DATE = '2100-01-01';
    const RECORD_COUNT_CRITERION = 0;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('facility_user_id') && $this->facility_user_id != null) {
            return $this->authorizeFacilityUserId($this->facility_user_id);
        }
        return false;
    }

    /**
     * 権限がなかった場合の処理を上書き
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('選択している利用者の編集は許可されていません。'),
        ], 400);
        throw new HttpResponseException($res);
    }

    protected function failedValidation(Validator $validator)
    {
        // messageを単配列化
        $flatArray = \Arr::flatten($validator->errors()->toArray());
        // 重複しているメッセージを除外
        $message = array_unique($flatArray);
        $res = response()->json([
            'errors' => $message,
        ], 400);
        throw new HttpResponseException($res);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $validationBasicAbstractFunc = function ($attribute, $value, $fail) {
            $inputData = $this->all();
            $startDate = $inputData['start_date'];
            $endDate = $inputData['end_date'];
            $facilityUserId = $inputData['facility_user_id'];

            $where = [
                ['facility_user_id', $facilityUserId],
                ['start_date', '<=', $endDate],
                ['end_date', '>=', $startDate],
            ];

            if (isset($inputData['id'])) {
                $where[] = ['id', '<>', $inputData['id']];
            }

            $countResult = BasicRemarks::where($where)->count();

            if ($countResult > self::RECORD_COUNT_CRITERION) {
                $fail('重複している期間があるので保存できません');
            }

            return $value;
        };

        $rules = [
            'facility_user_id'                      => 'bail | required | integer',
            'facility_id'                           => 'bail | required | integer',
            'dpc_code'                              => ['bail', 'required', 'regex:/\A[0-9a-zA-Z]{6}\z/u'],
            'main_injury_and_illness_name'          => 'bail | required | integer',
            'user_circumstance_code'                => ['bail', 'nullable', 'regex:/\A[ｦ-ﾟA-Z]+\z/u'],
            'start_date'                            => [
                'bail',
                'required',
                'date_format:Y/m/d',
                'before:' . self::VALID_END_DATE,
                'after:' . self::VALID_START_DATE,
                'regex:/^(19[0-9]{2}|20[0-9]{2})\/(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])$/',
                $validationBasicAbstractFunc
            ],

            'end_date'                              => [
                'bail',
                'required',
                'date_format:Y/m/d',
                'after_or_equal:start_date',
                'before:' . self::VALID_END_DATE,
                'after:' . self::VALID_START_DATE,
                'regex:/^(19[0-9]{2}|20[0-9]{2})\/(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])$/'
            ],
        ];

        if ($this->has('id')) {
            $rules['id'] = 'bail | required | integer';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'required'                                 => ':attribute は必須項目です。',
            'main_injury_and_illness_name.required'    => '正しいDPCコードを入力してください。',
            'dpc_code.regex'                           => '正しいDPCコードを入力してください。',
            'integer'                                  => ':attribute の形式が不正です。',
            'regex'                                    => ':attribute の桁数が不正です。',
            'after_or_equal'                           => '適用終了日が適用開始日より過去の日付です。',
            'before'                                   => '2099年12月以前の年月を入力してください',
            'after'                                    => '2000年4月以降の年月を入力してください',
            'date_format'                              => ':attribute の形式が不正です。'
        ];
    }

    public function attributes()
    {
        return [
            'facility_user_id'                  => '利用者選択',
            'facility_id'                       => '事業所',
            'dpc_code'                          => 'DPCコード',
            'main_injury_and_illness_name'      => '主傷病名',
            'user_circumstance_code'            => '利用者状態等コード',
            'start_date'                        => '有効開始日',
            'end_date'                          => '有効終了日',
        ];
    }
}
