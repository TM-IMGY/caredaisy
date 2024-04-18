<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Facility;
use Faker\Generator as Faker;

$factory->define(Facility::class, function (Faker $faker) {
    return [
        'facility_number' => $faker->randomNumber(9, $strict = true),
        'facility_name_kanji' => $faker->word,
        'facility_name_kana' => $faker->word,
        'insurer_no' => $faker->randomNumber(6, $strict = true),
        'area' => '5',
        'postal_code' => $faker->randomNumber(8, $strict = true),
        'location' => $faker->address,
        'phone_number' => $faker->phoneNumber,
        'fax_number' => $faker->phoneNumber,
        'remarks' => $faker->sentence($nbWords = 6, $variableNbWords = true),
        'invalid_flag' => 0,
        'abbreviation' => $faker->word,
        'facility_manager' => $faker->word        
    ];
});
