<?php

namespace App\Http\Requests\GroupHome\Service;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * 公費の取得のリクエスト。
 */
class PublicExpenseGetRequest extends CareDaisyBaseFormRequest
{
    /**
     * @param Validator $validator
     * @see FormRequest::failedValidation()
     */
    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors();
        throw new HttpResponseException(response()->json($error, 400));
    }

    /**
     */
    public function rules(): array
    {
        return [
            'public_expense_information_id' => 'bail|integer|required',
        ];
    }

    public function messages(): array
    {
        return [
            'integer' => ':attribute is integer',
            'required' => ':attribute is required'
        ];
    }
}
