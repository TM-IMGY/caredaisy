<?php

namespace App\Service\GroupHome;

use App\Models\Service;
use App\Models\ServiceType;
use App\Models\UserFacilityServiceInformation;

/**
 * 実績情報画面で発生したユースケースを解決するクラス。
 */
class ResultInformationService
{
    /**
     * 施設利用者が提供を受けているサービス種別について、対象年月中のものを全て取得して返す。
     * @param int $facilityUserId 施設利用者ID
     * @param int $year 年
     * @param int $month 月
     * @return array
     */
    public function listFacilityUserTargetYmService(int $facilityUserId, int $year, int $month): array
    {
        // 施設利用者が提供されているサービスの情報を取得する
        $services = UserFacilityServiceInformation::listFacilityUserTargetMonth($facilityUserId, $year, $month);

        // 施設利用者が提供されているサービスがない場合は、そのまま返す。
        if (count($services) == 0) {
            return [];
        }

        // 施設利用者が提供されているサービスのIDを全て取得する。
        $serviceIds = array_column($services, 'service_id');

        // 施設利用者が提供されているサービス種別IDを全て取得する。
        $services = Service::
            whereIn('id', $serviceIds)
            ->select('service_type_code_id')
            ->get()
            ->toArray();
        $serviceTypeCodeIds = array_column($services, 'service_type_code_id');

        // 施設利用者が提供されているサービス種別コード情報を全て取得する。
        $serviceTypes = ServiceType::
            whereIn('service_type_code_id', $serviceTypeCodeIds)
            ->get()
            ->toArray();

        return $serviceTypes;
    }
}
