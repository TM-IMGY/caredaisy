<?php

namespace App\Http\Requests\GroupHome\Service;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServiceResultGetDataRequest extends CareDaisyBaseFormRequest
{
    public function authorize(): bool
    {
        if ($this::has('facility_user_id') && $this->facility_user_id != null) {
            return $this->authorizeFacilityUserId($this->facility_user_id);
        }
        return false;
    }

    protected function failedAuthorization()
    {
        $res = response()->json(['errors' => array('この操作は許可されていません。')], 400);
        throw new HttpResponseException($res);
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors();
        throw new HttpResponseException(response()->json($error, 400));
    }

    public function rules(): array
    {
        return [
            'facility_user_id' => 'bail|integer|required',
            'year' => 'bail|integer|date_format:Y|required',
            'month' => 'bail|integer|date_format:m|required'
        ];
    }

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
