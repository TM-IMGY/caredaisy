<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FacilityUser;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Crypt;

$factory->define(FacilityUser::class, function (Faker $faker) {
    return [
        'insurer_no' => Crypt::encrypt($faker->word),
        'insured_no' => Crypt::encrypt($faker->word),
        'last_name' => Crypt::encrypt($faker->word),
        'first_name' => Crypt::encrypt($faker->word),
        'last_name_kana' => Crypt::encrypt($faker->word),
        'first_name_kana' => Crypt::encrypt($faker->word),
        'gender' => '1',
        'birthday' => $faker->dateTimeBetween( '-100 years', '-70 years'),
        'postal_code' => Crypt::encrypt($faker->randomNumber(8, $strict = true)),
        'location1' => Crypt::encrypt($faker->word),
        'location2' => Crypt::encrypt($faker->word),
        'phone_number' => Crypt::encrypt($faker->phoneNumber),
        'start_date' => $faker->dateTimeBetween( '-100 years', '-70 years'),
        'end_date' => $faker->dateTimeBetween( '-100 years', '-70 years'),
        'death_date' => $faker->dateTimeBetween( '-100 years', '-70 years'),
        'death_reason' => $faker->word,
        'remarks' => $faker->sentence($nbWords = 6, $variableNbWords = true),
        'blood_type' => '1',
        'rh_type' => '1',
        'cell_phone_number' => Crypt::encrypt($faker->phoneNumber),
        'before_in_status_id' => '1',
        'diagnosis_date' => $faker->dateTimeBetween( '-100 years', '-70 years'),
        'diagnostician' => Crypt::encrypt($faker->sentence($nbWords = 2, $variableNbWords = true)),
        'consent_date' => $faker->dateTimeBetween( '-100 years', '-70 years'),
        'consenter' => Crypt::encrypt($faker->sentence($nbWords = 2, $variableNbWords = true)),
        'consenter_phone_number' => Crypt::encrypt($faker->phoneNumber),
        'invalid_flag' => '0'
        
    ];
});
