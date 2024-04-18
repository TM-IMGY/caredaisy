<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\StaffHistory;
use Faker\Generator as Faker;

$factory->define(StaffHistory::class, function (Faker $faker) {
    return [
        'staff_id' => 1,
        'facility_id' => 1,
        'name' => '',
        'name_kana' => '',
        'gender' => 1,
        'employment_status' => 1,
        'employment_class' => 1,
        'working_status' => 1,
    ];
});
