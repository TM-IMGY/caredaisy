<?php

namespace App\Http\Requests\GroupHome\Service;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServiceCodeGetServiceCodeRequest extends FormRequest
{
    /**
     * @param Validator $validator
     * @throw HttpResponseException
     * @see FormRequest::failedValidation()
     */
    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->toArray();
        throw new HttpResponseException(
            response()->json($error, 400)
        );
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'year' => 'bail|integer|date_format:Y|required',
            'month' => 'bail|integer|date_format:m|required',
            'service_type_code' => 'bail|between:2,4|required|string'
        ];
    }

    public function messages()
    {
        return [
            'integer' => ':attribute is integer',
            'required' => ':attribute is required',
            'string' => ':attribute is string',
            'year.date_format' => ':attribute is date_format Y',
            'month.date_format' => ':attribute is date_format m'
        ];
    }
}
