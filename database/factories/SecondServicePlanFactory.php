<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\SecondServicePlan;
use Faker\Generator as Faker;

$factory->define(SecondServicePlan::class, function (Faker $faker) {
    return [
        'service_plan_id' => 1
    ];
});
