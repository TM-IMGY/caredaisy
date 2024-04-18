<?php

namespace App\Http\Requests\GroupHome\CarePlanInfo;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\CareDaisyBaseFormRequest;
use App\Rules\TextColumn;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\CarbonImmutable;

class ServicePlan1RegisterRequest extends CareDaisyBaseFormRequest
{
    const LIMIT_START_DATE = '2000-03-31';
    const LIMIT_END_DATE   = '2100-01-01';

    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize()
    {
        // 新規登録、更新処理のメソッドが同じ処理のためパラメーターが存在しないパターンが有
        // 上記のケースで「権限チェックが新規、後続処理が更新」のケースが存在するため
        // 別途回収する必要があります
        return $this->authorizeFacilityUserId($this->facility_user_id) &&
                $this->authorizeServicePlanId($this->service_plan_id, $this->facility_user_id) &&
                $this->authorizeServicePlan1Id($this->first_service_plan_id);
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
     * @return array
     */
    public function rules()
    {
        $rules = [
            'facility_user_id'        => 'bail|required|integer',
            'plan_start_period'       => 'bail|required|date|after:'.self::LIMIT_START_DATE.'|before:'.self::LIMIT_END_DATE,
            'plan_end_period'         => 'bail|required',
            'status'                  => 'bail|required|integer',
            'certification_status'    => 'bail|required|integer',
            'care_level_name'         => 'bail|required',
            'place'                   => 'nullable|max:255',
            'remarks'                 => ['nullable', 'string', new TextColumn],
            'independence_level'      => 'nullable|integer',
            'dementia_level'          => 'nullable|integer',
            'plan_division'           => 'bail|required|integer',
            'title1'                  => 'nullable|max:255',
            'content1'                => ['nullable', 'string', new TextColumn],
            'title2'                  => 'nullable|max:255',
            'content2'                => ['nullable', 'string', new TextColumn],
            'title3'                  => 'nullable|max:255',
            'content3'                => ['nullable', 'string', new TextColumn],
            'living_alone'            => 'boolean',
            'handicapped'             => 'boolean',
            'other'                   => 'boolean',
            'other_reason'            => 'nullable|max:30',
            'start_date'              => 'bail|required|date_format:Y-m-d|after:'.self::LIMIT_START_DATE.'|before:'.self::LIMIT_END_DATE.'|before_or_equal:end_date',
            'end_date'                => 'bail|required|date_format:Y-m-d|after:'.self::LIMIT_START_DATE.'|before:'.self::LIMIT_END_DATE,
            'first_plan_start_period' => 'required | date | after: 2000-03-31 23:59:59 | before: 2100-01-01 00:00:00',
            'recognition_date'                    => [
                'bail',
                'nullable',
                'regex:/^[0-9\-]+$/',
                'date',
                'before:'.self::LIMIT_END_DATE,
                'after:'.self::LIMIT_START_DATE
            ],

            'care_period_start'                   => [
                'bail',
                'nullable',
                'regex:/^[0-9\-]+$/',
                'date',
                'before:'.self::LIMIT_END_DATE,
                'after:'.self::LIMIT_START_DATE
            ],

            'care_period_end'                     => [
                'bail',
                'nullable',
                'regex:/^[0-9\-]+$/',
                'date',
                'after_or_equal:care_period_start',
                'before:'.self::LIMIT_END_DATE
            ],
        ];

        if ($this->has('service_plan_id') && $this->service_plan_id != "") {
            $rules['service_plan_id'] = 'bail | required | integer';
        }

        if ($this->has('first_service_plan_id') && $this->first_service_plan_id != "") {
            $rules['first_service_plan_id'] = 'bail | required | integer';
        }

        // 確定日
        if ($this->status == "3") {
            // ステータスが「確定」の場合
            $rules['fixed_date'] = ['bail','required','date','regex:/^(19[0-9]{2}|20[0-9]{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/'];
        } else {
            $rules['fixed_date'] = 'bail | nullable | date';
        }

        // 交付日時・同意者
        if ($this->status == "4") {
            // ステータスが「交付済み」の場合
            $rules['delivery_date'] = ['bail','required','date','regex:/^(19[0-9]{2}|20[0-9]{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])T([01][0-9]|2[0-3]):[0-5][0-9]$/'];
            $rules['consent'] = 'bail | required | max:255';
        } else {
            $rules['delivery_date'] = 'bail | nullable | date';
            $rules['consent'] = 'nullable | max:255';
        }

        return $rules;
    }

    /**
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            // 有効期間チェック
            if ( !$validator->errors()->has('start_date') && !is_null($this->care_period_start)) {
                $approvalStart = new CarbonImmutable($this->care_period_start); // 認定情報有効開始日
                $approvalEnd   = new CarbonImmutable($this->care_period_end); // 認定情報有効終了日
                $carePlanStart = new CarbonImmutable($this->start_date); // ケアプラン開始日
                $carePlanEnd = new CarbonImmutable($this->end_date); // ケアプラン終了日

                if ( !$carePlanStart->between($approvalStart, $approvalEnd)
                || !$carePlanEnd->between($approvalStart, $approvalEnd)) {
                    $validator->errors()->add('start_date', 'ケアプラン期間は認定情報の有効期間内で入力してください。');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'required'                      => ':attribute は必須項目です。',
            'integer'                       => ':attribute の形式が不正です。',
            'regex'                         => ':attribute の年月日が不正です。',
            'after_or_equal'                => ':attribute が有効開始日より過去の日付です。',
            'max'                           => ':attribute は :max文字（桁）以下で入力してください。',
            'date_format'                   => ':attribute の形式が不正です。',
            'before'                        => ':attribute は2099年12月以前の年月を入力してください。',
            'after'                         => ':attribute は2000年4月以降の年月を入力してください。',
            'before_or_equal'               => 'ケアプラン期間開始日とケアプラン期間終了日の関係性に誤りがあるので確認してください。',
            'after_or_equal'                => '認定情報の有効開始日と有効終了日の関係性に誤りがあるので確認してください',
            'date'                          => '日付を入力してください',
        ];
    }

    public function attributes()
    {
        return [
            'facility_user_id'              => '利用者選択',
            'plan_start_period'             => '作成日',
            'plan_end_period'               => '作成者',
            'status'                        => 'ステータス',
            'certification_status'          => '認定状況',
            'recognition_date'              => '認定年月日',
            'care_period_start'             => '有効開始日',
            'care_period_end'               => '有効終了日',
            'care_level_name'               => '要介護度',
            'place'                         => '場所',
            'remarks'                       => '備考',
            'independence_level'            => '障害高齢者自立度',
            'dementia_level'                => '認知症高齢者自立度',
            'plan_division'                 => '計画書区分',
            'title1'                        => '入力タイトル1',
            'content1'                      => '目標内容1',
            'title2'                        => '入力タイトル2',
            'content2'                      => '目標内容2',
            'title3'                        => '入力タイトル3',
            'content3'                      => '目標内容3',
            'living_alone'                  => '一人暮らし',
            'handicapped'                   => '家族等が障害',
            'other'                         => 'その他',
            'other_reason'                  => 'その他理由',
            'fixed_date'                    => '確定日',
            'delivery_date'                 => '交付日',
            'consent'                       => '同意者',
            'start_date'                    => 'ケアプラン期間開始日',
            'end_date'                      => 'ケアプラン期間終了日',
            'first_plan_start_period'       => '初回施設サービス計画作成日',
        ];
    }
}
