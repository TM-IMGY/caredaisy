<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\CommonRequest;

class WatchdogRequest extends CommonRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'target_function' => 'required'
        ];
    }
    
    public function messages()
    {
        return [
            'required' => ':attribute は必須項目です。'
        ];
    }
}
