<?php

namespace App\Http\Requests\GroupHome\TransmitInfo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\CareDaisyBaseFormRequest;

class DocumentRequest extends CareDaisyBaseFormRequest
{
    const VALID_START_DATE = '2000-03-31';
    const VALID_END_DATE = '2100-01-01';

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'facility_id'    => 'bail | required | integer'
        ];

        if($this->has('from_date')) {
            $rules['from_date'] = [
                'bail',
                'regex:/^[0-9\/]+$/',
                'date_format:Y/m/d',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ];
        }
        if($this->has('to_date')) {
            $rules['to_date'] = [
                'bail',
                'regex:/^[0-9\/]+$/',
                'date_format:Y/m/d',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ];
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if($validator->fails()) {
            return;
        }

        $fromDate = $this->input('from_date');
        $toDate = $this->input('to_date');
        if(!isset($fromDate) || !isset($toDate)) {
            return;
        }

        $validator->after(function ($validator) use($fromDate, $toDate)
        {
            if($fromDate > $toDate) {
                $validator->errors()->add('DateComparison', '絞り込み開始日と絞り込み終了日の関係性に誤りがあるので確認してください');
            }
        });
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
            'required'       => ':attribute は必須項目です',
            'integer'        => ':attribute の形式が不正です',
            'regex'          => ':attribute は半角で入力してください',
            'date_format'    => ':attribute の形式が不正です',
            'before'         => '2099年12月以前の年月を入力してください',
            'after'          => '2000年4月以降の年月を入力してください'
        ];
    }

    public function attributes()
    {
        return [
            'from_date'      => '絞り込み開始日',
            'to_date'        => '絞り込み終了日'
        ];
    }
}
