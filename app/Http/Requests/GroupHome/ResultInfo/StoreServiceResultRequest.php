<?php

namespace App\Http\Requests\GroupHome\ResultInfo;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceResultRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * falseを返せば強制認証エラー
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // todo 一時的にリクエストにおける認証を開放している
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'facility_user_id' => 'bail|required|numeric'
            ,'year' => 'bail|required|numeric'
            ,'month' => 'bail|required|numeric'
            ,'service_offer' => 'bail|required'
        ];
    }

    public function messages()
    {
        return [
            // 'title.required' => 'A title is r',
            // 'body.required'  => 'A message is r',
        ];
    }
}
