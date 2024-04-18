<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\WeeklyService;
use Faker\Generator as Faker;

$factory->define(WeeklyService::class, function (Faker $faker) {
    return [
        'facility_id' => 1,
        'description' => $faker->lastName(), // 日本語対応しているメソッド,
    ];
});

$factory->state(WeeklyService::class, 'common', [
    'facility_id' => 0
]);

$factory->state(WeeklyService::class, 'weekly', [
    'type' => 0
]);

$factory->state(WeeklyService::class, 'daily', [
    'type' => 8
]);

$factory->state(WeeklyService::class, 'notWeekly', [
    'type' => 9
]);
