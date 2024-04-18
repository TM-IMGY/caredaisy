<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserPublicExpenseRecordRepositoryInterface;
use App\Lib\Entity\Facility;
use App\Lib\Entity\FacilityUserPublicExpense;
use App\Lib\Entity\FacilityUserPublicExpenseRecord;
use Carbon\CarbonImmutable;
use DB;

/**
 * 施設利用者の公費のリポジトリ。
 */
class FacilityUserPublicExpenseRecordRepository implements FacilityUserPublicExpenseRecordRepositoryInterface
{
    /**
     * 施設利用者の公費の記録を返す。getのラッパー。
     * @param Facility $facility 事業所
     * @param int $facilityUserId 施設利用者のID。
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ?FacilityUserPublicExpenseRecord
     */
    public function find(Facility $facility, int $facilityUserId, int $year, int $month): ?FacilityUserPublicExpenseRecord
    {
        $facilityUserPublicExpenseRecords = $this->get($facility, [$facilityUserId], $year, $month);
        if (count($facilityUserPublicExpenseRecords) === 0) {
            return null;
        }
        return $facilityUserPublicExpenseRecords[0];
    }

    /**
     * 施設利用者の公費を返す。
     * 施設利用者の公費は対象年月単位で取り扱うのが原則だが、一部で個別で取得する必要があったため追加された。
     * @param int $facilityUserPublicExpenseId 施設利用者の公費のID
     */
    public function findById(int $facilityUserPublicExpenseId): ?FacilityUserPublicExpense
    {
        $query = <<<SQL
SELECT
    upe.amount_borne_person,
    upe.application_classification,
    upe.bearer_number,
    ps.benefit_rate,
    upe.burden_stage,
    upe.confirmation_medical_insurance_date,
    upe.effective_start_date as upe_effective_start_date,
    ps.effective_start_date as ps_effective_start_date,
    upe.expiry_date as upe_expiry_date,
    ps.expiry_date as ps_expiry_date,
    upe.facility_user_id,
    upe.food_expenses_burden_limit,
    upe.hospitalization_burden,
    ps.legal_name,
    ps.legal_number,
    upe.living_expenses_burden_limit,
    upe.outpatient_contribution,
    ps.priority,
    upe.public_expense_information_id,
    upe.recipient_number,
    upe.special_classification
FROM
    -- 施設利用者の公費テーブル
    (
        SELECT
            amount_borne_person,
            application_classification,
            bearer_number,
            burden_stage,
            confirmation_medical_insurance_date,
            effective_start_date,
            expiry_date,
            facility_user_id,
            food_expenses_burden_limit,
            hospitalization_burden,
            living_expenses_burden_limit,
            outpatient_contribution,
            public_expense_information_id,
            recipient_number,
            special_classification
        FROM
            i_user_public_expense_informations
        WHERE
            public_expense_information_id = ?
    ) as upe
INNER JOIN
    -- 公費マスタテーブル
    (
        SELECT DISTINCT
            benefit_rate,
            effective_start_date,
            expiry_date,
            legal_name,
            legal_number,
            priority
        FROM
            m_public_spendings
        -- TODO: 現状は存在しないが将来的に対応が必要な可能性がある。
        -- WHERE
        --     effective_start_date <= ?
        --     AND expiry_date >= ?
    ) as ps
ON
    SUBSTR(upe.bearer_number, 1, 2) = ps.legal_number
SQL;

        $records = DB::select($query, [$facilityUserPublicExpenseId]);

        // 施設利用者の公費を確保する変数。
        $facilityUserPublicExpense = null;

        // レコードを取得できる場合。
        if (count($records) === 1) {
            $record = $records[0];

            // 施設利用者の公費を作成する。
            $facilityUserPublicExpense = new FacilityUserPublicExpense(
                $record->amount_borne_person,
                $record->application_classification,
                $record->bearer_number,
                $record->burden_stage,
                $record->confirmation_medical_insurance_date,
                $record->upe_effective_start_date,
                $record->upe_expiry_date,
                $record->facility_user_id,
                $record->food_expenses_burden_limit,
                $record->hospitalization_burden,
                $record->living_expenses_burden_limit,
                $record->outpatient_contribution,
                $record->public_expense_information_id,
                $record->recipient_number,
                $record->special_classification,
                $record->benefit_rate,
                $record->ps_effective_start_date,
                $record->ps_expiry_date,
                null,
                $record->legal_name,
                $record->legal_number,
                $record->priority,
                null
            );
        }

        return $facilityUserPublicExpense;
    }

