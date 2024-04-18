<?php

namespace App\Http\Requests\GroupHome\Service;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * 特別診療費コード取得のフォームリクエスト。
 */
class SpecialMedicalCodeGetRequest extends CareDaisyBaseFormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('facility_id') && $this->facility_id != null) {
            return $this->authorizeFacilityId($this->facility_id);
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
     * @throw HttpResponseException
     * @see FormRequest::failedValidation()
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
            'facility_id' => 'bail|integer|required',
            'service_type_code' => 'bail|string|required',
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
            'string' => ':attribute is string',
            'year.date_format' => ':attribute is date_format Y',
            'month.date_format' => ':attribute is date_format m'
        ];
    }
}
