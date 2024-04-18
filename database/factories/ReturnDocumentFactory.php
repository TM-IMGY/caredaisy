<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ReturnDocument;
use Faker\Generator as Faker;

$factory->define(ReturnDocument::class, function (Faker $faker) {
    return [
        'target_date' => $faker->dateTimeBetween( '-1 months', '2 months')->format('Y-m-1'),
        'facility_number' => 1472301025,
        'document_type' => $faker->regexify('[1-2]{1}'),
        'document_code' => $faker->regexify('[A-Z]{20}'),
        'title' => $faker->word,
        'published_at' => $faker->dateTimeBetween( '-6 months', '1 months'),
        'checked_at' => $faker->dateTimeBetween( '-6 months', '1 months')->format('Y-m-1')
    ];
});
