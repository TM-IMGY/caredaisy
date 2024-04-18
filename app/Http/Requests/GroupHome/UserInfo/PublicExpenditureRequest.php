<?php

namespace App\Http\Requests\GroupHome\UserInfo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\CareDaisyBaseFormRequest;
use App\Models\UserPublicExpenseInformation;

class PublicExpenditureRequest extends CareDaisyBaseFormRequest
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
            'bearer_number'                            => 'bail | required | regex:/^[0-9a-zA-Z]{8}$/ |',
            'recipient_number'                         => 'bail | required | regex:/^[0-9a-zA-Z]{7}$/ |',
            'food_expenses_burden_limit'               => 'bail | nullable | integer | regex:/^[0-9]{0,11}$/ |',
            'living_expenses_burden_limit'             => 'bail | nullable | integer | regex:/^[0-9]{0,11}$/ |',
            'outpatient_contribution'                  => 'bail | nullable | integer | regex:/^[0-9]{0,11}$/ |',
            'hospitalization_burden'                   => 'bail | nullable | integer | regex:/^[0-9]{0,11}$/ |',
            'application_classification'               => 'bail | nullable | max:255',
            'special_classification'                   => 'bail | nullable | max:255',
            'amount_borne_person'                      => 'nullable|integer',
            'confirmation_medical_insurance_date'      => [
                'bail',
                'nullable',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],

            'effective_start_date'                     => [
                'bail',
                'required',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],

            'expiry_date'                              => [
                'bail',
                'required',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE,
                'after_or_equal:effective_start_date'
            ],
        ];

        if($this->has('public_expense_information_id')){
            $rules['public_expense_information_id'] = 'bail|required|integer';
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
        $bearerNumberType = substr($this->input('bearer_number'), 0, 2);
        $peInfoId = $this->input('public_expense_information_id');

        $validator->after(function ($validator) use($startDate, $endDate, $facilityUserId, $bearerNumberType, $peInfoId)
        {
            $where = [
                ['facility_user_id', $facilityUserId],
                ['effective_start_date', '<=', $endDate],
                ['expiry_date', '>=', $startDate],
                ['bearer_number', 'like', $bearerNumberType.'%'],
            ];

            if (isset($peInfoId)) {
                $where[] = ['public_expense_information_id', '<>', $peInfoId];
            }

            $count = UserPublicExpenseInformation::where($where)->count();
            if ($count > self::RECORD_COUNT_CRITERION) {
                $validator->errors()->add('DateDuplication', '重複している期間があるので保存できません');
            }
        });
    }

    public function messages()
    {
        return [
            'required'                                          => ':attribute は必須項目です。',
            'integer'                                           => ':attribute の形式が不正です。',
            'regex'                                             => ':attribute の桁数が不正です。',
            'date'                                       => ':attribute の形式が不正です。',
            'after_or_equal'                                    => '開始日と終了日の関係性に誤りがあるので確認してください',
            'before'                                            => '2099年12月以前の年月を入力してください',
            'after'                                             => '2000年4月以降の年月を入力してください',
            'max'                                               => ':attribute は :max文字（桁）以下で入力してください。',
            'effective_start_date.regex'                        => ':attribute は半角で入力してください',
            'expiry_date.regex'                                 => ':attribute は半角で入力してください',
            'confirmation_medical_insurance_date.regex'         => ':attribute は半角で入力してください',
        ];
    }

    public function attributes()
    {
        return [
            'facility_user_id'                          => '利用者選択',
            'public_expense_information_id'             => '公費情報履歴',
            'bearer_number'                             => '負担者番号',
            'recipient_number'                          => '受給者番号',
            'confirmation_medical_insurance_date'       => '公費情報確認日',
            'food_expenses_burden_limit'                => '食費負担限度額',
            'living_expenses_burden_limit'              => '居住費負担限度額',
            'outpatient_contribution'                   => '外来負担金',
            'hospitalization_burden'                    => '入院負担額',
            'application_classification'                => '申請区分',
            'special_classification'                    => '特別区分',
            'effective_start_date'                      => '有効開始日',
            'expiry_date'                               => '有効終了日',
            'amount_borne_person'                       => '本人支払額',
        ];
    }
}
