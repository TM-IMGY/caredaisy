<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CareReward;
use Faker\Generator as Faker;

$factory->define(CareReward::class, function (Faker $faker) {
    return [
        'service_id' => 2
    ];
});
