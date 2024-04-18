<?php

namespace App\Service\GroupHome;

use App\Models\UserIndependenceInformation;

class UserIndependenceInformationService
{
    /**
     * 利用者の自立度テーブルから情報を取得するための手続き
     * @param array $param
     * @param array $param['clm']
     * @param array $param['facility_user_id']
     * @param string $param['target_date'] 日付
     */
    public function get($param) : array
    {
        // ユーザーごとに判断日が対象日以下の条件で最新の情報を取得する
        $data = [];
        $userIndependenceInformation = UserIndependenceInformation::
            whereIn('facility_user_id', $param['facility_user_id'])
            ->whereDate('judgment_date', '<=', $param['target_date'])
            ->select($param['clm'])
            ->orderBy('judgment_date', 'asc')
            ->get()
            ->toArray();
        for ($i = 0,$cnt = count($userIndependenceInformation); $i < $cnt; $i++) {
            $facilityUserId = $userIndependenceInformation[$i]['facility_user_id'];
            $data[$facilityUserId] = $userIndependenceInformation[$i];
        }
        return array_values($data); // インデックスをリセット
    }
}
