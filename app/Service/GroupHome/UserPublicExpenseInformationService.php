<?php

namespace App\Service\GroupHome;

use App\Models\PublicSpending;
use App\Models\UserPublicExpenseInformation;
use Carbon\Carbon;
use App\Models\UserFacilityServiceInformation;

class UserPublicExpenseInformationService
{
    private const VALID_START_DATE = '2000-03-31';
    private const VALID_END_DATE = '2100-01-01';
    private const RECORD_COUNT_CRITERION = 0;

  /**
   * i_user_public_expense_infomations(テーブル名変わる予定)の対象レコードを取得する
   * @param $param key: clm_list,facility_user_id
   * @return array
   */
    public function get($param) : array {
        return UserPublicExpenseInformation::
        where('facility_user_id', $param['facility_user_id'])
            ->select($param['clm_list'])
            ->get()
            ->toArray();
    }

    public function getPublicSpending()
    {
        $result = PublicSpending::select('legal_number', 'legal_name')
            ->get()
            ->toArray();

        return response()->json($result);
    }

    public function getPublicExpenditureHistory($id)
    {
        $public_expenditure_history = UserPublicExpenseInformation::where('facility_user_id', $id)
            ->select('*')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        return $public_expenditure_history;
    }

    public function getPublicExpenditureValuesCheckResult($params)
    {
        // 他画面でも同様の処理を実装するため将来的に共通化予定

        $datas = [
            'bearer_number' => $params['bearer_number'],
            'recipient_number' => $params['recipient_number'],
            'effective_start_date' => $params['effective_start_date'],
            'expiry_date' => $params['expiry_date']
        ];

        // 必須項目の値の有無を確認
        foreach($datas as $data){
            if(is_null($data)){
                return response()->json(true);
            }
        }

        // 日付フォーマットを確認
        if (!preg_match('/\d{4}\/\d{2}\/\d{2}/', $params['effective_start_date']) || !preg_match('/\d{4}\/\d{2}\/\d{2}/', $params['expiry_date'])){
            return response()->json(true);
        }
        if ($params['confirmation_medical_insurance_date'] != null) {
            if (!preg_match('/\d{4}\/\d{2}\/\d{2}/', $params['effective_start_date'])) {
                return response()->json(true);
            }
        }

        // 半角数字以外の有無と桁数を確認
        // false(0)ならtrueを返す
        if(preg_match('/^([0-9]{8})+$/', $params['bearer_number']) === 0
            || preg_match('/^([0-9]{7})+$/', $params['recipient_number']) === 0){
            return response()->json(true);
        }

        // 期間の重複条件作成
        // 負担者番号の先頭から2文字目までを取得
        $bearerNumberType = substr($params['bearer_number'], 0, 2);

        $where = [
            ['facility_user_id', $params['facility_user_id']],
            ['effective_start_date', '<=', $params['expiry_date']],
            ['expiry_date', '>=', $params['effective_start_date']],
            ['bearer_number', 'like', $bearerNumberType.'%']
        ];

        if(isset($params['public_expense_information_id'])){
            $where[] = ['public_expense_information_id', '<>', $params['public_expense_information_id']];
        }

        // 負担者区分が同一かつ期間が重複しているレコード数を取得
        $countResult = UserPublicExpenseInformation::where($where)->count();

        // 開始日から1年後の日付を取得
        $carbon = new Carbon($params['effective_start_date']);
        $startDate1YearsLater = $carbon->addYears(1)->format('Y/m/d');

        // 期間の重複を確認
        if(self::RECORD_COUNT_CRITERION < $countResult){
            // 新規登録かつ開始日から見て終了日が1年以上の場合はfalseを返す
            if($params['update_type'] === false
                && $startDate1YearsLater <= $params['expiry_date']){
                return response()->json(false);
            }else{
                return response()->json(true);
            }
        }

        // 開始日・終了日の関係性を確認
        if($params['effective_start_date'] > $params['expiry_date']){
            return response()->json(true);
        }

        // 指定期間内か確認
        if(self::VALID_START_DATE >= $params['effective_start_date']
            || self::VALID_START_DATE >= $params['expiry_date']){
            return response()->json(true);
        }

        if(self::VALID_END_DATE <= $params['effective_start_date']
            || self::VALID_END_DATE <= $params['expiry_date']){
            return response()->json(true);
        }

        // 開始日から見て終了日が1年以上か確認
        if($startDate1YearsLater > $params['expiry_date']){
            return response()->json(true);
        }

        // 負担者区分が半角数字で「生活保護の医療」の場合はfalseを返す
        if(preg_match('/^12/', $params['bearer_number'])){
            return response()->json(false);
        }else{
            return response()->json(true);
        }

    }

    public function publicExpenditureSave($param)
    {
        if (empty($param['public_expense_information_id'])) {
            $result = UserPublicExpenseInformation::create([
            'facility_user_id' => $param['facility_user_id'],
            'bearer_number' => $param['bearer_number'],
            'recipient_number' => $param['recipient_number'],
            'confirmation_medical_insurance_date' => $param['confirmation_medical_insurance_date'],
            'food_expenses_burden_limit' => $param['food_expenses_burden_limit'],
            'living_expenses_burden_limit' => $param['living_expenses_burden_limit'],
            'outpatient_contribution' => $param['outpatient_contribution'],
            'hospitalization_burden' => $param['hospitalization_burden'],
            'application_classification' => $param['application_classification'],
            'special_classification' => $param['special_classification'],
            'effective_start_date' => $param['effective_start_date'],
            'expiry_date' => $param['expiry_date'],
            'amount_borne_person' => $param['amount_borne_person'],
            ]);
        } else {
            $result = UserPublicExpenseInformation::where('facility_user_id', '=', $param['facility_user_id'])
                ->Where('public_expense_information_id', '=', $param['public_expense_information_id'])
                ->update([
                'bearer_number' => $param['bearer_number'],
                'recipient_number' => $param['recipient_number'],
                'confirmation_medical_insurance_date' => $param['confirmation_medical_insurance_date'],
                'food_expenses_burden_limit' => $param['food_expenses_burden_limit'],
                'living_expenses_burden_limit' => $param['living_expenses_burden_limit'],
                'outpatient_contribution' => $param['outpatient_contribution'],
                'hospitalization_burden' => $param['hospitalization_burden'],
                'application_classification' => $param['application_classification'],
                'special_classification' => $param['special_classification'],
                'effective_start_date' => $param['effective_start_date'],
                'expiry_date' => $param['expiry_date'],
                'amount_borne_person' => $param['amount_borne_person'],
            ]);
        }
        return $result;
    }

    //公費略称のチェック用データ
    public function getPublicSpendingCheckedData($facility_user_id, $facility_id)
    {
        $now = new \Carbon\CarbonImmutable();
        $result = UserFacilityServiceInformation::with('Service.serviceType.publicSpending')->where([
                ['facility_user_id', $facility_user_id ],
                ['facility_id', $facility_id ],
                ['use_start', '<=', $now ]
            ])->where(function($query) use($now){
                $query->where('use_end', '>=', $now)->orWhereNull('use_end');
            })->first();
        return $result;
    }
}
