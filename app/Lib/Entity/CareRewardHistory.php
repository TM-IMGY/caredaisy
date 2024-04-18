<?php

namespace App\Lib\Entity;

use App\Lib\DomainService\CareAdditionSpecification;
use App\Lib\DomainService\DementiaInitialAdditionSpecification;
use App\Lib\DomainService\EndOfLifeCareAdditionSpecification;
use App\Lib\DomainService\JuvenileDementiaSpecification;
use App\Lib\DomainService\LeavingHospitalSpecification;
use App\Lib\DomainService\MovingOutConsultationSpecification;
use App\Lib\DomainService\StayOutSpecification;

/**
 * 介護報酬履歴。
 */
class CareRewardHistory
{
    private int $adlMaintenanceEtc;
    private ?int $careRewardId;
    private int $consultation;
    private int $covid19;
    private int $dementiaSpecialty;
    private int $dischargeCooperation;
    private int $discount;
    private int $emergencyResponse;
    private string $endMonth;
    private int $hospitalizationCost;
    private ?int $id;
    private int $improvementOfLivingFunction;
    private int $improvementOfSpecificTreatment;
    private int $individualFunctionTraining1;
    private int $individualFunctionTraining2;
    private int $initial;
    private int $juvenileDementia;
    private int $medicalCooperation;
    private int $medicalInstitutionCooperation;
    private int $nightCare;
    private int $nightCareOverCapacity;
    private int $nightNursingSystem;
    private int $nightShift;
    private int $nursingCare;
    private int $nutritionManagement;
    private int $oralHygieneManagement;
    private int $oralScreening;
    private int $overCapacity;
    private int $physicalRestraint;
    private int $scientificNursing;
    private int $section;
    private int $serviceForm;
    private string $startMonth;
    private int $strengthenServiceSystem;
    private int $supportContinuedOccupancy;
    private int $supportPersonsDisabilities;
    private int $treatmentImprovement;
    private int $vacancy;

    public function __construct(
        int $adlMaintenanceEtc,
        ?int $careRewardId,
        int $consultation,
        int $covid19,
        int $dementiaSpecialty,
        int $dischargeCooperation,
        int $discount,
        int $emergencyResponse,
        string $endMonth,
        int $hospitalizationCost,
        ?int $id,
        int $improvementOfLivingFunction,
        int $improvementOfSpecificTreatment,
        int $individualFunctionTraining1,
        int $individualFunctionTraining2,
        int $initial,
        int $juvenileDementia,
        int $medicalCooperation,
        int $medicalInstitutionCooperation,
        int $nightCare,
        int $nightCareOverCapacity,
        int $nightNursingSystem,
        int $nightShift,
        int $nursingCare,
        int $nutritionManagement,
        int $oralHygieneManagement,
        int $oralScreening,
        int $overCapacity,
        int $physicalRestraint,
        int $scientificNursing,
        int $section,
        int $serviceForm,
        string $startMonth,
        int $strengthenServiceSystem,
        int $supportContinuedOccupancy,
        int $supportPersonsDisabilities,
        int $treatmentImprovement,
        int $vacancy
    ) {
        $this->adlMaintenanceEtc = $adlMaintenanceEtc;
        $this->careRewardId = $careRewardId;
        $this->consultation = $consultation;
        $this->covid19 = $covid19;
        $this->dementiaSpecialty = $dementiaSpecialty;
        $this->dischargeCooperation = $dischargeCooperation;
        $this->discount = $discount;
        $this->emergencyResponse = $emergencyResponse;
        $this->endMonth = $endMonth;
        $this->hospitalizationCost = $hospitalizationCost;
        $this->id = $id;
        $this->improvementOfLivingFunction = $improvementOfLivingFunction;
        $this->improvementOfSpecificTreatment = $improvementOfSpecificTreatment;
        $this->individualFunctionTraining1 = $individualFunctionTraining1;
        $this->individualFunctionTraining2 = $individualFunctionTraining2;
        $this->initial = $initial;
        $this->juvenileDementia = $juvenileDementia;
        $this->medicalCooperation = $medicalCooperation;
        $this->medicalInstitutionCooperation = $medicalInstitutionCooperation;
        $this->nightCare = $nightCare;
        $this->nightCareOverCapacity = $nightCareOverCapacity;
        $this->nightNursingSystem = $nightNursingSystem;
        $this->nightShift = $nightShift;
        $this->nursingCare = $nursingCare;
        $this->nutritionManagement = $nutritionManagement;
        $this->oralHygieneManagement = $oralHygieneManagement;
        $this->oralScreening = $oralScreening;
        $this->overCapacity = $overCapacity;
        $this->physicalRestraint = $physicalRestraint;
        $this->scientificNursing = $scientificNursing;
        $this->section = $section;
        $this->serviceForm = $serviceForm;
        $this->startMonth = $startMonth;
        $this->strengthenServiceSystem = $strengthenServiceSystem;
        $this->supportContinuedOccupancy = $supportContinuedOccupancy;
        $this->supportPersonsDisabilities = $supportPersonsDisabilities;
        $this->treatmentImprovement = $treatmentImprovement;
        $this->vacancy = $vacancy;
    }

