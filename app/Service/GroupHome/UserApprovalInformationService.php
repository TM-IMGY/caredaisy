<?php

namespace App\Service\GroupHome;

use App\Models\UserCareInformation;

class UserApprovalInformationService
{
    private const VALID_START_DATE = '2000-03-31';
    private const VALID_END_DATE = '2100-01-01';
    private const RECORD_COUNT_CRITERION = 0;

    public function getApprovalValuesCheckResult($params)
    {
        // 他画面でも同様の処理を実装するため将来的に共通化予定

        // 必須項目の値の有無を確認
        foreach($params as $param){
            if(is_null($param)){
                return response()->json(true);
            }
        }

        if (!preg_match('/\d{4}\/\d{2}\/\d{2}/', $params['start_date']) || !preg_match('/\d{4}\/\d{2}\/\d{2}/', $params['end_date'])){
            return response()->json(true);
        }

       // 期間の重複条件作成
        $where = [
            ['facility_user_id', $params['facility_user_id']],
            ['care_period_start', '<=', $params['end_date']],
            ['care_period_end', '>=', $params['start_date']],
            ['certification_status', '=', 2],
        ];

        if ($params['save_id_approval']!== 0) {
            $where[] = ['user_care_info_id', '<>', $params['save_id_approval']];
        }

        // 重複期間のレコード数を取得
        $countResult = UserCareInformation::where($where)->count();

        // 開始日から4年後の日付を取得
        $startDate4YearsLater = date('Y-m-d', strtotime($params['start_date'].'4 year'));

        // 期間の重複を確認
        if(self::RECORD_COUNT_CRITERION < $countResult){
            return response()->json(true);
        }

        // 開始日・終了日の関係性を確認
        if($params['start_date'] > $params['end_date']){
            return response()->json(true);
        }

        // 指定期間内か確認
        if(self::VALID_START_DATE >= $params['start_date'] 
            || self::VALID_START_DATE >= $params['end_date'] 
            || self::VALID_START_DATE >= $params['recognition_date']){
            return response()->json(true);
        }

        if(self::VALID_END_DATE <= $params['start_date'] 
            || self::VALID_END_DATE <= $params['end_date'] 
            || self::VALID_END_DATE <= $params['recognition_date']){
            return response()->json(true);
        }

        // 開始日から見て終了日が4年以上か確認
        if($startDate4YearsLater > $params['end_date']){
            return response()->json(true);
        }

        return response()->json(false);

    }
}
