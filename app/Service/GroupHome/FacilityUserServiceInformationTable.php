<?php

namespace App\Service\GroupHome;

/**
 * 施設利用者のサービスのテーブル操作を行うクラス
 */
class FacilityUserServiceInformationTable
{
    /**
     * 対象期間の施設利用者複数人のサービスの情報を取得する
     * @param array $params key: facility_user_ids, usage_situation, use_start, use_end,
     * @return array
     */
    public function getTargetPeriodFacilityUsersServiceData(array $params) : array
    {
        // 施設利用者のIDのパラメーターを作成する
        $facilityUserIds = $params['facility_user_ids'];

        // SQLクエリのwhere句のプレースホルダーを作成する
        $whereFacilityUserIdIn = '';
        if (count($facilityUserIds) > 0) {
            $whereFacilityUserIdIn = 'AND facility_user_id IN (' . substr(str_repeat(', ?', count($facilityUserIds)), 1) . ')';
        }

        $query = <<< SQL
SELECT
  `facility_id`,
  `facility_user_id`,
  `service_id`,
  `usage_situation`,
  `use_start`,
  `use_end`,
  `user_facility_service_information_id`
FROM
  `i_user_facility_service_informations`
WHERE
  `usage_situation` = ?
  AND `use_start` <= ?
  AND `use_end` >= ?
  ${whereFacilityUserIdIn}
SQL;

        $queryBindingData = array_merge(
            [
            $params['usage_situation'],
            $params['use_start'],
            $params['use_end']
            ],
            $facilityUserIds,
        );

        $data = \DB::select($query, $queryBinding);

        return $data;
    }
}
