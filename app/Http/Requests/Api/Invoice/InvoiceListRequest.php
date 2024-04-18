<?php

namespace App\Http\Requests\Api\Invoice;

class InvoiceListRequest extends \App\Http\Requests\Api\CommonRequest
{
    public function rules()
    {
        return [
            'target' => 'required|numeric',
            'ym'     => 'regex:/^[0-9]{6}$/',
        ];
    }

    public function messages()
    {
        return [
            'required' => '[E00002]:attribute は必須項目です。',
            'numeric'  => '[E00004]:attribute を正しい形式で指定してください。',
            'regex'    => '[E00004]:attribute を正しい形式で指定してください。',
        ];
    }
}
