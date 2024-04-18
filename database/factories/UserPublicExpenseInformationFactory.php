<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UserPublicExpenseInformation;
use Faker\Generator as Faker;

$factory->define(UserPublicExpenseInformation::class, function (Faker $faker) {
    return [
        'facility_user_id' => 2,
        'bearer_number' => 12000003,
        'recipient_number' => 1234567,
        'confirmation_medical_insurance_date' => '2021/11/01',
        'effective_start_date' => '2021/11/01',
        'expiry_date' => '2024/10/31'
    ];
});
