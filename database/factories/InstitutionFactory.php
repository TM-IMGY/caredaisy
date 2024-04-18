<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Institution;
use Faker\Generator as Faker;

$factory->define(Institution::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'abbreviation' => $faker->word,
        'representative' => $faker->word,
        'phone_number' => $faker->phoneNumber,
        'fax_number' => $faker->phoneNumber,
        'postal_code' => $faker->randomNumber(8, $strict = true),
        'location' => $faker->address,
        'remarks' => $faker->sentence($nbWords = 6, $variableNbWords = true)
    ];
});
