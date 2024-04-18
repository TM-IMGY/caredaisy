<?php

namespace App\Http\Requests\GroupHome\FacilityInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class iUninsuredItemHistoryRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('uninsured_item_id') && $this->uninsured_item_id != null) {
            // 新規か更新かのチェック
            if ($this::has('id') && $this->id != null) {
                return $this->authorizeUninsuredItemId($this->uninsured_item_id) &&
                    $this->authorizeUninsuredItemHistoryId($this->id);
            } else {
                return $this->authorizeUninsuredItemId($this->uninsured_item_id);
            }
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
        $rules = [
            'item'              => 'required | max:255',
            'unit_cost'         => 'nullable | integer',
            'unit'              => 'required | integer',
            'set_one'           => 'nullable | boolean',
            'fixed_cost'        => 'nullable | boolean',
            'variable_cost'     => 'nullable | boolean',
            'welfare_equipment' => 'nullable | boolean',
            'meal'              => 'nullable | boolean',
            'daily_necessary'   => 'nullable | boolean',
            'hobby'             => 'nullable | boolean',
            'escort'            => 'nullable | boolean',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'required'              => ':attribute は必須項目です。',
            'integer'               => ':attribute の形式が不正です。',
            'max'                   => ':attribute の文字数(桁数)が不正です。',
        ];
    }

    public function attributes()
    {
        return [
            'item'              => '品目',
            'unit_cost'         => '単価',
            'unit'              => '単位',
            'set_one'           => '毎日1を設定',
            'fixed_cost'        => '固定費',
            'variable_cost'     => '変動費',
            'welfare_equipment' => '福祉用具',
            'meal'              => '食事',
            'daily_necessary'   => '日用品',
            'hobby'             => '趣味・娯楽',
            'escort'            => '同行・同伴',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res = response()->json([
            'errors' => $validator->errors(),
        ], 400);
        throw new HttpResponseException($res);
    }
}
