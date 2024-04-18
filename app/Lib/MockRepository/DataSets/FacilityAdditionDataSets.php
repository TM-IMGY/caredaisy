<?php

namespace App\Lib\MockRepository\DataSets;

/**
 * 事業所加算のデータセット。
 * 初期はcsvで管理していたが可読性が低すぎたためクラスとして換装した。
 */
class FacilityAdditionDataSets
{
    public static function get()
    {
        return [
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 1,
                'facility_id' => 1,
                'service_item_code_id' => 50,
                'service_type_code_id' => 1
            ],
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 2,
                'facility_id' => 1,
                'service_item_code_id' => 52,
                'service_type_code_id' => 1
            ],
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 3,
                'facility_id' => 1,
                'service_item_code_id' => 58,
                'service_type_code_id' => 1
            ],
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 4,
                'facility_id' => 3,
                'service_item_code_id' => 2340,
                'service_type_code_id' => 6
            ],
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 5,
                'facility_id' => 3,
                'service_item_code_id' => 2344,
                'service_type_code_id' => 6
            ],
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 6,
                'facility_id' => 3,
                'service_item_code_id' => 2367,
                'service_type_code_id' => 6
            ],
            [
                'addition_end_date' => '2022/09/30',
                'addition_start_date' => '2022/09/01',
                'facility_addition_id' => 7,
                'facility_id' => 4,
                'service_item_code_id' => 58,
                'service_type_code_id' => 1
            ],
            [
                'addition_end_date' => '2022/09/30',
                'addition_start_date' => '2022/09/01',
                'facility_addition_id' => 8,
                'facility_id' => 4,
                'service_item_code_id' => 64,
                'service_type_code_id' => 1
            ],
            [
                'addition_end_date' => '2022/09/30',
                'addition_start_date' => '2022/09/01',
                'facility_addition_id' => 9,
                'facility_id' => 4,
                'service_item_code_id' => 65,
                'service_type_code_id' => 1
            ],
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 10,
                'facility_id' => 5,
                'service_item_code_id' => 2340,
                'service_type_code_id' => 6
            ],
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 11,
                'facility_id' => 5,
                'service_item_code_id' => 2344,
                'service_type_code_id' => 6
            ],
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 12,
                'facility_id' => 5,
                'service_item_code_id' => 2367,
                'service_type_code_id' => 6
            ],
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 13,
                'facility_id' => 6,
                'service_item_code_id' => 2340,
                'service_type_code_id' => 6
            ],
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 14,
                'facility_id' => 6,
                'service_item_code_id' => 2344,
                'service_type_code_id' => 6
            ],
            [
                'addition_end_date' => '2022/08/31',
                'addition_start_date' => '2022/08/01',
                'facility_addition_id' => 15,
                'facility_id' => 6,
                'service_item_code_id' => 2367,
                'service_type_code_id' => 6
            ],
            [
                'addition_end_date' => '2022/09/30',
                'addition_start_date' => '2022/09/01',
                'facility_addition_id' => 16,
                'facility_id' => 8,
                'service_item_code_id' => 58,
                'service_type_code_id' => 1
            ],
            [
                'addition_end_date' => '2022/09/30',
                'addition_start_date' => '2022/09/01',
                'facility_addition_id' => 17,
                'facility_id' => 8,
                'service_item_code_id' => 64,
                'service_type_code_id' => 1
            ],
            [
                'addition_end_date' => '2022/09/30',
                'addition_start_date' => '2022/09/01',
                'facility_addition_id' => 18,
                'facility_id' => 8,
                'service_item_code_id' => 65,
                'service_type_code_id' => 1
            ]
        ];
    }
}
