<?php

namespace App\Http\Requests\GroupHome\UserInfo;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\UserBenefitInformation;

class BenefitInputRequest extends CareDaisyBaseFormRequest
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
     * バリデーション失敗時の処理を上書き
     *
     * @param Validator $validator
     * @throw HttpResponseException
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
     * 権限がなかった場合の処理を上書き
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('選択している利用者の編集は許可されていません。'),
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
            'facility_user_id'         => 'bail | required | integer',
            'benefit_type'             => 'bail | required | integer | between:1,3',
            'benefit_rate'             => 'bail | required |integer | between:0,100',
            'effective_start_date'     => [
                'bail',
                'required',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],

            'expiry_date'              => [
                'bail',
                'required',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE,
                'after_or_equal:effective_start_date'
            ],
        ];

        if($this->has('benefit_information_id')){
            $rules['benefit_information_id'] = 'bail | required | integer';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if ($validator->fails()) {
            return;
        }

        $startDate = $this->input('effective_start_date');
        $endDate = $this->input('expiry_date');

        if($endDate == null) {
            return;
        }

        $facilityUserId = $this->input('facility_user_id');
        $biInfoId = $this->input('benefit_information_id');

        $validator->after(function ($validator) use($startDate, $endDate, $facilityUserId, $biInfoId)
        {
            $where = [
                ['facility_user_id', $facilityUserId],
                ['effective_start_date', '<=', $endDate],
                ['expiry_date', '>=', $startDate],
            ];

            if (isset($biInfoId)) {
                $where[] = ['benefit_information_id', '<>', $biInfoId];
            }

            $count = UserBenefitInformation::where($where)->count();
            if ($count > self::RECORD_COUNT_CRITERION) {
                $validator->errors()->add('DateDuplication', '重複している期間があるので保存できません');
            }
        });
    }

    public function messages()
    {
        return [
            'required'          => ':attribute は必須項目です。',
            'integer'           => ':attribute の形式が不正です。',
            'regex'             => ':attribute は半角で入力してください',
            'date'       => ':attribute の形式が不正です。',
            'after_or_equal'    => '開始日と終了日の関係性に誤りがあるので確認してください',
            'before'            => '2099年12月以前の年月を入力してください',
            'after'             => '2000年4月以降の年月を入力してください',
            'max'               => ':attribute は :max文字（桁）以下で入力してください。',
        ];
    }

    public function attributes()
    {
        return [
            'facility_user_id'              => '利用者選択',
            'benefit_information_id'        => '給付率情報',
            'benefit_type'                  => '給付種類',
            'benefit_rate'                  => '負担割合',
            'effective_start_date'          => '有効開始日',
            'expiry_date'                   => '有効終了日',
        ];
    }
}
