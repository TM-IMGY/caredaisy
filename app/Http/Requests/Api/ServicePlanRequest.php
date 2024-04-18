<?php

namespace App\Http\Requests\Api;

use App\Rules\MbSize;
use App\Rules\MaxLength;

class ServicePlanRequest extends CommonRequest
{
    public function rules()
    {
        return [
            'facility_number' => [
                'required',
                'regex:/^[A-Z0-9]+$/',
                new MbSize(10, "[E00003]:attribute は10文字で入力してください。"),
            ],
            'care_daisy_facility_user_id' => [
                'required',
                'numeric',
                new MaxLength(20, "[E00007]:attribute は20文字以内で入力してください。"),
            ],
            'service_plan_id' => [
                'numeric',
                new MaxLength(20, "[E00007]:attribute は20文字以内で入力してください。"),
            ],
            'paging_no' => [
                'in:,-1,+1',
                function($attribute, $value, $fail){
                    $input = $this->all();
                    $sid = $input['service_plan_id'] ?? null;
                    if (empty($sid)) {
                        $fail('[E00002]:attribute は必須項目です。');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'required' => '[E00002]:attribute は必須項目です。',
            'numeric'  => '[E00004]:attribute を正しい形式で指定してください。',
            'regex'    => '[E00004]:attribute を正しい形式で指定してください。',
            'in'       => '[E00004]:attribute を正しい形式で指定してください。',
        ];
    }
}
