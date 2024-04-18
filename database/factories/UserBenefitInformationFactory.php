<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UserBenefitInformation;
use Faker\Generator as Faker;

$factory->define(UserBenefitInformation::class, function (Faker $faker) {
    return [
        'facility_user_id' => 2,
        'benefit_type' => 1,
        'benefit_rate' => 90,
        'effective_start_date' => '2021/11/01',
        'expiry_date' => '2024/10/31'
    ];
});
