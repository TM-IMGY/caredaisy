<?php

namespace App\Http\Requests\GroupHome\Service;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServiceResultGetBenefitBillingRequest extends CareDaisyBaseFormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'facility_user_id' => 'bail|integer|required',
            'year' => 'bail|integer|date_format:Y|required',
            'month' => 'bail|integer|date_format:m|required'
        ];
    }

    public function messages()
    {
        return [
            'integer' => ':attribute is integer',
            'required' => ':attribute is required',
            'year.date_format' => ':attribute is date_format Y',
            'month.date_format' => ':attribute is date_format m'
        ];
    }
}
