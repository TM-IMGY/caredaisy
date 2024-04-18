<?php

namespace App\Http\Requests\Api\Hospitac;

class FileUploadRequest extends \App\Http\Requests\Api\CommonRequest
{
    public function rules()
    {
        return [
            'file_data' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'required' => '[E00002]:attribute は必須項目です。',
        ];
    }
}
