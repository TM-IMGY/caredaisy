<?php

namespace App\Http\Requests\GroupHome\UserInfo;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\UserFacilityServiceInformation;

class ServiceRequest extends CareDaisyBaseFormRequest
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
            'facility_id'                   => 'required | integer',
            'facility_user_id'              => 'required | integer',
            'serviceTypeCodeId'             => 'required | integer',
            'usageSituation'                => 'required | integer',
            'useStart'                      => [
                'bail',
                'required',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],
            'useEnd'                        => [
                'bail',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE,
                'after_or_equal:useStart'
            ],
            'saveGetIdService'              => 'integer'
        ];

        return $rules;
    }

    public function withValidator($validator)
    {
        if ($validator->fails()) {
            return;
        }
        // 利用状況が"利用中"なら期間重複チェックを行う
        if ($this->usageSituation != 1) {
            return;
        }

        $startDate = $this->input('useStart');
        $endDate = $this->input('useEnd');
        $facilityUserId = $this->input('facility_user_id');
        $saveGetIdService = $this->input('saveGetIdService');

        $validator->after(function ($validator) use($startDate, $endDate, $facilityUserId, $saveGetIdService)
        {
            $where = [
                ['use_end', '>=', $startDate],
                ['facility_user_id', $facilityUserId],
                ['use_start', '<=', $endDate],
                ['usage_situation','=',1]
            ];

            if ($saveGetIdService !== 0) {
                $where[] = ["user_facility_service_information_id", "<>", $saveGetIdService];
            }

            $count = UserFacilityServiceInformation::where($where)->count();
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
            'date'       => ':attribute の形式が不正です。',
            'after_or_equal'    => ':attribute が利用開始日より過去の日付です。',
            'regex'             => ':attribute は半角で入力してください',
            'before'            => '2099年12月以前の年月を入力してください',
            'after'             => '2000年4月以降の年月を入力してください'
        ];
    }

    public function attributes()
    {
        return [
            'facility_id'           => '事業所名',
            'facility_user_id'      => '利用者選択',
            'serviceTypeCodeId'     => 'サービス種類 ',
            'usageSituation'        => '利用状況',
            'useStart'              => '利用開始日',
            'useEnd'                => '利用終了日',
        ];
    }
}
