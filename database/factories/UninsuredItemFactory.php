<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UninsuredItem;
use Faker\Generator as Faker;

$factory->define(UninsuredItem::class, function (Faker $faker) {
    return [
        'service_id' => 2,
        'start_month' => '2021/11/01'        
    ];
});
