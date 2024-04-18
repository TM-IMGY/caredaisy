<?php

namespace App\Http\Requests\GroupHome\Service;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * サービスコード(特定入所者サービス)のリクエスト。
 */
class ServiceCodeListIncompetentResidentsRequest extends FormRequest
{
    /**
     * @param Validator $validator
     * @see FormRequest::failedValidation()
     * @throw HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->toArray();
        throw new HttpResponseException(response()->json($error, 400));
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'year' => 'bail|integer|date_format:Y|required',
            'month' => 'bail|integer|date_format:m|required'
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'integer' => ':attribute is integer',
            'required' => ':attribute is required',
            'year.date_format' => ':attribute is date_format Y',
            'month.date_format' => ':attribute is date_format m'
        ];
    }
}
