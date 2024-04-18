<?php

namespace Tests\Factory;

use App\Lib\Entity\CareRewardHistory;
use Carbon\Carbon;

/**
 * テスト用の介護報酬履歴のファクトリ。
 */
class TestCareRewardHistoryFactory
{
    /**
     * ケア加算を生成する。
     * @param string $startMonth 開始月
     * @param string $endMonth 終了月
     */
    public function generateCareAddition(
        string $startMonth,
        string $endMonth
    ): CareRewardHistory {
        return new CareRewardHistory(
            // adl_maintenance_etc
            1,
            // care_reward_id
            1,
            // consultation
            1,
            // covid_19
            1,
            // dementia_specialty
            2,
            // discharge_cooperation
            1,
            // discount
            1,
            // emergency_response
            1,
            $endMonth,
            // hospitalization_cost
            1,
            // id
            1,
            // improvement_of_living_function
            1,
            // improvement_of_specific_treatment
            1,
            // individual_function_training_1
            1,
            // individual_function_training_2
            1,
            // initial
            1,
            // juvenile_dementia
            1,
            // medical_cooperation
            1,
            // medical_institution_cooperation
            1,
            // night_care
            1,
            // night_care_over_capacity
            1,
            // night_nursing_system
            1,
            // night_shift
            1,
            // nursing_care,
            1,
            // nutrition_management
            1,
            // oral_hygiene_management
            1,
            // oral_screening
            1,
            // over_capacity
            1,
            // physical_restraint
            1,
            // scientific_nursing
            1,
            // section
            1,
            // service_form
            1,
            $startMonth,
            // strengthen_service_system
            1,
            // support_continued_occupancy
            1,
            // support_persons_disabilities
            1,
            // treatment_improvement
            1,
            // vacancy
            1
        );
    }

    /**
     * 認知症対応型初期加算ありで生成する。
     * @param string $startMonth 開始月
     * @param string $endMonth 終了月
     */
    public function generateDementiaInitial(
        string $startMonth,
        string $endMonth
    ): CareRewardHistory {
        return new CareRewardHistory(
            // adl_maintenance_etc
            1,
            // care_reward_id
            1,
            // consultation
            1,
            // covid_19
            1,
            // dementia_specialty
            1,
            // discharge_cooperation
            1,
            // discount
            1,
            // emergency_response
            1,
            $endMonth,
            // hospitalization_cost
            1,
            // id
            1,
            // improvement_of_living_function
            1,
            // improvement_of_specific_treatment
            1,
            // individual_function_training_1
            1,
            // individual_function_training_2
            1,
            // initial
            2,
            // juvenile_dementia
            1,
            // medical_cooperation
            1,
            // medical_institution_cooperation
            1,
            // night_care
            1,
            // night_care_over_capacity
            1,
            // night_nursing_system
            1,
            // night_shift
            1,
            // nursing_care,
            1,
            // nutrition_management
            1,
            // oral_hygiene_management
            1,
            // oral_screening
            1,
            // over_capacity
            1,
            // physical_restraint
            1,
            // scientific_nursing
            1,
            // section
            1,
            // service_form
            1,
            $startMonth,
            // strengthen_service_system
            1,
            // support_continued_occupancy
            1,
            // support_persons_disabilities
            1,
            // treatment_improvement
            1,
            // vacancy
            1
        );
    }

    /**
     * 看取り介護加算を生成する。
     * @param string $startMonth 開始月
     * @param string $endMonth 終了月
     * @param int $nursingCare 看取り介護加算のランク。
     */
    public function generateEndOfLife(
        string $startMonth,
        string $endMonth,
        int $nursingCare
    ): CareRewardHistory {
        return new CareRewardHistory(
            // adl_maintenance_etc
            1,
            // care_reward_id
            1,
            // consultation
            1,
            // covid_19
            1,
            // dementia_specialty
            1,
            // discharge_cooperation
            1,
            // discount
            1,
            // emergency_response
            1,
            $endMonth,
            // hospitalization_cost
            1,
            // id
            1,
            // improvement_of_living_function
            1,
            // improvement_of_specific_treatment
            1,
            // individual_function_training_1
            1,
            // individual_function_training_2
            1,
            // initial
            1,
            // juvenile_dementia
            1,
            // medical_cooperation
            1,
            // medical_institution_cooperation
            1,
            // night_care
            1,
            // night_care_over_capacity
            1,
            // night_nursing_system
            1,
            // night_shift
            1,
            $nursingCare,
            // nutrition_management
            1,
            // oral_hygiene_management
            1,
            // oral_screening
            1,
            // over_capacity
            1,
            // physical_restraint
            1,
            // scientific_nursing
            1,
            // section
            1,
            // service_form
            1,
            $startMonth,
            // strengthen_service_system
            1,
            // support_continued_occupancy
            1,
            // support_persons_disabilities
            1,
            // treatment_improvement
            1,
            // vacancy
            1
        );
    }

