<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\StayOutManagement;
use Faker\Generator as Faker;

$factory->define(StayOutManagement::class, function (Faker $faker) {
    return [
        'facility_user_id' => 1,
        'start_date' => $faker->dateTime(),
        'meal_of_the_day_start_morning' => 0,
        'meal_of_the_day_start_lunch' => 0,
        'meal_of_the_day_start_snack' => 0,
        'meal_of_the_day_start_dinner' => 0,
        'end_date' => $faker->dateTime(),
        'meal_of_the_day_end_morning' => 0,
        'meal_of_the_day_end_lunch' => 0,
        'meal_of_the_day_end_snack' => 0,
        'meal_of_the_day_end_dinner' => 0,
        'reason_for_stay_out' => 1,
        'remarks_reason_for_stay_out' => $faker->word,
        'remarks' => $faker->word,
    ];
});
