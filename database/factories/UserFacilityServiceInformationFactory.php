<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UserFacilityServiceInformation;
use Faker\Generator as Faker;

$factory->define(UserFacilityServiceInformation::class, function (Faker $faker) {
    return [
        'facility_user_id' => 2,
        'facility_id' => 2,
        'usage_situation' => 1,
        'use_start' => '2021/11/01',
        'use_end' => '2024/10/31',
        'service_id' => 2
    ];
});
