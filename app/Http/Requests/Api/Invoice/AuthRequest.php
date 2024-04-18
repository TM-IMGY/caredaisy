<?php

namespace App\Http\Requests\Api\Invoice;

class AuthRequest extends \App\Http\Requests\Api\CommonRequest
{
    public function rules()
    {
        return [
            'grant_type'    => 'required|in:client_credentials',
            'client_id'     => 'required|numeric',
            'client_secret' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'required' => '[E00002]:attribute は必須項目です。',
            'numeric'  => '[E00004]:attribute を正しい形式で指定してください。',
            'in'       => '[E00004]:attribute を正しい形式で指定してください。',
        ];
    }
}
