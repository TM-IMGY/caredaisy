<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Approval;
use Faker\Generator as Faker;

$factory->define(Approval::class, function (Faker $faker) {
    return [
        'facility_user_id' => 2,
        'facility_id' => 2,
        'month' => '2021/12/01',
        'approval_type' => 1,
        'approval_flag' => 1
    ];
});
