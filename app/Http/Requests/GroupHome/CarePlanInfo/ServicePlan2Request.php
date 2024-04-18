<?php

namespace App\Http\Requests\GroupHome\CarePlanInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServicePlan2Request extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('service_plan_id') && $this->service_plan_id != null) {
            return $this->authorizeServicePlanId($this->service_plan_id);
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
