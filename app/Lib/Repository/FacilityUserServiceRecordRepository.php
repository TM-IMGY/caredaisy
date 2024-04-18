<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserServiceRecordRepositoryInterface;
use App\Lib\Entity\FacilityUserService;
use App\Lib\Entity\FacilityUserServiceRecord;
use Carbon\CarbonImmutable;
use DB;

/**
 * 施設利用者のサービス種類の記録のリポジトリクラス。
 */
class FacilityUserServiceRecordRepository implements FacilityUserServiceRecordRepositoryInterface
{
    /**
     * 施設利用者のサービスを返す。
     * @param int $facilityUserId 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserServiceRecord
     */
    public function find(int $facilityUserId, int $year, int $month): FacilityUserServiceRecord
    {
        $facilityUserServiceRecords = $this->get([$facilityUserId], $year, $month);
        return $facilityUserServiceRecords[0];
    }

    /**
     * 施設利用者の対象年月の最新かつ利用中のサービスを返す。
     * @param array $facilityUserIds 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserServiceRecord[]
     */
    public function get(array $facilityUserIds, int $year, int $month): array
    {
        $facilityUserCount = count($facilityUserIds);
        $facilityUserIdPlaceHolder = rtrim(str_repeat('?,', $facilityUserCount), ',');
        $whereFacilityUserIds = "facility_user_id IN ( ${facilityUserIdPlaceHolder} )";

        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $query = <<<SQL
SELECT
    area_unit_price_1,
    area_unit_price_2,
    area_unit_price_3,
    area_unit_price_4,
    area_unit_price_5,
    area_unit_price_6,
    area_unit_price_7,
    area_unit_price_8,
    area_unit_price_9,
    area_unit_price_10,
    facility_id,
    ufs.facility_user_id,
    service_end_date,
    ufs.service_id,
    service_start_date,
    service_type_code,
    s.service_type_code_id,
    st.service_type_name,
    usage_situation,
    use_end,
    use_start,
    user_facility_service_information_id
FROM
    -- 施設利用者のサービステーブル
    (
        SELECT
            facility_id,
            facility_user_id,
            service_id,
            usage_situation,
            use_end,
            use_start,
            user_facility_service_information_id
        FROM
            i_user_facility_service_informations
        WHERE
            {$whereFacilityUserIds}
            AND use_start <= ?
            AND use_end >= ?
            -- TODO: ビジネスロジック側で有効なサービスとして再定義する。
            AND usage_situation = 1
    ) as ufs
INNER JOIN
    -- 施設利用者が事業所から提供を受けているサービスごとに最新で集計したテーブル。
    (
        SELECT
            facility_user_id,
            service_id,
            max(use_start) as use_start_latest
        FROM
            i_user_facility_service_informations
        WHERE
            {$whereFacilityUserIds} 
            AND use_start <= ?
            AND use_end >= ?
            -- TODO: ビジネスロジック側で有効なサービスとして再定義する。
            AND usage_situation = 1
        GROUP BY
            facility_user_id,
            service_id
    ) as ufs_latest
ON
    ufs.facility_user_id = ufs_latest.facility_user_id
    AND ufs.service_id = ufs_latest.service_id
    AND ufs.use_start = ufs_latest.use_start_latest
INNER JOIN
    -- 事業所のサービステーブル
    (
        SELECT
            id,
            service_type_code_id
        FROM
            i_services
    ) s
ON
    ufs.service_id = s.id
INNER JOIN
    -- サービス種類コードマスタ
    (
        SELECT
            area_unit_price_1,
            area_unit_price_2,
            area_unit_price_3,
            area_unit_price_4,
            area_unit_price_5,
            area_unit_price_6,
            area_unit_price_7,
            area_unit_price_8,
            area_unit_price_9,
            area_unit_price_10,
            service_end_date,
            service_start_date,
            service_type_code,
            service_type_code_id,
            service_type_name
        FROM
            m_service_types
        WHERE
            service_start_date <= ?
            AND service_end_date >= ?
    ) st
ON
    s.service_type_code_id = st.service_type_code_id
SQL;

        $queryParameter = array_merge(
            $facilityUserIds,
            [$targetMonthEndDate, $targetMonthStartDate],
            $facilityUserIds,
            [$targetMonthEndDate, $targetMonthStartDate],
            [$targetMonthEndDate, $targetMonthStartDate]
        );
        $records = DB::select($query, $queryParameter);

        // 施設利用者ごとにサービスの記録を作成する。
        $facilityUserServiceRecords = [];
        foreach ($facilityUserIds as $facilityUserId) {
            $facilityUserServices = [];
            foreach ($records as $record) {
                if ($facilityUserId !== $record->facility_user_id) {
                    continue;
                }

                $facilityUserService = new FacilityUserService(
                    $record->area_unit_price_1,
                    $record->area_unit_price_2,
                    $record->area_unit_price_3,
                    $record->area_unit_price_4,
                    $record->area_unit_price_5,
                    $record->area_unit_price_6,
                    $record->area_unit_price_7,
                    $record->area_unit_price_8,
                    $record->area_unit_price_9,
                    $record->area_unit_price_10,
                    $record->facility_id,
                    $record->facility_user_id,
                    $record->service_end_date,
                    $record->service_id,
                    $record->service_start_date,
                    $record->service_type_code,
                    $record->service_type_code_id,
                    $record->service_type_name,
                    $record->usage_situation,
                    $record->use_end,
                    $record->user_facility_service_information_id,
                    $record->use_start
                );

                $facilityUserServices[] = $facilityUserService;
            }

            $facilityUserServiceRecords[] = new FacilityUserServiceRecord($facilityUserId, $facilityUserServices);
        }

        return $facilityUserServiceRecords;
    }
}
