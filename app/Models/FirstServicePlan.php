<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirstServicePlan extends Model
{

    /* 計画書区分 */
    public const PLAN_DIVISION_FIRST_TIME                    = 1; // 初回
    public const PLAN_DIVISION_INTRODUCTION                  = 2; // 紹介
    public const PLAN_DIVISION_CONTINUATION                  = 3; // 継続
    public const PLAN_DIVISION_INTRODUCTION_AND_CONTINUATION = 5; // 紹介＆継続

    protected $table = 'i_first_service_plans';
    protected $connection = 'mysql';
    protected $guarded = [
        'id',
    ];
}
