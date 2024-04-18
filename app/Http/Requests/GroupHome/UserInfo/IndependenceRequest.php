<?php

namespace App\Http\Requests\GroupHome\UserInfo;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

class IndependenceRequest extends CareDaisyBaseFormRequest
{
    const VALID_START_DATE = '2000-03-31';
    const VALID_END_DATE = '2100-01-01';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (($this::has('facility_user_id') && $this->facility_user_id != null)
                && ($this::has('saveGetIdIndependence') && $this->saveGetIdIndependence !== null)) {
            if ($this->saveGetIdIndependence == 0) {
                // 新規登録処理の場合、facility_user_idのみ権限チェック
                return $this->authorizeFacilityUserId($this->facility_user_id);
            } else {
                return $this->authorizeFacilityUserId($this->facility_user_id) &&
                    $this->authorizeUserIndependenceInformationId($this->saveGetIdIndependence);
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
            'errors' => array('選択している利用者の編集は許可されていません。'),
        ], 400);
        throw new HttpResponseException($res);
    }

    protected function failedValidation(Validator $validator)
    {
        $res = response()->json([
            'errors' => $validator->errors(),
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
            'independentIndependence'       => 'required',
            'dementiaIndependence'          => 'required',
            'judgmentDateIndependence'      => [
                'bail',
                'required',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],
            'judgeIndependence'             => 'required',
            'facility_user_id'              => 'required | integer',
            'saveGetIdIndependence'         => 'required | integer',
        ];
    }

    public function messages()
    {
        return [
            'required'                              => ':attribute は必須項目です。',
            'integer'                               => ':attribute の形式が不正です。',
            'regex'                                 => ':attribute は半角で入力してください',
            'date'                           => ':attribute の形式が不正です。',
            'before'                                => '2099年12月以前の年月を入力してください',
            'after'                                 => '2000年4月以降の年月を入力してください'
        ];
    }

    public function attributes()
    {
        return [
            'facility_user_id'              => '利用者選択',
            'independentIndependence'       => '障害高齢者自立度',
            'dementiaIndependence'          => '認知症度 ',
            'judgmentDateIndependence'      => '判断日',
            'judgeIndependence'             => '判断者',
            'saveGetIdIndependence'         => '自立度情報ID',
        ];
    }

    /**
     * バリデータインスタンスの設定
     * バリデータに未来日付チェックを追加する
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 判断日にエラーがない場合、未来日チェックを行う
            if (!$validator->errors()->has('judgmentDateIndependence')) {
                $dt = new Carbon($this->input('judgmentDateIndependence'));
                // 判断日が未来日であるか判定
                if ($dt->isFuture()) {
                    // 判断日が未来日だった場合、エラーメッセージを設定する
                    $validator->errors()->add(
                        'judgmentDateIndependence', '未来の日付は入力できません'
                    );
                }
            }
        });
    }
}
