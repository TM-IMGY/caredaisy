<?php

namespace App\Http\Requests\GroupHome\CarePlanInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServicePlanGetCarePlanPeriodRequest extends CareDaisyBaseFormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('facility_user_id') && $this->facility_user_id != null) {
            return $this->authorizeFacilityUserId($this->facility_user_id);
        }
        return false;
    }

    /**
     * 権限がなかった場合の処理を上書きする
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('この操作は許可されていません。')
        ], 400);
        throw new HttpResponseException($res);
    }

    /**
     * @param Validator $validator
     * @see FormRequest::failedValidation()
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        throw new HttpResponseException(response()->json($errors, 400));
    }

    /**
     * @return array
     */
    public function rules() : array
    {
        return [
            'facility_user_id' => 'bail|integer|required'
        ];
    }

    /**
     * @return array
     */
    public function messages() : array
    {
        return [
            'integer' => ':attribute is integer',
            'required' => ':attribute is required'
        ];
    }
}
