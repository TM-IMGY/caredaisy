<?php

namespace App\Http\Requests\GroupHome\ResultInfo;

use Illuminate\Foundation\Http\FormRequest;

class GetInfoTableRequest extends FormRequest
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
        ];
    }

    public function messages()
    {
        return [
        ];
    }
}
