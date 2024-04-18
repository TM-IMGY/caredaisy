<?php

namespace App\Http\Requests\GroupHome\CarePlanInfo;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use Carbon\Carbon;

class ServicePlan2RegisterRequest extends CareDaisyBaseFormRequest
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
        if ($this::has('service_plan_id') && $this->service_plan_id != null &&
            $this::has('service_plan2') && $this->service_plan2 != null
        ) {
            return $this->authorizeServicePlanId($this->service_plan_id) &&
                $this->authorizeServicePlan2Id($this->service_plan2, $this->service_plan_id);
        }
        return false;
    }

    /**
     * 権限がなかった場合の処理を上書き
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('選択している利用者情報の編集は許可されていません。'),
        ], 400);
        throw new HttpResponseException($res);
        // throw new \Illuminate\Auth\Access\AuthorizationException('この操作は許可されていません。');
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
        $customRule = function ($attribute, $value, $fail) {
            $inputData = $this->all();
        };

        $rules = [
            'service_plan_id'                       => 'required',
            'second_service_plan_id'                => 'required',
            'need'                                  => 'array',
            'need.*.second_service_plan_id'         => 'required | integer',
            'need.*.service_plan_need_id'           => 'nullable | integer',
            'need.*.needs'                          => 'nullable',
            'need.*.sort'                           => 'required | integer',
            'long'                                  => 'array',
            'long.*.service_plan_need_id'           => 'nullable | integer',
            'long.*.service_long_plan_id'           => 'nullable | integer',
            'long.*.goal'                           => 'nullable',
            'long.*.sort'                           => 'required | integer',
            'long.*.task_start'                     =>
            [
                'nullable',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],
            'long.*.task_end'                       =>
            [
                'nullable',
                'date',
                'after_or_equal:long.*.task_start',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],
            'short'                                 => 'array',
            'short.*.service_long_plan_id'          => 'nullable | integer',
            'short.*.service_short_plan_id'         => 'nullable | integer',
            'short.*.goal'                          => 'nullable',
            'short.*.sort'                          => 'required | integer',
            'short.*.task_start'                    =>
            [
                'nullable',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],
            'short.*.task_end'                      =>
            [
                'nullable',
                'date',
                'after_or_equal:short.*.task_start',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],
            'support'                               => 'array',
            'support.*.service_short_plan_id'       => 'nullable | integer',
            'support.*.service_plan_support_id'     => 'nullable | integer',
            'support.*.service'                     => 'nullable',
            'support.*.sort'                        => 'required | integer',
            'support.*.task_start'                  =>
            [
                'nullable',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],
            'support.*.task_end'                    =>
            [
                'nullable',
                'date',
                'after_or_equal:support.*.task_start',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE
            ],
            'support.*.frequency'                   => 'nullable',
            'support.*.staff'                       => 'nullable',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'required'          => ':attribute は必須項目です。',
            'integer'           => ':attribute の形式が不正です。',
            'after_or_equal'    => '開始日と終了日の関係性に誤りがあるので確認してください',
            'max'               => ':attribute は :max文字（桁）以下で入力してください。',
            'before'            => '2099年12月以前の年月を入力してください',
            'after'             => '2000年4月以降の年月を入力してください'
        ];
    }

    public function attributes()
    {
        return [
            'need.*.needs'              => '生活全般の課題',
            'need.*.sort'               => '並び順',
            'long.*.goal'               => '長期目標',
            'long.*.task_start'         => '開始日',
            'long.*.task_end'           => '終了日',
            'long.*.sort'               => '並び順',
            'short.*.goal'              => '短期目標',
            'short.*.task_start'        => '開始日',
            'short.*.task_end'          => '終了日',
            'short.*.sort'              => '並び順',
            'support.*.task_start'      => '開始日',
            'support.*.task_end'        => '終了日',
            'support.*.sort'            => '並び順',
            'support.*.staff'           => '担当者',
            'support.*.frequency'       => '頻度、曜日',
            'support.*.service'         => 'サービス内容',
        ];
    }

    public function validationData()
    {
        $inputData = $this->all();
        $needsList = $inputData['service_plan2']['need_list'];

        foreach ($needsList as $key => $list) {
            foreach ($needsList[$key]['long_plan_list'] as $value) {
                $longsList[] = $value;
            }
            unset($needsList[$key]['long_plan_list']);
        }

        foreach ($longsList as $key => $long) {
            foreach ($longsList[$key]['short_plan_list'] as $value) {
                $shortsList[] = $value;
            }
            unset($longsList[$key]['short_plan_list']);
        }

        foreach ($shortsList as $key => $short) {
            foreach ($shortsList[$key]['support_list'] as $value) {
                $supportsList[] = $value;
            }
            unset($shortsList[$key]['support_list']);
        }

        $allList = [];
        $allList['service_plan_id'] = $inputData['service_plan_id'];
        $allList['second_service_plan_id'] = $inputData['service_plan2']['second_service_plan_id'];
        $allList['need'] = $needsList;
        $allList['long'] = $longsList;
        $allList['short'] = $shortsList;
        $allList['support'] = $supportsList;
        return $allList;
    }

    public function withValidator($validator)
    {
        // ケアプラン期間内の日付であることをチェックする
        $validator->after(function ($validator) {
            if (count($validator->errors()) === 0) {
                // リクエストからケアプラン開始日と終了日を取得し、日付型に変換する
                $start = new Carbon($this->input('care_plan_period_start'));
                $end = new Carbon($this->input('care_plan_period_end'));
                // validation用にデータを変換
                $req = $this->validationData();
                // 援助目標長期・援助目標短期・援助内容のリストを配列にして処理を共通化する
                $arrayList = [$req['long'], $req['short'], $req['support']];
                foreach ($arrayList as $array) {
                    // 援助目標長期または援助目標短期または援助内容の配列の件数分ループをする
                    foreach ($array as $data) {
                        // 開始日と終了日を日付チェックの対象とする
                        $taskDateList = [$data['task_start'], $data['task_end']];
                        foreach ($taskDateList as $taskDate) {
                            if ($taskDate !== null) {
                                // 確認対象日付を日付型に変換する
                                $dt = new Carbon($taskDate);
                                // 確認対象日付がケアプラン開始日以前、またはケアプラン終了日以降の場合にエラーとする
                                if ($dt->lt($start) || $dt->gt($end)) {
                                    $validator->errors()->add(
                                        'care_plan_period',
                                        'ケアプラン期間内の日付を入力してください'
                                    );
                                    return;
                                }
                            }
                        }
                    }
                }
            }
        });
    }
}
