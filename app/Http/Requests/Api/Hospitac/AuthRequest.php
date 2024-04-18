<?php

namespace App\Http\Requests\Api\Hospitac;

class AuthRequest extends \App\Http\Requests\Api\CommonRequest
{
    public function rules()
    {
        return [
            'grant_type'    => 'required',
            'client_id'     => 'required',
            'client_secret' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'required' => '[E00002]:attribute は必須項目です。',
        ];
    }
}
