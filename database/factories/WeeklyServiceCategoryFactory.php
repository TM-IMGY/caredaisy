<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\WeeklyServiceCategory;
use Faker\Generator as Faker;

$factory->define(WeeklyServiceCategory::class, function (Faker $faker) {
    return [
        'facility_id' => 1,
        'type' => 0,
        'description' => $faker->lastName(), // 日本語対応しているメソッド
    ];
});

$factory->state(WeeklyServiceCategory::class, 'common', [
    'facility_id' => 0
]);

$factory->state(WeeklyServiceCategory::class, 'weekly', [
    'type' => 0
]);

$factory->state(WeeklyServiceCategory::class, 'daily', [
    'type' => 8
]);

$factory->state(WeeklyServiceCategory::class, 'notWeekly', [
    'type' => 9
]);