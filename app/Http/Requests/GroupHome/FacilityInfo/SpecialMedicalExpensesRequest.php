<?php

namespace App\Http\Requests\GroupHome\FacilityInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use \App\Models\CareRewardHistory;
use App\Models\SpecialMedicalSelect;
use Carbon\Carbon;

class SpecialMedicalExpensesRequest extends CareDaisyBaseFormRequest
{
    const VALID_START_DATE = '2021-04-01';
    const VALID_END_DATE = '2024-03-31';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('facility_id') && $this->facility_id != null) {
            return $this->authorizeFacilityId($this->facility_id);
        }
        return false;
    }

    /**
     * 権限がなかった場合の処理を上書き
     */
    protected function failedAuthorization()
    {
        $res = response()->json([
            'errors' => array('選択している情報の編集は許可されていません。'),
        ], 400);
        throw new HttpResponseException($res);
    }

    /**
     * @param Validator $validator
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
        // 加算状況の期間内かどうか
        $validateCareRewardMonth = function ($attribute, $value, $fail) {
            $requestData = $this->all();
            $careRewardId = $requestData['care_reward_id'];
            $where = [
                ['care_reward_id', $requestData['care_reward_id']],
                ['start_month', '<=', $requestData['start_month']],
                ['end_month', '>=', $requestData['end_month']]
            ];
            $count = CareRewardHistory::where($where)->count();
            if ($count == 0) {
                $fail('加算状況利用期間外な為、保存できません');
            }
        };

        // 期間重複が存在するかどうか
        $validateDuplicateMonth = function ($attribute, $value, $fail) {
            $requestData = $this->all();
            $where = [
                ['care_rewards_id', $requestData['care_reward_id']],
                ['end_month', '>=', $requestData['start_month']],
                ['start_month', '<=', $requestData['end_month']]
            ];

            if (isset($requestData['special_medical_selects_id'])) {
                $where[] = ['id', '<>', $requestData['special_medical_selects_id']];
            }

            $count = SpecialMedicalSelect::where($where)->count();
            if ($count > 0) {
                $fail('期間重複している履歴があります。');
            }
        };


        $rules = [
            'facility_id'                           => 'bail | required | integer',
            'start_month'                           => ['bail', 'after_or_equal:' . self::VALID_START_DATE, $validateCareRewardMonth, $validateDuplicateMonth],
            'end_month'                             => 'bail | before_or_equal:' . self::VALID_END_DATE . ' | after_or_equal:start_month',
            'care_reward_id'                        => 'bail | required | integer',
            'checked'                               => 'array',
        ];

        if ($this->has($this->special_medical_selects_id)) {
            $rules['special_medical_selects_id'] = 'bail | required | integer';
        }

        return $rules;
    }

    public function messages()
    {
        $validStartDateObj = new Carbon(self::VALID_START_DATE);
        $validEndDateObj = new Carbon(self::VALID_END_DATE);

        return [
            'required'                     => ':attribute は必須項目です。',
            'integer'                      => ':attribute の形式が不正です。',
            'array'                        => ':attribute の形式が不正です。',
            'after_or_equal'               => '開始日と終了日の関係性に誤りがあるので確認してください',
            'before'                       => '2099年12月以前の年月を入力してください',
            'after'                        => '2000年4月以降の年月を入力してください',
            'after_or_equal'               => '開始月、終了月を' . $validStartDateObj->format('Y/n') . '~' . $validEndDateObj->format('Y/n') . 'の範囲で指定してください',
            'before_or_equal'              => '開始月、終了月を' . $validStartDateObj->format('Y/n') . '~' . $validEndDateObj->format('Y/n') . 'の範囲で指定してください',
        ];
    }

    public function attributes()
    {
        return [
            'facility_id'                           => '事業所選択',
            'start_month'                           => '有効開始月',
            'end_month'                             => '有効終了月',
            'special_medical_selects_id'            => '特別診療費ID',
            'care_reward_id'                        => '加算状況ID',
            'checked'                               => '選択内容',
        ];
    }
}
