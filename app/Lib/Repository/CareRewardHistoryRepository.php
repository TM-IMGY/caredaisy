<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\CareRewardHistoryRepositoryInterface;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\CareRewardNotFoundException;
use App\Lib\Entity\CareRewardHistory;
use Carbon\CarbonImmutable;
use DB;

/**
 * 介護報酬履歴のリポジトリクラス。
 */
class CareRewardHistoryRepository implements CareRewardHistoryRepositoryInterface
{

    /**
     * サービスIDから介護報酬履歴を取得して返す。
     * @param int $serviceId 事業所のサービスのID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return array
     */
    public function find(int $serviceId, int $year, int $month): CareRewardHistory
    {
        $targetYmStartDate = "${year}-${month}-1";
        $targetYmEndDate = (new CarbonImmutable($targetYmStartDate))->endOfMonth()->format('Y-m-d');

        $query = <<<SQL
SELECT
    *
FROM
    -- 介護報酬テーブル。
    -- サービスは介護報酬をN個持つ。
    (
        SELECT
            `id`,
            `service_id`
        FROM
            `i_care_rewards`
        WHERE
            `service_id` = ?
    ) as cr
INNER JOIN
    -- 介護報酬履歴テーブル。
    -- 介護報酬履歴は対象年月に1つだけ持つ。
    (
        SELECT
            `adl_maintenance_etc`,
            `care_reward_id`,
            `consultation`,
            `covid-19` as `covid_19`,
            `dementia_specialty`,
            `discharge_cooperation`,
            `discount`,
            `emergency_response`,
            `end_month`,
            `hospitalization_cost`,
            `id`,
            `improvement_of_living_function`,
            `improvement_of_specific_treatment`,
            `individual_function_training_1`,
            `individual_function_training_2`,
            `initial`,
            `juvenile_dementia`,
            `medical_cooperation`,
            `medical_institution_cooperation`,
            `night_care`,
            `night_care_over_capacity`,
            `night_nursing_system`,
            `night_shift`,
            `nursing_care`,
            `nutrition_management`,
            `oral_hygiene_management`,
            `oral_screening`,
            `over_capacity`,
            `physical_restraint`,
            `scientific_nursing`,
            `section`,
            `service_form`,
            `start_month`,
            `strengthen_service_system`,
            `support_continued_occupancy`,
            `support_persons_disabilities`,
            `treatment_improvement`,
            `vacancy`
        FROM
            `i_care_reward_histories`
        WHERE
            `start_month` <= ?
            AND `end_month` >= ?
    ) as crh
ON
    cr.id = crh.care_reward_id
SQL;

        $records = DB::select($query, [$serviceId, $targetYmEndDate, $targetYmStartDate]);

        if (empty($records)) {
            throw new CareRewardNotFoundException();
        }

        // (SQLのコメント参照)介護報酬履歴はサービスについて対象年月中に一意のレコードを持つ。
        $record = $records[0];

        $careRewardHistory = new CareRewardHistory(
            $record->adl_maintenance_etc,
            $record->care_reward_id,
            $record->consultation,
            $record->covid_19,
            $record->dementia_specialty,
            $record->discharge_cooperation,
            $record->discount,
            $record->emergency_response,
            $record->end_month,
            $record->hospitalization_cost,
            $record->id,
            $record->improvement_of_living_function,
            $record->improvement_of_specific_treatment,
            $record->individual_function_training_1,
            $record->individual_function_training_2,
            $record->initial,
            $record->juvenile_dementia,
            $record->medical_cooperation,
            $record->medical_institution_cooperation,
            $record->night_care,
            $record->night_care_over_capacity,
            $record->night_nursing_system,
            $record->night_shift,
            $record->nursing_care,
            $record->nutrition_management,
            $record->oral_hygiene_management,
            $record->oral_screening,
            $record->over_capacity,
            $record->physical_restraint,
            $record->scientific_nursing,
            $record->section,
            $record->service_form,
            $record->start_month,
            $record->strengthen_service_system,
            $record->support_continued_occupancy,
            $record->support_persons_disabilities,
            $record->treatment_improvement,
            $record->vacancy,
        );

        return $careRewardHistory;
    }
}
