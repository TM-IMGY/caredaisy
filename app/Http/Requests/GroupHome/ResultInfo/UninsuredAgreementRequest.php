<?php

namespace App\Http\Requests\GroupHome\ResultInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\StayOutManagement;

class UninsuredAgreementRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (
            $this::has('facility_id') && $this->facility_id != null
            && $this::has('facility_user_id') && $this->facility_user_id != null
        ) {
            return $this->authorizeFacilityId($this->facility_id) &&
                $this->authorizeFacilityIdFUserId($this->facility_user_id, $this->facility_id);
        }
        return false;
    }

    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('この操作は許可されていません。'),
        ], 400);
        throw new HttpResponseException($res);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // sample
        $rules = [];

        return $rules;
    }

    public function messages()
    {
        return [
            'required'      => ':attribute は必須項目です。',
            'date_format'   => ':attribute の形式が不正です。',
            'min'           => ':attribute は :min文字（桁）以上で入力してください。',
            'max'           => ':attribute は :max文字（桁）以下で入力してください。',
        ];
    }

    public function attributes()
    {
        return [];
    }

    // API系の共通クラス作って、そこに移動したほうがよさそう
    protected function failedValidation(Validator $validator)
    {
        $res = response()->json([
            'errors' => $validator->errors(),
        ], 400);
        throw new HttpResponseException($res);
    }
}
