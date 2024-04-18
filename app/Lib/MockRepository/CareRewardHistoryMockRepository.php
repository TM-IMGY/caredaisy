<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\CareRewardHistoryRepositoryInterface;
use App\Lib\Entity\CareRewardHistory;
use App\Lib\MockRepository\DataSets\CareRewardHistoryDataSets;

/**
 * 介護報酬履歴のモックリポジトリのクラス。
 */
class CareRewardHistoryMockRepository implements CareRewardHistoryRepositoryInterface
{
    /**
     * サービスIDから介護報酬履歴を取得して返す。
     * TODO: 対象年月で絞り込む。
     * @param int $serviceId 事業所のサービスのID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return array
     */
    public function find(int $serviceId, int $year, int $month): CareRewardHistory
    {
        $dataSets = CareRewardHistoryDataSets::get();
        $careRewardHistoryRecord = null;
        foreach ($dataSets as $record) {
            if ($record['service_id'] === $serviceId) {
                $careRewardHistoryRecord = $record;
                break;
            }
        }

        $careRewardHistory = new CareRewardHistory(
            $careRewardHistoryRecord['adl_maintenance_etc'],
            $careRewardHistoryRecord['care_reward_id'],
            $careRewardHistoryRecord['consultation'],
            $careRewardHistoryRecord['covid_19'],
            $careRewardHistoryRecord['dementia_specialty'],
            $careRewardHistoryRecord['discharge_cooperation'],
            $careRewardHistoryRecord['discount'],
            $careRewardHistoryRecord['emergency_response'],
            $careRewardHistoryRecord['end_month'],
            $careRewardHistoryRecord['hospitalization_cost'],
            $careRewardHistoryRecord['id'],
            $careRewardHistoryRecord['improvement_of_living_function'],
            $careRewardHistoryRecord['improvement_of_specific_treatment'],
            $careRewardHistoryRecord['individual_function_training_1'],
            $careRewardHistoryRecord['individual_function_training_2'],
            $careRewardHistoryRecord['initial'],
            $careRewardHistoryRecord['juvenile_dementia'],
            $careRewardHistoryRecord['medical_cooperation'],
            $careRewardHistoryRecord['medical_institution_cooperation'],
            $careRewardHistoryRecord['night_care'],
            $careRewardHistoryRecord['night_care_over_capacity'],
            $careRewardHistoryRecord['night_nursing_system'],
            $careRewardHistoryRecord['night_shift'],
            $careRewardHistoryRecord['nursing_care'],
            $careRewardHistoryRecord['nutrition_management'],
            $careRewardHistoryRecord['oral_hygiene_management'],
            $careRewardHistoryRecord['oral_screening'],
            $careRewardHistoryRecord['over_capacity'],
            $careRewardHistoryRecord['physical_restraint'],
            $careRewardHistoryRecord['scientific_nursing'],
            $careRewardHistoryRecord['section'],
            $careRewardHistoryRecord['service_form'],
            $careRewardHistoryRecord['start_month'],
            $careRewardHistoryRecord['strengthen_service_system'],
            $careRewardHistoryRecord['support_continued_occupancy'],
            $careRewardHistoryRecord['support_persons_disabilities'],
            $careRewardHistoryRecord['treatment_improvement'],
            $careRewardHistoryRecord['vacancy']
        );

        return $careRewardHistory;
    }
}
