<?php

namespace App\Http\Requests\GroupHome\StaffInfo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\CareDaisyBaseFormRequest;

class StaffRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->authorizeFacilityUserId();
        return true;
    }
    
    /**
     * 権限がなかった場合の処理を上書き
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('選択している利用者の編集は許可されていません。'),
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
            'name' => 'required',
            'name_kana' => 'required',
            'employee_number' => 'required',
            'date_of_employment' => 'required',
        ];
    }
    public function withValidator(Validator $validator)
    {
        $validator->sometimes('password', 'required', function ($input) {
            return (is_null($input->staff_history_id) or $input->password_changed);
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
            'name' => '氏名',
            'name_kana' => 'フリガナ',
            'employee_number' => '社員番号',
            'date_of_employment' => '入社日',
            'password' => 'パスワード',
        ];
    }
}
