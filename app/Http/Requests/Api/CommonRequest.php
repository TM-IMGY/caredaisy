<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CommonRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        foreach ($validator->errors()->all() as $error) {
            // 先頭のエラーのみ返す
            throw new HttpResponseException(response()->validationError($error));
        }
    }

    public function authorize()
    {
        return true;
    }
}
