<?php

namespace App\Http\Requests\GroupHome\CarePlanInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServicePlan1GetHistoryRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 権限チェック
        if ($this::has('facility_user_id') && $this->facility_user_id != null) {
            if ($this::has('service_plan_id') && $this->service_plan_id != null) {
                return $this->authorizeFacilityUserId($this->facility_user_id) &&
                    $this->authorizeServicePlanId($this->service_plan_id, $this->facility_user_id);
            }
            if (!$this::has('service_plan_id')) {
                return $this->authorizeFacilityUserId($this->facility_user_id);
            }
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
        return [
            //
        ];
    }
}
