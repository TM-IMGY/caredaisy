<?php

// DBバージョン 2021/9/1時点

use App\Models\ServiceResult;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(ServiceResult::class, function (Faker $faker) {
    return [
        'Approval' => 0,
        'calc_kind' => 1,
        'classification_support_limit_over' => null,
        'date_daily_rate' => '1111111111111111111111111111110',
        'days_short_stay_previous_month' => null,
        'document_create_date' => date("Y/m/d H:i:s"),
        'facility_id' => '0',
        'facility_name_kanji' => 'ServiceResultFactoryダミー',
        'facility_number' => '0000000000',
        'facility_user_id' => 0,
        'first_service_plan_id' => 0,
        'service_code' => '322141',
        'service_count' => 30,
        'service_count_date' => 30,
        'service_end_time' => 9999,
        'service_start_time' => 9999,
        'service_use_date' => date("Y/m/d H:i:s"),
        'target_date' => date("Y/m/d"),
    ];
});
