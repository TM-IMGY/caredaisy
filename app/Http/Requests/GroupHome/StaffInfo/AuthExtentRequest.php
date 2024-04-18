<?php

namespace App\Http\Requests\GroupHome\StaffInfo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class AuthExtentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * 権限がなかった場合の処理を上書き
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('選択しているスタッフの編集は許可されていません。'),
        ], 400);
        throw new HttpResponseException($res);
    }

    /**
     * バリデーション失敗時の処理を上書き
     *
     * @param Validator $validator
     * @throw HttpResponseException
     * @see FormRequest::failedValidation()
     */
    protected function failedValidation(Validator $validator)
    {
        // messageBagを単配列化
        $flatArray = \Arr::flatten($validator->errors()->toArray());
        // 重複しているメッセージを除外
        $message = array_unique($flatArray);
        $res = response()->json([
            'errors' => $message,
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
            'start_date' => 'required',
        ];
    }
    public function withValidator(Validator $validator)
    {
        $validator->sometimes('auth', 'required', function ($input) {
            return !($input->planner or $input->claimant or $input->administrator);
        });
    }
    
    public function messages()
    {
        return [
            'required'      => ':attribute は必須項目です。',
        ];
    }

    public function attributes()
    {
        return [
            'start_date' => '権限開始日',
            'auth' => '権限設定',
        ];
    }
}
