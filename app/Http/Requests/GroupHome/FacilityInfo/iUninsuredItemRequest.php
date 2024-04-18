<?php

namespace App\Http\Requests\GroupHome\FacilityInfo;

use App\Http\Requests\CareDaisyBaseFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\UninsuredItem;
use App\Models\UninsuredItemHistory;

class iUninsuredItemRequest extends CareDaisyBaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this::has('service_id') && $this->service_id != null) {
            return $this->authorizeServiceId($this->service_id);
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
        // 利用開始月の重複チェック
        $sameMonthCheck = function ($attribute, $value, $fail) {
            $thisMonth = date('Y-m');
            $serviceId = $this->service_id;
            $monthLists = UninsuredItem::where('service_id', $serviceId)
                ->select('start_month')
                ->orderBy('start_month', 'desc')
                ->get()
                ->map(function ($item) {
                    $month = $item->start_month;
                    $latestMonth = date('Y-m', strtotime($month));
                    return $latestMonth;
                });

            if ($monthLists->isEmpty()) {
                return $value;
            }

            if (in_array($thisMonth, $monthLists->toArray(), true)) {
                $fail('すでに今月の保険外費用は登録されています');
            }

            return $value;
        };


        $rules = [
            'service_id'                         => 'required | integer',
            'start_month'                        => ['bail', 'required', 'date_format:"Y-m-d"', $sameMonthCheck],
        ];

        // 初回登録以降
        if ($this->has('latest_item_list')) {
            $addRule = [
                'close_uninsured_items_id'             => 'required | integer',
                'latest_item_list'                     => 'array',
                'latest_item_list.*.item'              => 'required | max:255',
                'latest_item_list.*.unit_cost'         => 'nullable | integer',
                'latest_item_list.*.unit'              => 'required | integer',
                'latest_item_list.*.set_one'           => 'boolean',
                'latest_item_list.*.fixed_cost'        => 'boolean',
                'latest_item_list.*.variable_cost'     => 'boolean',
                'latest_item_list.*.welfare_equipment' => 'boolean',
                'latest_item_list.*.meal'              => 'boolean',
                'latest_item_list.*.daily_necessary'   => 'boolean',
                'latest_item_list.*.hobby'             => 'boolean',
                'latest_item_list.*.escort'            => 'boolean',
                'end_month'                            => 'bail | required | date_format:"Y-m-d"',
            ];
            $rules += $addRule;
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'required'              => ':attribute は必須項目です。',
            'integer'               => ':attribute の形式が不正です。',
            'max'                   => ':attribute の文字数(桁数)が不正です。',
            'date_format:"Y-m-d'    => ':attribute の形式が不正です。',
        ];
    }

    public function attributes()
    {
        return [
            'latest_item_list.*.item'              => '品目',
            'latest_item_list.*.unit_cost'         => '単価',
            'latest_item_list.*.unit'              => '単位',
            'latest_item_list.*.set_one'           => '毎日1を設定',
            'latest_item_list.*.fixed_cost'        => '固定費',
            'latest_item_list.*.variable_cost'     => '変動費',
            'latest_item_list.*.welfare_equipment' => '福祉用具',
            'latest_item_list.*.meal'              => '食事',
            'latest_item_list.*.daily_necessary'   => '日用品',
            'latest_item_list.*.hobby'             => '趣味・娯楽',
            'latest_item_list.*.escort'            => '同行・同伴',
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
