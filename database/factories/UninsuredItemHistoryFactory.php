<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UninsuredItemHistory;
use Faker\Generator as Faker;

$factory->define(UninsuredItemHistory::class, function (Faker $faker) {
    return [
        'uninsured_item_id' => 1,
        'item' => '朝食',
        'unit_cost' => 200,
        'unit' => 1,
        'set_one' => 1,
        'fixed_cost' => 0,
        'variable_cost' => 0,
        'welfare_equipment' => 0,
        'meal' => 1,
        'daily_necessary' => 0,
        'hobby' => 0,
        'escort' => 0,
        'reserved1' => 0,
        'reserved2' => 0,
        'reserved3' => 0,
        'reserved4' => 0,
        'reserved5' => 0
    ];
});
