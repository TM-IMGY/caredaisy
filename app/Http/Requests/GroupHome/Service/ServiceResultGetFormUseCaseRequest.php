<?php

namespace App\Http\Requests\GroupHome\Service;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServiceResultGetFormUseCaseRequest extends CareDaisyBaseFormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        if (
            $this::has('facility_id') && $this->facility_id != null
            && $this::has('facility_user_id') && $this->facility_user_id != null
        ) {
            return $this->authorizeFacilityId($this->facility_id)
                && $this->authorizeFacilityUserId($this->facility_user_id);
        }

        return false;
    }

    /**
     * 権限がなかった場合の処理を上書きする。
     */
    protected function failedAuthorization()
    {
        $res = response()->json(['errors' => array('この操作は許可されていません。')], 400);
        throw new HttpResponseException($res);
    }

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
     * @return array
     */
    public function rules(): array
    {
        return [
            'facility_id' => 'bail|integer|required',
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
