<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Auth;
use Faker\Generator as Faker;

$factory->define(Auth::class, function (Faker $faker) {
    return [
        'request' => '[]',
        'authority' => '[]',
        'care_plan' => '[]',
        'facility' => '[]',
        'facility_user_1' => '[]',
        'facility_user_2' => '[]'        
    ];
});
