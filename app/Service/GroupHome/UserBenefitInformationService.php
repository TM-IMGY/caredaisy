<?php

namespace App\Service\GroupHome;

use App\Models\UserBenefitInformation;

class UserBenefitInformationService
{
    private const VALID_START_DATE = '2000-03-31';
    private const VALID_END_DATE = '2100-01-01';
    private const RECORD_COUNT_CRITERION = 0;

  /**
   * @param array $param: clm_list,facility_user_id_list,month,year
   * @return array
   */
    public function getData($param) : array {
        $qb = UserBenefitInformation::whereIn('facility_user_id', $param['facility_user_id_list'])
            ->date($param['year'], $param['month'])
            ->select($param['clm_list']);

        $data = $qb->orderBy('effective_start_date', 'asc')->get()->toArray();

      // 重複行をeffective_start_dateが最新のレコードで上書きする
        $dataUniq = [];
        for ($i = 0,$cnt = count($data); $i < $cnt; $i++) {
            $facilityUserID = $data[$i]['facility_user_id'];
            $dataUniq[$facilityUserID] = $data[$i];
        }
      // ユニークにしてインデックスをリセット
        $dataUniq = array_values($dataUniq);

        return $dataUniq;
    }

    public function getBenefitHistory($id)
    {
        $benefit_history = UserBenefitInformation::where('facility_user_id', $id)
            ->select(
                'created_at',
                'benefit_information_id',
                'benefit_type',
                'benefit_rate',
                'effective_start_date',
                'expiry_date',
            )
            ->orderBy('effective_start_date', 'desc')
            ->get()
            ->toArray();

        return $benefit_history;
    }

    public function getBenefitData($targetId)
    {
        $benefitHistory = UserBenefitInformation::where('benefit_information_id', $targetId)
            ->select('benefit_information_id', 'benefit_type', 'benefit_rate', 'effective_start_date', 'expiry_date')
            ->first();

        $benefitData = [
            "benefit_information_id" => $benefitHistory->benefit_information_id,
            "benefit_type" => $benefitHistory->benefit_type,
            "benefit_rate" => $benefitHistory->benefit_rate,
            "effective_start_date" => $benefitHistory->effective_start_date,
            "expiry_date" => $benefitHistory->expiry_date,
        ];

        return $benefitData;
    }

    public function getBenefitValuesCheckResult($params)
    {
        // 他画面でも同様の処理を実装するため将来的に共通化予定

        $datas = [
            'benefit_type' => $params['benefit_type'],
            'benefit_rate' => $params['benefit_rate'],
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

        // 期間の重複条件作成
        $where = [
            ['facility_user_id', $params['facility_user_id']],
            ['effective_start_date', '<=', $datas['expiry_date']],
            ['expiry_date', '>=', $datas['effective_start_date']],
        ];

        if(isset($params['benefit_information_id'])){
            $where[] = ['benefit_information_id', '<>', $params['benefit_information_id']];
        }

        // 重複期間のレコード数を取得
        $countResult = UserBenefitInformation::where($where)->count();

        // 開始日から1年後の日付を取得
        $startDate1YearsLater = date('Y/m/d', strtotime($datas['effective_start_date'].'1 year'));

        // 期間の重複を確認
        if(self::RECORD_COUNT_CRITERION < $countResult){
            // 新規登録かつ開始日から見て終了日が1年以上の場合はfalseを返す
            if($params['post_type'] === 'register'
                && $startDate1YearsLater <= $datas['expiry_date']){
                return response()->json(false);
            }else{
                return response()->json(true);
            }
        }

        // 開始日・終了日の関係性を確認
        if($datas['effective_start_date'] > $datas['expiry_date']){
            return response()->json(true);
        }

        // 指定期間内か確認
        if(self::VALID_START_DATE >= $datas['effective_start_date']
            || self::VALID_START_DATE >= $datas['expiry_date'] ){
            return response()->json(true);
        }

        if(self::VALID_END_DATE <= $datas['effective_start_date']
            || self::VALID_END_DATE <= $datas['expiry_date']){
            return response()->json(true);
        }

        // 開始日から見て終了日が1年以上か確認
        if($startDate1YearsLater > $datas['expiry_date']){
            return response()->json(true);
        }

        return response()->json(false);
    }

    public function benefitSave($param)
    {
        if (empty($param['benefit_information_id'])) {
            $res = UserBenefitInformation::create([
                'facility_user_id' => $param['facility_user_id'],
                'benefit_type' => $param['benefit_type'],
                'benefit_rate' => $param['benefit_rate'],
                'effective_start_date' => $param['effective_start_date'],
                'expiry_date' => $param['expiry_date'],
            ]);
        } else {
            $res = UserBenefitInformation::where('facility_user_id', '=', $param['facility_user_id'])
                ->Where('benefit_information_id', '=', $param['benefit_information_id'])
                ->update([
                    'benefit_type' => $param['benefit_type'],
                    'benefit_rate' => $param['benefit_rate'],
                    'effective_start_date' => $param['effective_start_date'],
                    'expiry_date' => $param['expiry_date'],
                ]);
        }

        return $res;
    }
}
