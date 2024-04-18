<?php

namespace App\Http\Requests\Api;

use App\Rules\MbSize;

class UserRequest extends CommonRequest
{
    public function rules()
    {
        return [
            'facility_number' => [
                'required',
                'regex:/^[A-Z0-9]+$/',
                new MbSize(10, "[E00003]:attribute は10文字で入力してください。"),
            ],
            'all_get_flg'     => 'in:,1',
        ];
    }

    public function messages()
    {
        return [
            'required' => '[E00002]:attribute は必須項目です。',
            'regex'    => '[E00004]:attribute を正しい形式で指定してください。',
            'in'       => '[E00004]:attribute を正しい形式で指定してください。',
        ];
    }
}
