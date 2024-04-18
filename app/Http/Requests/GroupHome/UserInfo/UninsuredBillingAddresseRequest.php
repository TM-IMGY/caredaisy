<?php

namespace App\Http\Requests\GroupHome\UserInfo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\CareDaisyBaseFormRequest;

class UninsuredBillingAddresseRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('facility_user_id') && $this->facility_user_id != null) {
            return $this->authorizeFacilityUserId($this->facility_user_id);
        }

        return false;
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
       // sample
        $rules = [
            // 'name' => 'required',
            // 'bank_number' => 'required',
            // 'branch_number' => 'required',
            // 'bank_account' => 'required',
            // 'depositor' => 'required',
        ];

        return $rules;
    }
    // 支払い方法が引き落としの場合に必須のバリデーションを行う
    // public function withValidator($validator) {
    //     $validator->after(function ($validator) {
    //         if($this->filled(['payment_method'])) {
    //             if($this->input('payment_method') == 1) {
    //                 $validator->errors()->add('割引額', '合計金額より小さい額を指定してください．');
    //             }
    //         }
    //     });
    // }

    public function messages()
    {
        return [
        ];
    }

    public function attributes()
    {
        return [
            'name' => '氏名',
            'bank_number' => '銀行番号',
            'branch_number' => '支店番号',
            'bank_account' => '口座情報',
            'depositor' => '預金者名（カナ）',
        ];
    }
}
