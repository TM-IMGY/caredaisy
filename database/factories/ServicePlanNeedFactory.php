<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ServicePlanNeed;
use Faker\Generator as Faker;

$factory->define(ServicePlanNeed::class, function (Faker $faker) {
    return [
        'second_service_plan_id' => 1,
        'needs' => '【移動・歩行状態】
・買い物に行きたい
・日中は歩行器使用
・歩行不安定で転倒リスクあり
・夜間はトイレまで車いす',
        'sort' => 1
    ];
});