   /**
     * 入院時費用を生成する。
     * @param string $startMonth 開始月
     * @param string $endMonth 終了月
     */
    public function generateHospitalization(
        string $startMonth,
        string $endMonth
    ): CareRewardHistory {
        return new CareRewardHistory(
            // adl_maintenance_etc
            1,
            // care_reward_id
            1,
            // consultation
            1,
            // covid_19
            1,
            // dementia_specialty
            1,
            // discharge_cooperation
            1,
            // discount
            1,
            // emergency_response
            1,
            $endMonth,
            // hospitalization_cost
            2,
            // id
            1,
            // improvement_of_living_function
            1,
            // improvement_of_specific_treatment
            1,
            // individual_function_training_1
            1,
            // individual_function_training_2
            1,
            // initial
            1,
            // juvenile_dementia
            1,
            // medical_cooperation
            1,
            // medical_institution_cooperation
            1,
            // night_care
            1,
            // night_care_over_capacity
            1,
            // night_nursing_system
            1,
            // night_shift
            1,
            // nursing_care,
            1,
            // nutrition_management
            1,
            // oral_hygiene_management
            1,
            // oral_screening
            1,
            // over_capacity
            1,
            // physical_restraint
            1,
            // scientific_nursing
            1,
            // section
            1,
            // service_form
            1,
            $startMonth,
            // strengthen_service_system
            1,
            // support_continued_occupancy
            1,
            // support_persons_disabilities
            1,
            // treatment_improvement
            1,
            // vacancy
            1
        );
    }

    /**
     * 初期状態で生成する。
     * @param string $startMonth 開始月
     * @param string $endMonth 終了月
     */
    public function generateInitial(string $startMonth, string $endMonth): CareRewardHistory
    {
        return new CareRewardHistory(
            // adl_maintenance_etc
            1,
            // care_reward_id
            1,
            // consultation
            1,
            // covid_19
            1,
            // dementia_specialty
            1,
            // discharge_cooperation
            1,
            // discount
            1,
            // emergency_response
            1,
            $endMonth,
            // hospitalization_cost
            1,
            // id
            1,
            // improvement_of_living_function
            1,
            // improvement_of_specific_treatment
            1,
            // individual_function_training_1
            1,
            // individual_function_training_2
            1,
            // initial
            1,
            // juvenile_dementia
            1,
            // medical_cooperation
            1,
            // medical_institution_cooperation
            1,
            // night_care
            1,
            // night_care_over_capacity
            1,
            // night_nursing_system
            1,
            // night_shift
            1,
            // nursing_care
            1,
            // nutrition_management
            1,
            // oral_hygiene_management
            1,
            // oral_screening
            1,
            // over_capacity
            1,
            // physical_restraint
            1,
            // scientific_nursing
            1,
            // section
            1,
            // service_form
            1,
            $startMonth,
            // strengthen_service_system
            1,
            // support_continued_occupancy
            1,
            // support_persons_disabilities
            1,
            // treatment_improvement
            1,
            // vacancy
            1
        );
    }

