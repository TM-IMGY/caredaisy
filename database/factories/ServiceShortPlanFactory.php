<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ServiceShortPlan;
use Faker\Generator as Faker;

$factory->define(ServiceShortPlan::class, function (Faker $faker) {
    return [
        'service_long_plan_id' => 1,
        'goal' => '施設周辺を杖歩行できるようになる',
        'task_start' => '2021/11/01',
        'task_end' => '2022/03/31',
        'sort' => 1
    ];
});
