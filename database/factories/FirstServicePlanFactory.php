<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FirstServicePlan;
use Faker\Generator as Faker;

$factory->define(FirstServicePlan::class, function (Faker $faker) {
    return [
        'service_plan_id' => 1,
        'plan_division' => 1,
        'living_alone' => 1,
        'handicapped' => 0,
        'other' => 0,
        'title1' => '利用者及び家族の生活に対する意向を踏まえた課題分析の結果',
        'content1' => '（例）買い物を毎週したい（本人）',
        'title2' => '介護認定審査会の意見及びサービスの種類の指定',
        'content2' => '（例）特になし。要介護状態が長期にわたって変化しないと考えられるので、認定有効期間を12カ月に延長する',
        'title3' => '総合的な援助の方針',
        'content3' => '（例）機能訓練指導員と連携し下肢筋力を維持していく。'
    ];
});
