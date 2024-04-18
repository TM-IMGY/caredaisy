<?php

namespace App\Http\Requests\GroupHome\ResultInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;

class ResultInfoGetApprovedUsersRequest extends CareDaisyBaseFormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        ];
    }

    public function messages()
    {
        return [
        ];
    }
}
