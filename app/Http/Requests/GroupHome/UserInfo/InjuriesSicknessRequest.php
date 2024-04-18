<?php

namespace App\Http\Requests\GroupHome\UserInfo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\CareDaisyBaseFormRequest;
use App\Models\InjuriesSickness;

class InjuriesSicknessRequest extends CareDaisyBaseFormRequest
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
        $validationDuplicationDateFunc = function ($attribute, $value, $fail) {
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

            $countResult = InjuriesSickness::where($where)->count();

            if ($countResult != self::RECORD_COUNT_CRITERION) {
                $fail('重複している期間があるので保存できません');
            }

            return $value;
        };

        /**
         * 特別診療費の重複チェック
         */
        $validationDuplicationIdFunc = function ($attribute, $value, $fail) {
            $inputData = $this->all();
            $special = $inputData['request_special'];
            foreach ($special as $key => $val) {
                $ids[$key] =  $val['ids'];
            }
            $ids = array_reduce($ids, 'array_merge', []);
            $duplicateIds = array_diff($ids, array(null));
            // 各値の出現回数を数える
            $valueCount = array_count_values($duplicateIds);
            if (empty($valueCount)) {
                return $value;
            }
            // 最大の出現回数を取得する
            $max = max($valueCount);
            if ($max > 1) {
                $fail('特別診療費で重複している項目があります。');
            }
            return $value;
        };

        /**
         * 特別診療費が選択されていた場合に傷病名が空白ではないかどうか
         */
        $validationEmptyFunc = function ($attribute, $value, $fail) {
            $keyNum = filter_var($attribute, FILTER_SANITIZE_NUMBER_INT);
            $inputData = $this->all();
            $validateTarget = $inputData['request_special'][$keyNum];
            $ids = array_diff($validateTarget['ids'], array(null));
            if (!$value && count($ids) > 0) {
                $fail('傷病名' . $keyNum . 'が未入力です。');
            }
            return $value;
        };

        $rules = [
            'facility_user_id'              => 'bail | required | integer',
            'facility_id'                   => 'bail | required | integer',
            'request_special'               => 'required | array',
            'request_special.*.name'        => ['bail', 'max:100', $validationEmptyFunc],
            'request_special.*.ids'          => ['bail', 'array', $validationDuplicationIdFunc],
            'request_special.*.ids.*'        => 'bail |nullable | integer',
            'start_date'                    => [
                'bail',
                'required',
                'date_format:Y/m/d',
                'before:' . self::VALID_END_DATE,
                'after:' . self::VALID_START_DATE,
                'regex:/^(19[0-9]{2}|20[0-9]{2})\/(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])$/',
                $validationDuplicationDateFunc
            ],
            'end_date'                      => [
                'bail',
                'required',
                'date_format:Y/m/d',
                'after_or_equal:start_date',
                'before:' . self::VALID_END_DATE,
                'after:' . self::VALID_START_DATE,
                'regex:/^(19[0-9]{2}|20[0-9]{2})\/(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])$/'
            ],
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'required'                      => ':attribute は必須項目です。',
            'integer'                       => ':attribute の形式が不正です。',
            'regex'                         => ':attribute の桁数が不正です。',
            'max'                           => ':attribute は :max文字以下で入力してください。',
            'after_or_equal'                => '適用終了日が適用開始日より過去の日付です。',
            'date_format'                   => ':attribute の形式が不正です。',
            'before'                        => '2099年12月以前の年月を入力してください',
            'after'                         => '2000年4月以降の年月を入力してください',
        ];
    }

    public function attributes()
    {
        return [
            'facility_user_id'              => '利用者選択',
            'facility_id'                   => '事業所',
            'request_special.*.name'        => '傷病名',
            'request_special.*.id'          => '特別診療費',
            'start_date'                    => '適用開始日',
            'end_date'                      => '適用終了日',
        ];
    }
}
