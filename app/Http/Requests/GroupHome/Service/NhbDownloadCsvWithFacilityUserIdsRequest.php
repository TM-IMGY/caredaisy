<?php

namespace App\Http\Requests\GroupHome\Service;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class NhbDownloadCsvWithFacilityUserIdsRequest extends CareDaisyBaseFormRequest
{

    public function authorize()
    {
        if ($this::has('facility_user_ids') && $this->facility_user_ids != null) {
            return $this->authorizeFacilityUserIds($this->facility_user_ids);
        }
        return false;
    }

    /**
     * 権限がなかった場合の処理を上書き
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('この操作は許可されていません。'),
        ], 400);
        throw new HttpResponseException($res);
    }

    /**
     * @param Validator $validator
     * @throw HttpResponseException
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
    public function rules()
    {
        return [
            'facility_user_ids' => 'array|bail|required',
            'year' => 'bail|integer|date_format:Y|required',
            'month' => 'bail|integer|date_format:m|required'
        ];
    }

    public function messages()
    {
        return [
            'array' => ':attribute is array',
            'integer' => ':attribute is integer',
            'required' => ':attribute is required',
            'year.date_format' => ':attribute is date_format Y',
            'month.date_format' => ':attribute is date_format m',
        ];
    }
}
