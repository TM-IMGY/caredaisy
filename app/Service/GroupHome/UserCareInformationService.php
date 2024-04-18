<?php

namespace App\Service\GroupHome;

use App\Models\UserCareInformation;

class UserCareInformationService
{
  /**
   * @param array $param: clm_list,facility_user_id_list,month,year,with
   * @return array
   */
    public function get($param) : array
    {
        $qb = UserCareInformation::whereIn('facility_user_id', $param['facility_user_id_list'])
            ->when($param['year'] !== null && $param['month'] !== null, function($query) use ($param){
                return $query->date($param['year'], $param['month']);
            })
        ->select($param['clm_list']);

        if (array_key_exists('with', $param)) {
            $qb = $qb->with($param['with']);
        }

        $data = $qb->orderBy('care_period_start', 'asc')->get()->toArray();

      // 重複行をcare_period_startが最新のレコードで上書きする
        $dataUniq = [];
        for ($i = 0,$cnt = count($data); $i < $cnt; $i++) {
            $facilityUserID = $data[$i]['facility_user_id'];
            $dataUniq[$facilityUserID] = $data[$i];

            if (array_key_exists('m_care_levels', $dataUniq[$facilityUserID])) {
                $dataUniq[$facilityUserID]['care_level'] = $dataUniq[$facilityUserID]['m_care_levels'];
                unset($dataUniq[$facilityUserID]['m_care_levels']);
            }
        }

        $dataUniq = array_values($dataUniq);

        return $dataUniq;
    }

  /**
   * @param array $param: clm_list,facility_user_id_list,month,year,with
   * @return array
   */
    public function getApprovalStatus($param) : array
    {
        $qb = UserCareInformation::whereIn('facility_user_id', $param['facility_user_id_list'])
            ->when($param['year'] !== null && $param['month'] !== null, function($query) use ($param){
                return $query->date($param['year'], $param['month']);
            })
        ->select($param['clm_list']);

        if (array_key_exists('with', $param)) {
            $qb = $qb->with($param['with']);
        }

        $data = $qb->orderBy('care_period_start', 'asc')->get()->toArray();

        // 重複行をcare_period_startが最新のレコードで上書きする
        $dataUniq = [];
        $statusId = [];
        for ($i = 0,$cnt = count($data); $i < $cnt; $i++) {
            $facilityUserID = $data[$i]['facility_user_id'];
            $dataUniq[$facilityUserID] = $data[$i];
            // 利用者の認定情報申請中のレコードが1つでもある場合、そのfacilityUserIDを配列に入れておく。
            if($dataUniq[$facilityUserID]['certification_status'] == 1){
                $statusId[] += $facilityUserID;
            }
            if (array_key_exists('m_care_levels', $dataUniq[$facilityUserID])) {
                $dataUniq[$facilityUserID]['care_level'] = $dataUniq[$facilityUserID]['m_care_levels'];
                unset($dataUniq[$facilityUserID]['m_care_levels']);
            }
        }
        // 重複しているfacilityUserIDを解消する。
        $statusId = array_unique($statusId);
        $statusId = array_values($statusId);

        // 利用者の認定情報申請中のレコードが1つでもあるfacilityUserIDのステータスを申請中とする。
        // (利用者リストに申請中のアイコンを表示するため)
        for($i = 0; $i < count($statusId); $i++){
            $dataUniq[$statusId[$i]]['certification_status'] = 1;
        }
        
        // インデックスをリセット
        $dataUniq = array_values($dataUniq);

        return $dataUniq;
    }
}
