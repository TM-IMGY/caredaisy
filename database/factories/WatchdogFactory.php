<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Watchdog;
use Faker\Generator as Faker;

$factory->define(Watchdog::class, function (Faker $faker) {
    return [
        'target_function' => $faker->word
    ];
});
