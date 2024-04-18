<?php

namespace App\Http\Requests\GroupHome\Service;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServiceResultSaveRequest extends CareDaisyBaseFormRequest
{
    public function authorize(): bool
    {
        if ($this::has('facility_id') && $this->facility_id != null &&
            $this::has('facility_user_id') && $this->facility_user_id != null
        ) {
            return $this->authorizeFacilityId($this->facility_id) &&
                $this->authorizeFacilityUserId($this->facility_user_id);
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
            'facility_id' => 'bail|integer|required',
            'facility_user_id' => 'bail|integer|required',
            'service_results' => 'bail|array',
            'service_results.*.burden_limit' => 'bail|integer|nullable',
            'service_results.*.date_daily_rate' => 'bail|min:30|max:31|required|string',
            'service_results.*.date_daily_rate_one_month_ago' => 'bail|min:30|max:31|required|string',
            'service_results.*.date_daily_rate_two_month_ago' => 'bail|min:30|max:31|required|string',
            'service_results.*.service_count_date' => 'bail|integer|required',
            'service_results.*.service_item_code_id' => 'bail|integer|required',
            'service_results.*.special_medical_code_id' => 'bail|integer|nullable',
            'year' => 'bail|integer|date_format:Y|required',
            'month' => 'bail|integer|date_format:m|required'
        ];
    }

    public function messages(): array
    {
        return [
            'array' => ':attribute が配列ではありません',
            'integer' => ':attribute が数値ではありません',
            'required' => ':attribute がありません',
            'year.date_format' => ':attribute が Y 形式ではありません',
            'month.date_format' => ':attribute が m 形式ではありません'
        ];
    }

    public function attributes(): array
    {
        return [
            'facility_id' => '事業所ID',
            'facility_user_id' => '施設利用者ID',
            'service_results' => 'サービス実績',
            'year' => '対象年',
            'month' => '対象月'
        ];
    }
}
