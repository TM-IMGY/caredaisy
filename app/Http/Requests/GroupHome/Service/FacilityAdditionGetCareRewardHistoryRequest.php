<?php

namespace App\Http\Requests\GroupHome\Service;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FacilityAdditionGetCareRewardHistoryRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('facility_id') && $this->facility_id != null &&
            $this::has('id') && $this->id != null
        ) {
            return $this->authorizeFacilityId($this->facility_id)
                && $this->authorizeCareRewardHistoryId($this->id, $this->facility_id);
        }
        return false;
    }

    /**
     * 権限がなかった場合の処理を上書きする
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
            'id' => 'bail|integer|required',
            'facility_id' => 'bail|integer|required',
            'service_type_code_id' => 'bail|integer|required',
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'integer' => ':attribute is integer',
            'required' => ':attribute is required'
        ];
    }
}
