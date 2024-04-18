<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\AuthExtent;
use Faker\Generator as Faker;

$factory->define(AuthExtent::class, function (Faker $faker) {
    return [
        'staff_id' => 1,
        'auth_id' => 1,
        'corporation_id' => 1,
        'institution_id' => 1,
        'facility_id' => 1,
        'start_date' => '2021-01-01'
    ];
});
