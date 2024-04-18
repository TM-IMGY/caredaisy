<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UserCareInformation;
use Faker\Generator as Faker;

$factory->define(UserCareInformation::class, function (Faker $faker) {
    return [
        'facility_user_id' => 2,
        'care_level_id' => 8,
        'certification_status' => 2,
        'recognition_date' => '2021/11/01',
        'care_period_start' => '2021/11/01',
        'care_period_end' => '2024/10/31',
        'date_confirmation_insurance_card' => '2021/11/01',
        'date_qualification' => '2021/11/01'
    ];
});
