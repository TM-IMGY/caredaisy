<?php

namespace App\Http\Requests\GroupHome\UserInfo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\CareDaisyBaseFormRequest;
use App\Models\FacilityUserBurdenLimit;

class BurdenLimitRequest extends CareDaisyBaseFormRequest
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
        if ($this->has('facility_user_id') && $this->facility_user_id != null) {
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
        $res = response()->json([
            'errors' => $validator->errors(),
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
        $rules = [
            'facility_user_id'                         => 'bail | required | integer',
            'food_expenses_burden_limit'               => 'bail | required | integer | digits_between:1,4',
            'living_expenses_burden_limit'             => 'bail | required | integer | digits_between:1,4',
            'start_date'                               => [
                'bail',
                'required',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],
            'end_date'                                 => [
                'bail',
                'required',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE,
                'after_or_equal:start_date'
            ],
        ];
        return $rules;
    }

    public function withValidator($validator)
    {
        if ($validator->fails()) {
            return;
        }

        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        $facilityUserId = $this->input('facility_user_id');
        $id = $this->input('id');

        $validator->after(function ($validator) use($startDate, $endDate, $facilityUserId, $id)
        {
            $where = [
                ['facility_user_id', $facilityUserId],
                ['start_date', '<=', $endDate],
                ['end_date', '>=', $startDate],
            ];

            if (isset($id)) {
                $where[] = ['id', '<>', $id];
            }

            $count = FacilityUserBurdenLimit::where($where)->count();
            if ($count > self::RECORD_COUNT_CRITERION) {
                $validator->errors()->add('DateDuplication', '重複している期間が登録されているため保存できません。');
            }
        });
    }

    public function messages()
    {
        return [
            'required'                       => ':attribute は必須項目です。',
            'integer'                        => ':attribute の形式が不正です。',
            'regex'                          => ':attribute の桁数が不正です。',
            'date'                           => ':attribute の形式が不正です。',
            'after_or_equal'                 => '適用終了日が適用開始日より過去の日付です。',
            'before'                         => '2099年12月以前の年月を入力してください',
            'after'                          => '2000年4月以降の年月を入力してください',
            'digits_between'                 => ':attribute は 0 ～ 9999の間で入力してください。',
            'start_date.regex'               => ':attribute は半角で入力してください',
            'end_date.regex'                 => ':attribute は半角で入力してください',
        ];
    }

    public function attributes()
    {
        return [
            'facility_user_id'                          => '利用者選択',
            'food_expenses_burden_limit'                => '食費（負担限度額）',
            'living_expenses_burden_limit'              => '居住費（負担限度額）',
            'start_date'                                => '適用開始日',
            'end_date'                                  => '適用終了日',
        ];
    }
}
