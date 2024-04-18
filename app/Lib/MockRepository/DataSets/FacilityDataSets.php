<?php

namespace App\Lib\MockRepository\DataSets;

/**
 * 事業所のデータセット。
 * 初期はcsvで管理していたが可読性が低すぎたためクラスとして換装した。
 */
class FacilityDataSets
{
    // 事例 公費あり(月途中)
    public const CASE_32_PATTERN_1_ID = 1;

    // 種類32
    public const CASE_32_PATTERN_2_ID = 2;

    // 事例 種類55 公費あり 特定入所者介護サービスあり
    public const CASE_55_PATTERN_1_ID = 3;

    // 事例 種類32 公費なし
    public const CASE_32_PATTERN_3_ID = 4;

    // 事例 種類55 公費あり 本人支払い額あり
    public const CASE_55_PATTERN_2_ID = 5;

    // 事例 種類55 被保険者番号H 公費あり 特定入所者介護サービスあり
    public const CASE_55_PATTERN_3_ID = 6;

    // ユースケース 自動サービスコード テスト用
    public const USE_CASE_AUTO_SERVICE_CODE_TEST = 7;

    // 事例 種類32 看取りあり
    public const CASE_32_PATTERN_4_ID = 8;

    public static function get()
    {
        return [
            [
                'abbreviation' => null,
                'allow_transmission' => 1,
                'area' => 1,
                'facility_id' => 1,
                'facility_manager' => null,
                'facility_name_kana' => 'ジギョウショ',
                'facility_name_kanji' => '事業所',
                'facility_number' => '0000000001',
                'fax_number' => null,
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'institution_id' => 1,
                'location' => '住所',
                'phone_number' => '0000000000',
                'postal_code' => '0000000',
                'remarks' => null,
                'services' => [
                    [
                        'area' => 1,
                        'area_unit_price_1' => 1090,
                        'area_unit_price_2' => 1072,
                        'area_unit_price_3' => 1068,
                        'area_unit_price_4' => 1054,
                        'area_unit_price_5' => 1045,
                        'area_unit_price_6' => 1027,
                        'area_unit_price_7' => 1014,
                        'area_unit_price_8' => 1000,
                        'area_unit_price_9' => null,
                        'area_unit_price_10' => null,
                        'change_date' => '2022/08/01',
                        'facility_id' => 1,
                        'first_plan_input' => 0,
                        'id' => 1,
                        'service_end_date' => '9999/12/01',
                        'service_start_date' => '2021/04/01',
                        'service_type_code' => '32',
                        'service_type_code_id' => 1,
                        'service_type_name' => '認知症対応型共同生活介護'
                    ]
                ]
            ],
            [
                'abbreviation' => null,
                'allow_transmission' => 1,
                'area' => 1,
                'facility_id' => 2,
                'facility_manager' => null,
                'facility_name_kana' => 'ジギョウショ',
                'facility_name_kanji' => '事業所',
                'facility_number' => '0000000001',
                'fax_number' => null,
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'institution_id' => 2,
                'location' => '住所',
                'phone_number' => '0000000000',
                'postal_code' => '0000000',
                'remarks' => null,
                'services' => [
                    [
                        'area' => 1,
                        'area_unit_price_1' => 1090,
                        'area_unit_price_2' => 1072,
                        'area_unit_price_3' => 1068,
                        'area_unit_price_4' => 1054,
                        'area_unit_price_5' => 1045,
                        'area_unit_price_6' => 1027,
                        'area_unit_price_7' => 1014,
                        'area_unit_price_8' => 1000,
                        'area_unit_price_9' => null,
                        'area_unit_price_10' => null,
                        'change_date' => '2022/09/01',
                        'facility_id' => 2,
                        'first_plan_input' => 0,
                        'id' => 2,
                        'service_end_date' => '9999/12/01',
                        'service_start_date' => '2021/04/01',
                        'service_type_code' => '32',
                        'service_type_code_id' => 1,
                        'service_type_name' => '認知症対応型共同生活介護'
                    ]
                ]
            ],
            [
                'abbreviation' => null,
                'allow_transmission' => 1,
                'area' => 1,
                'facility_id' => 3,
                'facility_manager' => null,
                'facility_name_kana' => 'ジギョウショ',
                'facility_name_kanji' => '事業所',
                'facility_number' => '0000000001',
                'fax_number' => null,
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'institution_id' => 3,
                'location' => '住所',
                'phone_number' => '0000000000',
                'postal_code' => '0000000',
                'remarks' => null,
                'services' => [
                    [
                        'area' => 7,
                        'area_unit_price_1' => 1090,
                        'area_unit_price_2' => 1072,
                        'area_unit_price_3' => 1068,
                        'area_unit_price_4' => 1054,
                        'area_unit_price_5' => 1045,
                        'area_unit_price_6' => 1027,
                        'area_unit_price_7' => 1014,
                        'area_unit_price_8' => 1000,
                        'area_unit_price_9' => null,
                        'area_unit_price_10' => null,
                        'change_date' => '2022/08/01',
                        'facility_id' => 3,
                        'first_plan_input' => 0,
                        'id' => 3,
                        'service_end_date' => '9999/12/01',
                        'service_start_date' => '2021/04/01',
                        'service_type_code' => '55',
                        'service_type_code_id' => 6,
                        'service_type_name' => '介護医療院'
                    ]
                ]
            ],
            [
                'abbreviation' => null,
                'allow_transmission' => 1,
                'area' => 5,
                'facility_id' => 4,
                'facility_manager' => null,
                'facility_name_kana' => 'ジギョウショ',
                'facility_name_kanji' => '事業所',
                'facility_number' => '0000000001',
                'fax_number' => null,
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'institution_id' => 4,
                'location' => '住所',
                'phone_number' => '0000000000',
                'postal_code' => '0000000',
                'remarks' => null,
                'services' => [
                    [
                        'area' => 5,
                        'area_unit_price_1' => 1090,
                        'area_unit_price_2' => 1072,
                        'area_unit_price_3' => 1068,
                        'area_unit_price_4' => 1054,
                        'area_unit_price_5' => 1045,
                        'area_unit_price_6' => 1027,
                        'area_unit_price_7' => 1014,
                        'area_unit_price_8' => 1000,
                        'area_unit_price_9' => null,
                        'area_unit_price_10' => null,
                        'change_date' => '2022/09/01',
                        'facility_id' => 4,
                        'first_plan_input' => 0,
                        'id' => 4,
                        'service_end_date' => '9999/12/01',
                        'service_start_date' => '2021/04/01',
                        'service_type_code' => '32',
                        'service_type_code_id' => 1,
                        'service_type_name' => '認知症対応型共同生活介護'
                    ]
                ]
            ],
            [
                'abbreviation' => null,
                'allow_transmission' => 1,
                'area' => 1,
                'facility_id' => 5,
                'facility_manager' => null,
                'facility_name_kana' => 'ジギョウショ',
                'facility_name_kanji' => '事業所',
                'facility_number' => '0000000001',
                'fax_number' => null,
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'institution_id' => 5,
                'location' => '住所',
                'phone_number' => '0000000000',
                'postal_code' => '0000000',
                'remarks' => null,
                'services' => [
                    [
                        'area' => 7,
                        'area_unit_price_1' => 1090,
                        'area_unit_price_2' => 1072,
                        'area_unit_price_3' => 1068,
                        'area_unit_price_4' => 1054,
                        'area_unit_price_5' => 1045,
                        'area_unit_price_6' => 1027,
                        'area_unit_price_7' => 1014,
                        'area_unit_price_8' => 1000,
                        'area_unit_price_9' => null,
                        'area_unit_price_10' => null,
                        'change_date' => '2022/08/01',
                        'facility_id' => 5,
                        'first_plan_input' => 0,
                        'id' => 5,
                        'service_end_date' => '9999/12/01',
                        'service_start_date' => '2021/04/01',
                        'service_type_code' => '55',
                        'service_type_code_id' => 6,
                        'service_type_name' => '介護医療院'
                    ]
                ]
            ],
            [
                'abbreviation' => null,
                'allow_transmission' => 1,
                'area' => 1,
                'facility_id' => 6,
                'facility_manager' => null,
                'facility_name_kana' => 'ジギョウショ',
                'facility_name_kanji' => '事業所',
                'facility_number' => '0000000001',
                'fax_number' => null,
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'institution_id' => 6,
                'location' => '住所',
                'phone_number' => '0000000000',
                'postal_code' => '0000000',
                'remarks' => null,
                'services' => [
                    [
                        'area' => 7,
                        'area_unit_price_1' => 1090,
                        'area_unit_price_2' => 1072,
                        'area_unit_price_3' => 1068,
                        'area_unit_price_4' => 1054,
                        'area_unit_price_5' => 1045,
                        'area_unit_price_6' => 1027,
                        'area_unit_price_7' => 1014,
                        'area_unit_price_8' => 1000,
                        'area_unit_price_9' => null,
                        'area_unit_price_10' => null,
                        'change_date' => '2022/08/01',
                        'facility_id' => 6,
                        'first_plan_input' => 0,
                        'id' => 6,
                        'service_end_date' => '9999/12/01',
                        'service_start_date' => '2021/04/01',
                        'service_type_code' => '55',
                        'service_type_code_id' => 6,
                        'service_type_name' => '介護医療院'
                    ]
                ]
            ],
            [
                'abbreviation' => null,
                'allow_transmission' => 1,
                'area' => 1,
                'facility_id' => 7,
                'facility_manager' => null,
                'facility_name_kana' => 'ジギョウショ',
                'facility_name_kanji' => '事業所',
                'facility_number' => '0000000001',
                'fax_number' => null,
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'institution_id' => 7,
                'location' => '住所',
                'phone_number' => '0000000000',
                'postal_code' => '0000000',
                'remarks' => null,
                'services' => [
                    [
                        'area' => 1,
                        'area_unit_price_1' => 1090,
                        'area_unit_price_2' => 1072,
                        'area_unit_price_3' => 1068,
                        'area_unit_price_4' => 1054,
                        'area_unit_price_5' => 1045,
                        'area_unit_price_6' => 1027,
                        'area_unit_price_7' => 1014,
                        'area_unit_price_8' => 1000,
                        'area_unit_price_9' => null,
                        'area_unit_price_10' => null,
                        'change_date' => '2022/11/01',
                        'facility_id' => 7,
                        'first_plan_input' => 0,
                        'id' => 7,
                        'service_end_date' => '9999/12/01',
                        'service_start_date' => '2021/04/01',
                        'service_type_code' => '33',
                        'service_type_code_id' => 3,
                        'service_type_name' => '特定施設入居者生活介護'
                    ]
                ]
            ],
            [
                'abbreviation' => null,
                'allow_transmission' => 1,
                'area' => 5,
                'facility_id' => 8,
                'facility_manager' => null,
                'facility_name_kana' => 'ジギョウショ',
                'facility_name_kanji' => '事業所',
                'facility_number' => '0000000001',
                'fax_number' => null,
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'institution_id' => 8,
                'location' => '住所',
                'phone_number' => '0000000000',
                'postal_code' => '0000000',
                'remarks' => null,
                'services' => [
                    [
                        'area' => 5,
                        'area_unit_price_1' => 1090,
                        'area_unit_price_2' => 1072,
                        'area_unit_price_3' => 1068,
                        'area_unit_price_4' => 1054,
                        'area_unit_price_5' => 1045,
                        'area_unit_price_6' => 1027,
                        'area_unit_price_7' => 1014,
                        'area_unit_price_8' => 1000,
                        'area_unit_price_9' => null,
                        'area_unit_price_10' => null,
                        'change_date' => '2022/09/01',
                        'facility_id' => 8,
                        'first_plan_input' => 0,
                        'id' => 8,
                        'service_end_date' => '9999/12/01',
                        'service_start_date' => '2021/04/01',
                        'service_type_code' => '32',
                        'service_type_code_id' => 1,
                        'service_type_name' => '認知症対応型共同生活介護'
                    ]
                ]
            ]
        ];
    }
}
