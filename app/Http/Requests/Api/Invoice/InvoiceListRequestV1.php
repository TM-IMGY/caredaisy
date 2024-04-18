<?php

namespace App\Http\Requests\Api\Invoice;

use App\Rules\Ym6digits;

class InvoiceListRequestV1 extends \App\Http\Requests\Api\CommonRequest
{
    public function rules()
    {
        return [
            'terminal_number' => 'required|numeric',
            'target' => 'required|digits:1',
            'ym'     => 'numeric', new Ym6digits,
        ];
    }

    public function messages()
    {
        return [
            'required' => '[E00002]:attribute は必須項目です。',
            'numeric'  => '[E00004]:attribute を正しい形式で指定してください。',
            'digits'   => '[E00004]:attribute を正しい形式で指定してください。',
        ];
    }
}
