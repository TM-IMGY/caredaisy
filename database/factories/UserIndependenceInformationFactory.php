<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UserIndependenceInformation;
use Faker\Generator as Faker;

$factory->define(UserIndependenceInformation::class, function (Faker $faker) {
    return [
        'facility_user_id' => 2,
        'independence_level' => 8,
        'dementia_level' => 8,
        'judgment_date' => '2021/11/01',
        'judger' => '介護太郎'
    ];
});