    public function getNursingCare(): int
    {
        return $this->nursingCare;
    }

    /**
     * TODO: JsonSerializable インターフェース利用の方がいいか?
     */
    public function getData(): array
    {
        return [
            'adl_maintenance_etc' => $this->adlMaintenanceEtc,
            'care_reward_id' => $this->careRewardId,
            'consultation' => $this->consultation,
            'covid19' => $this->covid19,
            'dementia_specialty' => $this->dementiaSpecialty,
            'discharge_cooperation' => $this->dischargeCooperation,
            'discount' => $this->discount,
            'emergency_response' => $this->emergencyResponse,
            'end_month' => $this->endMonth,
            'hospitalization_cost' => $this->hospitalizationCost,
            'id' => $this->id,
            'improvement_of_living_function' => $this->improvementOfLivingFunction,
            'improvement_of_specific_treatment' => $this->improvementOfSpecificTreatment,
            'individual_function_training_1' => $this->individualFunctionTraining1,
            'individual_function_training_2' => $this->individualFunctionTraining2,
            'initial' => $this->initial,
            'juvenile_dementia' => $this->juvenileDementia,
            'medical_cooperation' => $this->medicalCooperation,
            'medical_institution_cooperation' => $this->medicalInstitutionCooperation,
            'night_care' => $this->nightCare,
            'night_care_over_capacity' => $this->nightCareOverCapacity,
            'night_nursing_system' => $this->nightNursingSystem,
            'night_shift' => $this->nightShift,
            'nursing_care' => $this->nursingCare,
            'nutrition_management' => $this->nutritionManagement,
            'oral_hygiene_management' => $this->oralHygieneManagement,
            'oral_screening' => $this->oralScreening,
            'over_capacity' => $this->overCapacity,
            'physical_restraint' => $this->physicalRestraint,
            'scientific_nursing' => $this->scientificNursing,
            'section' => $this->section,
            'service_form' => $this->serviceForm,
            'start_month' => $this->startMonth,
            'strengthen_service_system' => $this->strengthenServiceSystem,
            'support_continued_occupancy' => $this->supportContinuedOccupancy,
            'support_persons_disabilities' => $this->supportPersonsDisabilities,
            'treatment_improvement' => $this->treatmentImprovement,
            'vacancy' => $this->vacancy
        ];
    }

    public function getDementiaSpecialty(): int
    {
        return $this->dementiaSpecialty;
    }

    public function isDementiaSpecialtyAvailable(): bool
    {
        return $this->dementiaSpecialty > CareAdditionSpecification::NO_ADDITION;
    }

    /**
     * 特定施設退院退所がありかを返す。
     * @return bool;
     */
    public function isLeavingHospitalAvailable(): bool
    {
        return $this->dischargeCooperation === LeavingHospitalSpecification::ADDITIONAL;
    }

    /**
     * 認知症対応型初期加算がありかを返す。
     * @return bool
     */
    public function isInitialAvailable(): bool
    {
        return $this->initial === DementiaInitialAdditionSpecification::ADDITIONAL;
    }

    /**
     * 若年性認知症受入加算がありかを返す。
     */
    public function isJuvenileDementiaAvailable(): bool
    {
        return $this->juvenileDementia === JuvenileDementiaSpecification::ADDITIONAL;
    }

    /**
     * 退居時相談援助加算がありかを返す。
     */
    public function isMovingOutConsultationAvailable(): bool
    {
        return $this->consultation === MovingOutConsultationSpecification::ADDITIONAL;
    }

    /**
     * 看取り介護加算がありかを返す。
     */
    public function isNursingCareAvailable(): bool
    {
        return $this->nursingCare === EndOfLifeCareAdditionSpecification::ADDITIONAL
            || $this->nursingCare === EndOfLifeCareAdditionSpecification::ADDITION_1;
    }

    /**
     * 入院時費用がありかを返す。
     * @return bool
     */
    public function isHospitalizationCost(): bool
    {
        return $this->hospitalizationCost === StayOutSpecification::HOSPITALIZATION_COST;
    }
}
