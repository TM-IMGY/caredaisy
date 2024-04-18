<?php

namespace App\Http\Requests\Api\Invoice;

class InvoiceUpdateRequest extends \App\Http\Requests\Api\CommonRequest
{
    public function rules()
    {
        return [
            'invoices' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'required' => '[E00002]:attribute は必須項目です。',
        ];
    }
}
