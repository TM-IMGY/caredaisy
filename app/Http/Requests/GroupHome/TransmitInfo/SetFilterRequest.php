<?php

namespace App\Http\Requests\GroupHome\TransmitInfo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\CareDaisyBaseFormRequest;

class SetFilterRequest extends CareDaisyBaseFormRequest
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
        if ($this::has('facility_id') && $this->facility_id != null) {
            return $this->authorizeFacilityId($this->facility_id);
        }

        return false;
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

    public function rules()
    {
        $rules = [
            'from_date' =>
            [
                'bail',
                'nullable',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],
            'to_date' =>
            [
                'bail',
                'nullable',
                'date',
                'after_or_equal:from_date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],
        ];
        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        $res = response()->json([
            'errors' => $validator->errors(),
        ], 400);
        throw new HttpResponseException($res);
    }

    public function messages()
    {
        return [
            'date'           => ':attribute の形式が不正です。',
            'after_or_equal' => '開始月と終了月の関係性に誤りがあるので確認してください',
            'before'         => '2099年12月以前の年月を入力してください',
            'after'          => '2000年4月以降の年月を入力してください'
        ];
    }

    public function attributes()
    {
        return [
            'from_date' => '開始月',
            'to_date'   => '終了月'
        ];
    }
}