    /**
     * 若年性認知症受入加算ありで生成する。
     * @param int $juvenileDementia 若年性認知症受入加算
     * @param int $year 対象年
     * @param int $month 対象月
     */
    public function generateJuvenileDementia(
        int $juvenileDementia,
        int $year,
        int $month
    ): CareRewardHistory {
        $startMonth = "${year}-${month}-1";
        $endMonth = (new Carbon("${year}-${month}"))->lastOfMonth()->format('Y-m-d');

        return new CareRewardHistory(
            // adl_maintenance_etc
            1,
            // care_reward_id
            1,
            // consultation
            1,
            // covid_19
            1,
            // dementia_specialty
            1,
            // discharge_cooperation
            1,
            // discount
            1,
            // emergency_response
            1,
            $endMonth,
            // hospitalization_cost
            1,
            // id
            1,
            // improvement_of_living_function
            1,
            // improvement_of_specific_treatment
            1,
            // individual_function_training_1
            1,
            // individual_function_training_2
            1,
            // initial
            1,
            $juvenileDementia,
            // medical_cooperation
            1,
            // medical_institution_cooperation
            1,
            // night_care
            1,
            // night_care_over_capacity
            1,
            // night_nursing_system
            1,
            // night_shift
            1,
            // nursing_care,
            1,
            // nutrition_management
            1,
            // oral_hygiene_management
            1,
            // oral_screening
            1,
            // over_capacity
            1,
            // physical_restraint
            1,
            // scientific_nursing
            1,
            // section
            1,
            // service_form
            1,
            $startMonth,
            // strengthen_service_system
            1,
            // support_continued_occupancy
            1,
            // support_persons_disabilities
            1,
            // treatment_improvement
            1,
            // vacancy
            1
        );
    }

    /**
     * 退院退所時相談加算ありで生成する。
     * @param string $startMonth 開始月
     * @param string $endMonth 終了月
     */
    public function generateLeavingHospital(string $startMonth, string $endMonth): CareRewardHistory
    {
        return new CareRewardHistory(
            // adl_maintenance_etc
            1,
            // care_reward_id
            1,
            // consultation
            1,
            // covid_19
            1,
            // dementia_specialty
            1,
            // discharge_cooperation
            2,
            // discount
            1,
            // emergency_response
            1,
            $endMonth,
            // hospitalization_cost
            1,
            // id
            1,
            // improvement_of_living_function
            1,
            // improvement_of_specific_treatment
            1,
            // individual_function_training_1
            1,
            // individual_function_training_2
            1,
            // initial
            1,
            // juvenile_dementia
            1,
            // medical_cooperation
            1,
            // medical_institution_cooperation
            1,
            // night_care
            1,
            // night_care_over_capacity
            1,
            // night_nursing_system
            1,
            // night_shift
            1,
            // nursing_care
            1,
            // nutrition_management
            1,
            // oral_hygiene_management
            1,
            // oral_screening
            1,
            // over_capacity
            1,
            // physical_restraint
            1,
            // scientific_nursing
            1,
            // section
            1,
            // service_form
            1,
            $startMonth,
            // strengthen_service_system
            1,
            // support_continued_occupancy
            1,
            // support_persons_disabilities
            1,
            // treatment_improvement
            1,
            // vacancy
            1
        );
    }

    /**
     * 退居時相談援助加算ありで生成する。
     * @param string $startMonth 開始月
     * @param string $endMonth 終了月
     */
    public function generateMovingOutConsultation(
        string $startMonth,
        string $endMonth
    ): CareRewardHistory {
        return new CareRewardHistory(
            // adl_maintenance_etc
            1,
            // care_reward_id
            1,
            // consultation
            2,
            // covid_19
            1,
            // dementia_specialty
            1,
            // discharge_cooperation
            1,
            // discount
            1,
            // emergency_response
            1,
            $endMonth,
            // hospitalization_cost
            1,
            // id
            1,
            // improvement_of_living_function
            1,
            // improvement_of_specific_treatment
            1,
            // individual_function_training_1
            1,
            // individual_function_training_2
            1,
            // initial
            1,
            // juvenile_dementia
            1,
            // medical_cooperation
            1,
            // medical_institution_cooperation
            1,
            // night_care
            1,
            // night_care_over_capacity
            1,
            // night_nursing_system
            1,
            // night_shift
            1,
            // nursing_care
            1,
            // nutrition_management
            1,
            // oral_hygiene_management
            1,
            // oral_screening
            1,
            // over_capacity
            1,
            // physical_restraint
            1,
            // scientific_nursing
            1,
            // section
            1,
            // service_form
            1,
            $startMonth,
            // strengthen_service_system
            1,
            // support_continued_occupancy
            1,
            // support_persons_disabilities
            1,
            // treatment_improvement
            1,
            // vacancy
            1
        );
    }
}
