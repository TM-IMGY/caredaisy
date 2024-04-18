<?php

namespace App\Http\Requests\GroupHome\ResultInfo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResultInfoGetServiceCodeRequest extends FormRequest
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
            'year' => 'bail|integer|between:2020,3000|required',
            'month' => 'bail|integer|between:1,12|required',
            'service_type_code' => 'bail|string|between:2,4|required'
        ];
    }

    public function messages()
    {
        return [
            'year.integer' => 'A year is integer',
            'year.between' => 'A year is between 2020 - 3000',
            'year.required' => 'A year is required',
            'month.integer' => 'A month is integer',
            'month.between' => 'A month is between 1 - 12',
            'month.required' => 'A month is required',
            'service_type_code.string' => 'A service_type_code is string',
            'service_type_code.between' => 'A service_type_code is between 2 - 4',
            'service_type_code.required' => 'A service_type_code is required',
        ];
    }
}
