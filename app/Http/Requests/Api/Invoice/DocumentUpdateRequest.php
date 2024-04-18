<?php

namespace App\Http\Requests\Api\Invoice;

class DocumentUpdateRequest extends \App\Http\Requests\Api\CommonRequest
{
    public function rules()
    {
        return [
            'documents' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'required' => '[E00002]:attribute は必須項目です。',
        ];
    }
}
