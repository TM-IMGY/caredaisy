<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Service;
use Faker\Generator as Faker;

$factory->define(Service::class, function (Faker $faker) {
    return [
        'facility_id' => 1,
        'service_type_code_id' => 1,
        'area' => 5,
        'change_date' => '2021/12/01'
    ];
});
