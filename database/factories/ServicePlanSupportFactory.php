<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ServicePlanSupport;
use Faker\Generator as Faker;

$factory->define(ServicePlanSupport::class, function (Faker $faker) {
    return [
        'service_short_plan_id' => 1,
        'service' => '①週１回機能訓練指導員のリハビリを実施する',                
        'staff' => '機能訓練指導員',
        'frequency' => '毎日',
        'task_start' => '2021/11/01',
        'task_end' => '2022/03/31',
        'sort' => 1
    ];
});
