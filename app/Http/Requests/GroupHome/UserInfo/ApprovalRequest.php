<?php

namespace App\Http\Requests\GroupHome\UserInfo;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\CareLevel;
use App\Models\UserCareInformation;

class ApprovalRequest extends CareDaisyBaseFormRequest
{
    const VALID_START_DATE = '2000-03-31';
    const VALID_END_DATE = '2100-01-01';

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
            'facility_user_id'                   => 'bail | required | integer',
            'careLevel'                          => 'bail | required | integer',
            'certificationStatus'                => 'bail | required | integer',
            'recognitionDate'                    => [
                'bail',
                'required_if:certificationStatus,2',
                'nullable',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],

            'startDate'                          => [
                'bail',
                'required_if:certificationStatus,2',
                'nullable',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],

            'endDate'                            => [
                'bail',
                'required_if:certificationStatus,2',
                'nullable',
                'regex:/^[0-9\/]+$/',
                'date',
                'after_or_equal:startDate',
                'before:'.self::VALID_END_DATE
            ],

            'date_confirmation_insurance_card'   => [
                'bail',
                'nullable',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],

            'date_qualification'                 => [
                'bail',
                'nullable',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],

            'saveGetIdApproval'                  => 'integer', //履歴ID
        ];

        return $rules;
    }
    /**
     * 既存データと期間が重なるかどうかチェック
     */
    public function withValidator($validator)
    {
        if ($validator->fails()) return;
        $startDate = $this->input('startDate');
        $endDate = $this->input('endDate');
        $facilityUserId = $this->input('facility_user_id');
        $userCareInfoId = $this->input('saveGetIdApproval');
        $validator->after(function ($validator) use($startDate, $endDate, $facilityUserId, $userCareInfoId)
        {
            if(!isset($userCareInfoId)) $userCareInfoId = 0;
            if(isset($facilityUserId) && isset($startDate) && isset($endDate)  )
            {
                $duplicate = UserCareInformation::where([
                    ['facility_user_id', $facilityUserId],
                    ['care_period_end', '>=', $startDate],
                    ['care_period_start', '<=', $endDate],
                    ['user_care_info_id', '<>', $userCareInfoId]
                ])->count();
                if ($duplicate > 0)
                {
                    $validator->errors()->add('DateDuplication', '重複している期間があるので保存できません');
                }
            }

        });
    }

    public function messages()
    {
        return [
            'required'          => ':attribute は必須項目です。',
            'required_if'       => ':attribute は必須項目です。',
            'integer'           => ':attribute の形式が不正です。',
            'date'       => ':attribute の形式が不正です。',
            'before'            => '2099年12月以前の年月を入力してください',
            'after'             => '2000年4月以降の年月を入力してください',
            'after_or_equal'    => '開始日と終了日の関係性に誤りがあるので確認してください',
            'regex'             => ':attribute は半角で入力してください'
        ];
    }

    public function attributes()
    {
        return [
            'facility_user_id'                              => '利用者選択',
            'careLevel'                                     => '要介護度 ',
            'certificationStatus'                           => '認定状況',
            'recognitionDate'                               => '認定年月日',
            'startDate'                                     => '有効開始日',
            'endDate'                                       => '有効終了日',
            'date_confirmation_insurance_card'              => '保険証確認日',
            'date_qualification'                            => '交付年月日',
        ];
    }
}
