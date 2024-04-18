<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserCareRecordRepositoryInterface;
use App\Lib\Entity\CareLevel;
use App\Lib\Entity\FacilityUserCare;
use App\Lib\Entity\FacilityUserCareRecord;
use Carbon\CarbonImmutable;
use DB;

/**
 * 施設利用者の介護情報のリポジトリクラス。
 */
class FacilityUserCareRecordRepository implements FacilityUserCareRecordRepositoryInterface
{
    /**
     * 施設利用者の介護情報の記録を返す。getのラッパー。
     * @param int $facilityUserId 施設利用者のID。
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserCareRecord
     */
    public function find(int $facilityUserId, int $year, int $month): FacilityUserCareRecord
    {
        $facilityUserCareRecords = $this->get([$facilityUserId], $year, $month);
        return $facilityUserCareRecords[0];
    }

    /**
     * 施設利用者を返す。
     * @param array $facilityUserIds 施設利用者のID。
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserCareRecord[]
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
    uci.care_level_id,
    care_level,
    care_level_name,
    care_period_end,
    uci.care_period_start,
    certification_status,
    classification_support_limit_units,
    date_confirmation_insurance_card,
    date_qualification,
    uci.facility_user_id,
    recognition_date,
    user_care_info_id
FROM
    -- 施設利用者の介護情報テーブル
    (
        SELECT DISTINCT
            care_level_id,
            care_period_end,
            care_period_start,
            certification_status,
            date_confirmation_insurance_card,
            date_qualification,
            facility_user_id,
            recognition_date,
            user_care_info_id
        FROM
            i_user_care_informations
        WHERE
            {$whereFacilityUserIds}
            AND care_period_start <= ?
            AND care_period_end >= ?
    ) uci
INNER JOIN
    m_care_levels cl
ON
    uci.care_level_id = cl.care_level_id
SQL;

        $queryParameter = array_merge($facilityUserIds, [$targetMonthEndDate, $targetMonthStartDate]);
        $records = DB::select($query, $queryParameter);

        // 施設利用者ごとに介護情報の記録を作成する。
        $facilityUserCareRecords = [];
        foreach ($facilityUserIds as $facilityUserId) {
            $facilityUserCares = [];
            foreach ($records as $record) {
                if ($facilityUserId !== $record->facility_user_id) {
                    continue;
                }

                // 介護情報クラスを作成する。
                $careLevel = new CareLevel(
                    $record->care_level_id,
                    $record->care_level,
                    $record->care_level_name,
                    $record->classification_support_limit_units
                );

                $facilityUserCare = new FacilityUserCare(
                    $careLevel,
                    $record->care_period_end,
                    $record->care_period_start,
                    $record->certification_status,
                    $record->date_confirmation_insurance_card,
                    $record->date_qualification,
                    $record->facility_user_id,
                    $record->recognition_date,
                    $record->user_care_info_id
                );

                $facilityUserCares[] = $facilityUserCare;
            }

            $facilityUserCareRecords[] = new FacilityUserCareRecord($facilityUserId, $facilityUserCares);
        }

        return $facilityUserCareRecords;
    }
}
