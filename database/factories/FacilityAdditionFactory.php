<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FacilityAddition;
use Faker\Generator as Faker;

$factory->define(FacilityAddition::class, function (Faker $faker) {
    return [
        'facility_id' => 2,
        'service_item_code_id' => 47,
        'addition_start_date' => '2021/11/01',
        'addition_end_date' => '2024/03/31'
    ];
});
