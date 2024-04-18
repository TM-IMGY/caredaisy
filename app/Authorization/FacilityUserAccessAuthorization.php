<?php

namespace App\Authorization;

use App\Models\UserFacilityInformation;
use App\Service\GroupHome\FacilityService;

class FacilityUserAccessAuthorization
{
    /**
     * @param array $facilityUserIdList 検証対象の施設利用者のIDのリスト
     * @return bool
     */
    public function can(array $facilityUserIdList) : bool
    {
        // ログインしているアカウントに紐づく事業所IDのリストを取得
        $facilityService = new FacilityService();
        $facilityIdList = array_column($facilityService->getRelatedData(), 'facility_id');

        // 事業所IDのリストに紐づく、アクセス可能な施設利用者のIDのリストを取得する
        $facilityUser = UserFacilityInformation::whereIn('facility_id', $facilityIdList)
            ->select('facility_user_id')
            ->get()
            ->toArray();
        $AccessibleFacilityUserIdList = array_column($facilityUser, 'facility_user_id');

        // アクセス可能な施設利用者のIDのリストに、検証対象の施設利用者のIDのリストが含まれるか
        for ($i = 0,$cnt = count($facilityUserIdList); $i < $cnt; $i++) {
            if (!in_array($facilityUserIdList[$i], $AccessibleFacilityUserIdList, true)) {
                return false;
            }
        }
        return true;
    }
}
