<?php

namespace App\Http\Requests\GroupHome\ResultInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\StayOutManagement;
use Carbon\Carbon;

class StayOutManagementRequest extends CareDaisyBaseFormRequest
{
    const VALID_START_DATE = '2000-03-31';
    const VALID_END_DATE = '2100-01-01';
    const DEFAULT_END_DATE = '2099-12-31';
    const DEFAULT_END_TIME = '23:59:59';
    const RECORD_COUNT_CRITERION = 0;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->has('facility_user_id') && $this->facility_user_id != null) {
            // 新規か更新なのかをチェック
            if ($this->has('id') && $this->id != null) {
                return $this->authorizeFacilityUserId($this->facility_user_id)
                    && $this->authorizeStayOutManagementId($this->id);
            } else {
                return $this->authorizeFacilityUserId($this->facility_user_id);
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
        return [
            'start_date'                        => [
                'bail',
                'required',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE,
            ],
            'start_time'                        => [
                'bail',
                'required',
                'date_format:H:i'
            ],
            'meal_of_the_day_start_morning'     => 'required | boolean',
            'meal_of_the_day_start_lunch'       => 'required | boolean',
            'meal_of_the_day_start_snack'       => 'required | boolean',
            'meal_of_the_day_start_dinner'      => 'required | boolean',

            'end_date'                          => [
                'bail',
                'nullable',
                'regex:/^[0-9\/]+$/',
                'date',
                'before:'.self::VALID_END_DATE,
                'after:'.self::VALID_START_DATE,
            ],
            'end_time'                          => [
                'bail',
                'nullable',
                'date_format:H:i',
            ],
            'meal_of_the_day_end_morning'       => 'required | boolean',
            'meal_of_the_day_end_lunch'         => 'required | boolean',
            'meal_of_the_day_end_snack'         => 'required | boolean',
            'meal_of_the_day_end_dinner'        => 'required | boolean',
            'reason_for_stay_out'               => 'bail | required | integer | min:1 | max: 5',
            'remarks'                           => 'bail | nullable | max:255',
        ];
    }

    public function withValidator($validator)
    {
        if ($validator->fails()) {
            return;
        }

        $id = $this->input('id');
        $startDate = $this->input('start_date');
        $startTime = $this->input('start_time');
        $startDatetime = Carbon::parse($startDate.' '.$startTime);
        // 終了日時が空欄=施設利用者の外出等が"終了していない"ため
        // デフォルトで2099-12-31 23:59:59を格納する
        $endDate = is_null($this->input('end_date')) ? self::DEFAULT_END_DATE : $this->input('end_date');
        $endTime = is_null($this->input('end_time')) ? self::DEFAULT_END_TIME : $this->input('end_time');
        $endDatetime = Carbon::parse($endDate.' '.$endTime);
        $facilityUserId = $this->input('facility_user_id');

        $validator->after(function ($validator) use($id, $startDate, $startTime, $startDatetime, $endDate, $endTime, $endDatetime, $facilityUserId)
        {
            // 終了日か終了時間のどちらか一方のみ未入力の場合の処理
            if ($this->filled('end_date') && !$this->filled('end_time')) {
                $validator->errors()->add('EndDateBlank', '終了時間を入力してください');
            } elseif (!$this->filled('end_date') && $this->filled('end_time')) {
                $validator->errors()->add('EndTimeBlank', '終了日を入力してください');
            }

            // 開始日時＞終了日時の場合の処理
            if($startDatetime > $endDatetime) {
                $validator->errors()->add('DateComparison', '開始日時と終了日時の関係性に誤りがあるので確認してください');
            }

            // 期間重複時の処理
            $where = [
                ['facility_user_id', $facilityUserId],
                ['end_date', '>=', $startDatetime],
                ['start_date', '<=', $endDatetime]
            ];

            if(isset($id)){
                $where[] = ['id', '<>', $id];
            }

            $count = StayOutManagement::where($where)->count();
            if($count > self::RECORD_COUNT_CRITERION){
                $validator->errors()->add('DateDuplication', '重複している期間があるので保存できません');
            }

            // 既存履歴の終了日時が未入力の場合の処理
            $where = [['facility_user_id', $facilityUserId], ['end_date', null]];
            if (!is_null($id)) {
                $where[] = ['id', '<>', $id];
            }
            $countResult = StayOutManagement::where($where)->count();
            if ($countResult > self::RECORD_COUNT_CRITERION) {
                $validator->errors()->add('EndDateTime', '終了日時が入力されていない外泊情報があるので保存できません');
            }
        });
    }

    public function messages()
    {
        return [
            'required'      => ':attribute は必須項目です。',
            'date_format'   => ':attribute の形式が不正です。',
            'min'           => ':attribute は :min文字（桁）以上で入力してください。',
            'max'           => ':attribute は :max文字（桁）以下で入力してください。',
            'before'        => '2099年12月以前の年月を入力してください',
            'after'         => '2000年4月以降の年月を入力してください',
            'date'   => ':attribute の形式が不正です。',
            'regex'         => ':attribute は半角で入力してください'
        ];
    }

    public function attributes()
    {
        return [
            'start_date'                        => '開始日',
            'start_time'                        => '開始時間',
            'meal_of_the_day_start_morning'     => '当日の食事(開始/朝食)',
            'meal_of_the_day_start_lunch'       => '当日の食事(開始/昼食)',
            'meal_of_the_day_start_snack'       => '当日の食事(開始/間食)',
            'meal_of_the_day_start_dinner'      => '当日の食事(開始/夕食)',
            'end_date'                          => '終了日',
            'end_time'                          => '終了時間',
            'meal_of_the_day_end_morning'       => '当日の食事(終了/朝食)',
            'meal_of_the_day_end_lunch'         => '当日の食事(終了/昼食)',
            'meal_of_the_day_end_snack'         => '当日の食事(終了/間食)',
            'meal_of_the_day_end_dinner'        => '当日の食事(終了/夕食)',
            'reason_for_stay_out'               => '外泊理由',
            'remarks'                           => '備考'
        ];
    }

    // API系の共通クラス作って、そこに移動したほうがよさそう
    protected function failedValidation(Validator $validator)
    {
        $res = response()->json([
            'errors' => $validator->errors(),
        ], 400);
        throw new HttpResponseException($res);
    }
}
