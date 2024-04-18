<?php

namespace App\Service\GroupHome;

use \App\Models\CareReward;
use \App\Models\CareRewardHistory;
use \App\Models\Service;

/**
 * 加算状況のテーブル操作に責任をもつクラス
 */
class AdditionStatusTable
{
    // 加算状況画面における登録処理対象カラム
    // modelに記載でもいい気がする
    const CARE_REWARD_HISTORY_TABLE_COLUMN = [
        // 種別32と37
        'consultation',
        'dementia_specialty',
        'discount',
        'emergency_response',
        'hospitalization_cost',
        'improvement_of_living_function',
        'improvement_of_specific_treatment',
        'initial',
        'juvenile_dementia',
        'medical_cooperation',
        'night_care',
        'night_care_over_capacity',
        'night_shift',
        'nursing_care',
        'nutrition_management',
        'oral_hygiene_management',
        'oral_screening',
        'over_capacity',
        'physical_restraint',
        'scientific_nursing',
        'section',
        'strengthen_service_system',
        'treatment_improvement',
        'vacancy',
        // 種別33追加
        'adl_maintenance_etc',
        'discharge_cooperation',
        'individual_function_training_1',
        'individual_function_training_2',
        'medical_institution_cooperation',
        'night_nursing_system',
        'service_form',
        'support_continued_occupancy',
        'support_persons_disabilities',
        // 種類55追加
        'safety_subtraction',
        'nutritional_subtraction',
        'recuperation_subtraction',
        'overnight_expenses_cost',
        'trial_exit_service_fee',
        'other_consultation_cost',
        're_entry_nutrition_cooperation',
        'before_leaving_visit_guidance',
        'after_leaving_visit_guidance',
        'leaving_guidance',
        'leaving_information_provision',
        'after_leaving_alignment',
        'home_visit_nursing_Instructions',
        'nutrition_management_strength',
        'oral_transfer',
        'oral_maintenance',
        'oral_hygiene',
        'recuperation_food',
        'home_return_support',
        'emergency_treatment',
        'severe_dementia_treatment',
        'excretion_support',
        'promotion_independence_support',
        'long_term_medical_treatment',
        'safety_measures_system',
        'severe_skin_ulcer',
        'drug_guidance',
        'group_communication_therapy',
        'physical_therapy',
        'occupational_therapy',
        'speech_hearing_therapy',
        'psychiatric_occupational_therapy',
        'other_rehabilitation_provision',
        'dementia_short_rehabilitation',
        'registered_nurse_ratio',
        'unit_care_undevelopment',
        // ベースアップ等支援加算
        'baseup'
    ];

    /**
     * 介護報酬履歴を全て取得して返す
     * @param array $params
     * @return array
     */
    public function getCareRewardHistories(array $params) : array
    {
        // 介護報酬履歴情報が事業所情報とリレーションしていないため、まずサービス情報を取得する
        $services = Service::
            where('facility_id', $params['facility_id'])
            ->where('service_type_code_id', $params['service_type_code_id'])
            ->select('id')
            ->get()
            ->toArray();
        if (count($services) == 0) {
            return [];
        }

        // サービスから介護報酬IDを全て取得する
        $careRewards = CareReward::
            whereIn('service_id', array_column($services, 'id'))
            ->select('id')
            ->get()
            ->toArray();
        if (count($careRewards) == 0) {
            return [];
        }

        // 介護報酬履歴IDから介護報酬履歴を全て取得する
        $careRewardHistories = CareRewardHistory::
            whereIn('care_reward_id', array_column($careRewards, 'id'))
            ->orderBy('start_month', 'desc')
            ->get()
            ->toArray();

        return $careRewardHistories;
    }

