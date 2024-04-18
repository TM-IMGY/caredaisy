<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ServicePlan;
use Faker\Generator as Faker;

$factory->define(ServicePlan::class, function (Faker $faker) {
    return [
        'facility_user_id' => 2,
        'plan_start_period' => '2021/11/01',
        'plan_end_period' => '介護花子',
        'status' => 1,
        'certification_status' => 2,
        'recognition_date' => '2021/11/01',
        'care_period_start' => '2021/11/01',
        'care_period_end' => '2024/10/31',
        'care_level_name' => '要介護３',
        'independence_level' => 2,
        'dementia_level' => 7
    ];
});
