<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UninsuredRequest;
use Faker\Generator as Faker;

$factory->define(UninsuredRequest::class, function (Faker $faker) {
    return [
        'uninsured_item_history_id' => 1,
        'facility_user_id' => 2,
        'month' => '2021/12/01',
        'unit_cost' => 200,
        'sort' => 1
    ];
});
