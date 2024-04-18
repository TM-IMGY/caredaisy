<?php

namespace App\Http\Requests\GroupHome\Service;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class FacilityUserGetRequest extends CareDaisyBaseFormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        // FacilityUserController.phpから呼ばれるが、Controller側で権限チェックを行っているため、一旦対象外とする。
        // 時期を見てControllerから処理を外し、こちらに集約する
        return true;
    }

    /**
     * 権限がなかった場合の処理を上書き
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
     * @throw HttpResponseException
     * @see FormRequest::failedValidation()
     */
    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors();
        throw new HttpResponseException(response()->json($error, 400));
    }

    /**
     * @return array
     */
    public function rules()
    {
        return
        [
            'facility_user_id_list' => ['array','bail'],
            'facility_user_id_list.*' => 'bail|integer|required',
            'clm' => [
                'array',
                'bail',
                'required',
                Rule::in([
                    'facility_user_id','insurer_no','insured_no','last_name','first_name','last_name_kana','first_name_kana',
                    'gender','birthday','postal_code','location1','location2','phone_number','start_date','end_date',
                    'death_date','death_reason','remarks','blood_type','rh_type','cell_phone_number','before_in_status_id',
                    'after_out_status_id','diagnosis_date','diagnostician','consent_date','consenter','consenter_phone_number',
                    'invalid_flag','spacial_address_flag'
                ])
            ],
            'approval' => ['array','bail'],
            'approval.month' => 'bail|integer|date_format:m',
            'approval.year' => 'bail|integer|date_format:Y',
            'benefit_rate' => ['array','bail'],
            'benefit_rate.month' => 'bail|integer|date_format:m',
            'benefit_rate.year' => 'bail|integer|date_format:Y',
            'care_info' => ['array','bail'],
            'care_info.clm_list' => [
                'array',
                'bail',
                Rule::in([
                    'care_level_id','care_period_end','care_period_start','certification_status','facility_user_id','recognition_date'
                ])
            ],
            'care_info.month' => 'bail|nullable|integer|date_format:m',
            'care_info.year' => 'bail|nullable|integer|date_format:Y',
            'care_info.with' => 'array | bail',
            'care_info.with.care_level' => [
                'array',
                'bail',
                Rule::in([
                    'care_level_id','care_level','care_level_name'
                ])
            ]
        ];
    }

    public function messages()
    {
        return [
            'array' => ':attribute is array',
            'date_format' => ':attribute is date_format',
            'integer' => ':attribute is integer',
        ];
    }
}
