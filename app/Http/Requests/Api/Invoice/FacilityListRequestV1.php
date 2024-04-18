<?php

namespace App\Http\Requests\Api\Invoice;

class FacilityListRequestV1 extends \App\Http\Requests\Api\CommonRequest
{
    public function rules()
    {
        return [
            'terminal_number' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'required' => '[E00002]:attribute は必須項目です。',
            'numeric'  => '[E00004]:attribute を正しい形式で指定してください。',
        ];
    }
}
