<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Invoice;
use Faker\Generator as Faker;

$factory->define(Invoice::class, function (Faker $faker) {
    return [
        'target_date' => $faker->dateTimeBetween( '-1 months', '2 months')->format('Y-m-1'),
        'service_date' => $faker->dateTimeBetween( '-1 months', '2 months')->format('Y-m-1'),
        'facility_number' => 1472301025,
        'facility_user_count' => $faker->randomNumber(2, $strict = true),
        'billing_amount' => $faker->randomNumber(5, $strict = true),
        'accept_code' => $faker->regexify('[A-Z]{1,19}'),
        'cancel_code' => $faker->regexify('[A-Z]{1,19}'),
        'basic_status' => $faker->regexify('[A-Z]{1,4}'),
        'sub_status' => $faker->regexify('[A-Z]{1}'),
        'status' => $faker->regexify('[0-9]{1}'),
        'sent_at' => $faker->dateTimeBetween( '-1 months', '2 months'),
    ];
});