    /**
     * 介護報酬履歴の新規挿入をする
     * @param array $careRewardHistory
     * @param string $serviceId
     * @return int
     */
    public function insertCareRewardHistory(array $careRewardHistory, string $serviceId) : int
    {
        \DB::beginTransaction();
        try {
            // 介護報酬を新規挿入してIDを取得する
            $careRewardId = CareReward::insertGetId([
                'service_id' => $serviceId
            ]);

            // 介護報酬履歴を新規挿入する
            $careRewardHistory['care_reward_id'] = $careRewardId;
            $careRewardHistoryId = CareRewardHistory::insertGetId($careRewardHistory);

            \DB::commit();

            return $careRewardHistoryId;
        } catch (\Exception $th) {
            \DB::rollBack();
            throw $th;
        }
    }

    /**
     * 介護報酬履歴の更新をする。種別によって要不要のカラム、意味合いが変わるカラムがあるため改修が必要
     * @param array $params
     * @return void
     */
    public function updateCareRewardHistory(array $params) : void
    {
        $query = <<< SQL
UPDATE
    i_care_reward_histories
SET
-- 種別32と37
`consultation` = ?,
`dementia_specialty` = ?,
`discount` = ?,
`emergency_response` = ?,
`hospitalization_cost` = ?,
`improvement_of_living_function` = ?,
`improvement_of_specific_treatment` = ?,
`initial` = ?,
`juvenile_dementia` = ?,
`medical_cooperation` = ?,
`night_care` = ?,
`night_care_over_capacity` = ?,
`night_shift` = ?,
`nursing_care` = ?,
`nutrition_management` = ?,
`oral_hygiene_management` = ?,
`oral_screening` = ?,
`over_capacity` = ?,
`physical_restraint` = ?,
`scientific_nursing` = ?,
`section` = ?,
`strengthen_service_system` = ?,
`treatment_improvement` = ?,
`vacancy` = ?,
-- 種別33追加
`adl_maintenance_etc` = ?,
`discharge_cooperation` = ?,
`individual_function_training_1` = ?,
`individual_function_training_2` = ?,
`medical_institution_cooperation` = ?,
`night_nursing_system` = ?,
`service_form` = ?,
`support_continued_occupancy` = ?,
`support_persons_disabilities` = ?,
-- 種類55追加
`safety_subtraction` = ?,
`nutritional_subtraction` = ?,
`recuperation_subtraction` = ?,
`overnight_expenses_cost` = ?,
`trial_exit_service_fee` = ?,
`other_consultation_cost` = ?,
`re_entry_nutrition_cooperation` = ?,
`before_leaving_visit_guidance` = ?,
`after_leaving_visit_guidance` = ?,
`leaving_guidance` = ?,
`leaving_information_provision` = ?,
`after_leaving_alignment` = ?,
`home_visit_nursing_Instructions` = ?,
`nutrition_management_strength` = ?,
`oral_transfer` = ?,
`oral_maintenance` = ?,
`oral_hygiene` = ?,
`recuperation_food` = ?,
`home_return_support` = ?,
`emergency_treatment` = ?,
`severe_dementia_treatment` = ?,
`excretion_support` = ?,
`promotion_independence_support` = ?,
`long_term_medical_treatment` = ?,
`safety_measures_system` = ?,
`severe_skin_ulcer` = ?,
`drug_guidance` = ?,
`group_communication_therapy` = ?,
`physical_therapy` = ?,
`occupational_therapy` = ?,
`speech_hearing_therapy` = ?,
`psychiatric_occupational_therapy` = ?,
`other_rehabilitation_provision` = ?,
`dementia_short_rehabilitation` = ?,
`registered_nurse_ratio` = ?,
`unit_care_undevelopment` = ?,
-- ベースアップ等支援加算
`baseup` = ?,
`start_month` = ?,
`end_month` = ?
WHERE
    `id` = ?
SQL;
        \DB::select($query, self::createValues($params));

    }

    /**
     * 設定する値を生成する
     */
    private function createValues($params)
    {
        $requestValues = [];
        foreach (self::CARE_REWARD_HISTORY_TABLE_COLUMN as $key => $columnName) {
            $requestValues[] = array_key_exists($columnName, $params) ? $params[$columnName] : 1;
        }
        array_push($requestValues, $params['start_month'], $params['end_month'], $params['care_reward_histories_id']);
        return $requestValues;
    }
}
