<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CareRewardHistory;
use Faker\Generator as Faker;

$factory->define(CareRewardHistory::class, function (Faker $faker) {
    return [
        'care_reward_id' => 1,
        'start_month' => '2021/11/01',
        'end_month' => '2024/03/31',
        'section' => 1,
        'vacancy' => 1,
        'night_shift' => 1,
        'night_care' => 1,
        'juvenile_dementia' => 1,
        'nursing_care' => 2,
        'medical_cooperation' => 2,
        'dementia_specialty' => 2,
        'strengthen_service_system' => 1,
        'treatment_improvement' => 6,
        'night_care_over_capacity' => 2,
        'improvement_of_living_function' => 2,
        'improvement_of_specific_treatment' => 1,
        'emergency_response' => 1,
        'over_capacity' => 1,
        'physical_restraint' => 1,
        'initial' => 1,
        'consultation' => 1,
        'nutrition_management' => 1,
        'oral_hygiene_management' => 1,
        'oral_screening' => 1,
        'scientific_nursing' => 1,
        'hospitalization_cost' => 1,
        'discount' => 1,
        'covid-19' => 1,
    ];
});
