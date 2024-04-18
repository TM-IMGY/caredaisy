<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ServiceLongPlan;
use Faker\Generator as Faker;

$factory->define(ServiceLongPlan::class, function (Faker $faker) {
    return [
        'service_plan_need_id' => 1,
        'goal' => '月に１回買い物に行くことができるようになる',
        'task_start' => '2021/11/01',
        'task_end' => '2022/03/31',
        'sort' => 1
    ];
});