    /**
     * 施設利用者の公費の記録を返す。
     * @param Facility $facility 事業所。事業所を渡すことで、そのサービス種類から公費を絞ることができる。
     * @param array $facilityUserIds 施設利用者のID。
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserPublicExpenseRecord[]
     */
    public function get(Facility $facility, array $facilityUserIds, int $year, int $month): array
    {
        $serviceTypeCodeIds = $facility->getServiceTypeCodeIds();
        $facilityServiceCount = count($serviceTypeCodeIds);
        $facilityServicePlaceHolder = rtrim(str_repeat('?,', $facilityServiceCount), ',');
        $whereServiceTypeCodeIds = "service_type_code_id IN ( ${facilityServicePlaceHolder} )";

        $facilityUserCount = count($facilityUserIds);
        $facilityUserIdPlaceHolder = rtrim(str_repeat('?,', $facilityUserCount), ',');
        $whereFacilityUserIds = "facility_user_id IN ( ${facilityUserIdPlaceHolder} )";

        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $query = <<<SQL
SELECT
    upe.amount_borne_person,
    upe.application_classification,
    upe.bearer_number,
    ps.benefit_rate,
    upe.burden_stage,
    upe.confirmation_medical_insurance_date,
    upe.effective_start_date as upe_effective_start_date,
    ps.effective_start_date as ps_effective_start_date,
    upe.expiry_date as upe_expiry_date,
    ps.expiry_date as ps_expiry_date,
    upe.facility_user_id,
    upe.food_expenses_burden_limit,
    upe.hospitalization_burden,
    ps.id,
    ps.legal_name,
    ps.legal_number,
    upe.living_expenses_burden_limit,
    upe.outpatient_contribution,
    ps.priority,
    upe.public_expense_information_id,
    upe.recipient_number,
    ps.service_type_code_id,
    upe.special_classification
FROM
    -- 施設利用者の公費テーブル
    (
        SELECT
            amount_borne_person,
            application_classification,
            bearer_number,
            burden_stage,
            confirmation_medical_insurance_date,
            effective_start_date,
            expiry_date,
            facility_user_id,
            food_expenses_burden_limit,
            hospitalization_burden,
            living_expenses_burden_limit,
            outpatient_contribution,
            public_expense_information_id,
            recipient_number,
            special_classification
        FROM
            i_user_public_expense_informations
        WHERE
            {$whereFacilityUserIds}
            AND effective_start_date <= ?
            AND expiry_date >= ?
    ) as upe
INNER JOIN
    -- 施設利用者が事業所より提供を受けるサービス種類(最新)のテーブル。
    (
        SELECT
            ufs_.facility_user_id,
            ufs_.service_id,
            service_type_code_id
        FROM
            -- 施設利用者が事業所より提供を受けるサービス種類のテーブル。
            (
                SELECT
                    facility_user_id,
                    service_id,
                    use_start
                FROM
                    i_user_facility_service_informations
                WHERE
                    {$whereFacilityUserIds} 
                    AND use_start <= ?
                    AND use_end >= ?
            ) as ufs_
        INNER JOIN
            -- 施設利用者が事業所より提供を受けるサービス種類ごとに最新で集計した一時テーブル。
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
                GROUP BY
                    facility_user_id,
                    service_id
            ) as ufs_latest_
        ON
            ufs_.facility_user_id = ufs_latest_.facility_user_id
            AND ufs_.service_id = ufs_latest_.service_id
            AND ufs_.use_start = ufs_latest_.use_start_latest
        INNER JOIN
            -- 事業所が提供するサービス種類のテーブル。
            (
                SELECT
                    id,
                    service_type_code_id
                FROM
                    i_services
            ) s_
        ON
            ufs_.service_id = s_.id
    ) as ufs
ON
    upe.facility_user_id = ufs.facility_user_id
INNER JOIN
    -- 公費マスタテーブル
    (
        SELECT
            benefit_rate,
            effective_start_date,
            expiry_date,
            id,
            legal_name,
            legal_number,
            priority,
            service_type_code_id
        FROM
            m_public_spendings
        WHERE
            {$whereServiceTypeCodeIds}
            AND effective_start_date <= ?
            AND expiry_date >= ?
    ) as ps
ON
    SUBSTR(upe.bearer_number, 1, 2) = ps.legal_number
    AND ufs.service_type_code_id = ps.service_type_code_id
SQL;

        $queryParameter = array_merge(
            $facilityUserIds,
            [$targetMonthEndDate, $targetMonthStartDate],
            $facilityUserIds,
            [$targetMonthEndDate, $targetMonthStartDate],
            $facilityUserIds,
            [$targetMonthEndDate, $targetMonthStartDate],
            $serviceTypeCodeIds,
            [$targetMonthEndDate, $targetMonthStartDate]
        );
        $records = DB::select($query, $queryParameter);


        // 施設利用者ごとに公費の記録を作成する。
        $facilityUserPublicExpenseRecords = [];
        foreach ($facilityUserIds as $facilityUserId) {
            $facilityUserPublicExpenses = [];
            foreach ($records as $record) {
                if ($facilityUserId !== $record->facility_user_id) {
                    continue;
                }

                $facilityUserPublicExpense = new FacilityUserPublicExpense(
                    $record->amount_borne_person,
                    $record->application_classification,
                    $record->bearer_number,
                    $record->burden_stage,
                    $record->confirmation_medical_insurance_date,
                    $record->upe_effective_start_date,
                    $record->upe_expiry_date,
                    $record->facility_user_id,
                    $record->food_expenses_burden_limit,
                    $record->hospitalization_burden,
                    $record->living_expenses_burden_limit,
                    $record->outpatient_contribution,
                    $record->public_expense_information_id,
                    $record->recipient_number,
                    $record->special_classification,
                    $record->benefit_rate,
                    $record->ps_effective_start_date,
                    $record->ps_expiry_date,
                    $record->id,
                    $record->legal_name,
                    $record->legal_number,
                    $record->priority,
                    $record->service_type_code_id
                );
    
                $facilityUserPublicExpenses[] = $facilityUserPublicExpense;
            }

            if (count($facilityUserPublicExpenses) === 0) {
                continue;
            }

            $facilityUserPublicExpenseRecords[] = new FacilityUserPublicExpenseRecord($facilityUserId, $facilityUserPublicExpenses);
        }

        return $facilityUserPublicExpenseRecords;
    }
}
