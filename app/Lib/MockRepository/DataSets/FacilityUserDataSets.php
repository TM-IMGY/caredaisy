<?php

namespace App\Lib\MockRepository\DataSets;

/**
 * 施設利用者のデータセット。
 * 初期はcsvで管理していたが可読性が低すぎたためクラスとして換装した。
 */
class FacilityUserDataSets
{
    // 事例 公費あり(月途中)
    public const CASE_32_PATTERN_1_ID = 1;

    // 種類32で特殊以外の可能な限りのサービスコードを持つ。
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
    // 退院退所時連携加算 対象年月に入居、退去、外泊がある
    public const USE_CASE_AUTO_SERVICE_CODE_TEST_1 = 7;
    // 退院退所時連携加算 対象年月に看取りがある
    public const USE_CASE_AUTO_SERVICE_CODE_TEST_2 = 8;
    // 退院退所時連携加算 対象年月に認定情報が変わる。
    public const USE_CASE_AUTO_SERVICE_CODE_TEST_3 = 9;

    // 事例 種類32 看取りあり
    public const CASE_32_PATTERN_4_ID = 10;

    public static function get()
    {
        return [
            [
                'after_out_status' => null,
                'before_in_status' => [
                    'before_in_status' => 1,
                    'before_in_status_id' => 1,
                    'before_in_status_name' => '居宅',
                    'before_in_statuses_end_date' => '9999/12/31',
                    'before_in_statuses_start_date' => '2021/08/01'
                ],
                'birth_day' => '1970/01/01',
                'blood_type' => null,
                'cell_phone_number' => null,
                'consent_date' => null,
                'consenter' => null,
                'consenter_phone_number' => null,
                'death_date' => null,
                'death_reason' => null,
                'diagnosis_date' => null,
                'diagnostician' => null,
                'end_date' => null,
                'facility_user_id' => 1,
                'first_name' => '名前',
                'first_name_kana' => 'ナマエ',
                'gender' => 1,
                'insured_no' => '123456',
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'last_name' => '名字',
                'last_name_kana' => 'ミョウジ',
                'location1' => null,
                'location2' => null,
                'phone_number' => null,
                'postal_code' => null,
                'remarks' => null,
                'rh_type' => null,
                'spacial_address_flag' => 0,
                'start_date' => '2022/07/01'
            ],
            [
                'after_out_status' => null,
                'before_in_status' => [
                    'before_in_status' => 1,
                    'before_in_status_id' => 1,
                    'before_in_status_name' => '居宅',
                    'before_in_statuses_end_date' => '9999/12/31',
                    'before_in_statuses_start_date' => '2021/08/01'
                ],
                'birth_day' => '1970/01/01',
                'blood_type' => null,
                'cell_phone_number' => null,
                'consent_date' => null,
                'consenter' => null,
                'consenter_phone_number' => null,
                'death_date' => null,
                'death_reason' => null,
                'diagnosis_date' => null,
                'diagnostician' => null,
                'end_date' => null,
                'facility_user_id' => 2,
                'first_name' => '名前',
                'first_name_kana' => 'ナマエ',
                'gender' => 1,
                'insured_no' => '123456',
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'last_name' => '名字',
                'last_name_kana' => 'ミョウジ',
                'location1' => null,
                'location2' => null,
                'phone_number' => null,
                'postal_code' => null,
                'remarks' => null,
                'rh_type' => null,
                'spacial_address_flag' => 0,
                'start_date' => '2022/09/01'
            ],
            [
                'after_out_status' => null,
                'before_in_status' => [
                    'before_in_status' => 2,
                    'before_in_status_id' => 1,
                    'before_in_status_name' => '医療機関',
                    'before_in_statuses_end_date' => '9999/12/31',
                    'before_in_statuses_start_date' => '2021/08/01'
                ],
                'birth_day' => '1970/01/01',
                'blood_type' => null,
                'cell_phone_number' => null,
                'consent_date' => null,
                'consenter' => null,
                'consenter_phone_number' => null,
                'death_date' => null,
                'death_reason' => null,
                'diagnosis_date' => null,
                'diagnostician' => null,
                'end_date' => null,
                'facility_user_id' => 3,
                'first_name' => '名前',
                'first_name_kana' => 'ナマエ',
                'gender' => 1,
                'insured_no' => '123456',
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'last_name' => '名字',
                'last_name_kana' => 'ミョウジ',
                'location1' => null,
                'location2' => null,
                'phone_number' => null,
                'postal_code' => null,
                'remarks' => null,
                'rh_type' => null,
                'spacial_address_flag' => 0,
                'start_date' => '2022/08/01'
            ],
            [
                'after_out_status' => null,
                'before_in_status' => [
                    'before_in_status' => 1,
                    'before_in_status_id' => 1,
                    'before_in_status_name' => '居宅',
                    'before_in_statuses_end_date' => '9999/12/31',
                    'before_in_statuses_start_date' => '2021/08/01'
                ],
                'birth_day' => '1970/01/01',
                'blood_type' => null,
                'cell_phone_number' => null,
                'consent_date' => null,
                'consenter' => null,
                'consenter_phone_number' => null,
                'death_date' => null,
                'death_reason' => null,
                'diagnosis_date' => null,
                'diagnostician' => null,
                'end_date' => null,
                'facility_user_id' => 4,
                'first_name' => '名前',
                'first_name_kana' => 'ナマエ',
                'gender' => 1,
                'insured_no' => '123456',
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'last_name' => '名字',
                'last_name_kana' => 'ミョウジ',
                'location1' => null,
                'location2' => null,
                'phone_number' => null,
                'postal_code' => null,
                'remarks' => null,
                'rh_type' => null,
                'spacial_address_flag' => 0,
                'start_date' => '2022/10/01'
            ],
            [
                'after_out_status' => null,
                'before_in_status' => [
                    'before_in_status' => 2,
                    'before_in_status_id' => 1,
                    'before_in_status_name' => '医療機関',
                    'before_in_statuses_end_date' => '9999/12/31',
                    'before_in_statuses_start_date' => '2021/08/01'
                ],
                'birth_day' => '1970/01/01',
                'blood_type' => null,
                'cell_phone_number' => null,
                'consent_date' => null,
                'consenter' => null,
                'consenter_phone_number' => null,
                'death_date' => null,
                'death_reason' => null,
                'diagnosis_date' => null,
                'diagnostician' => null,
                'end_date' => null,
                'facility_user_id' => 5,
                'first_name' => '名前',
                'first_name_kana' => 'ナマエ',
                'gender' => 2,
                'insured_no' => '123456',
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'last_name' => '名字',
                'last_name_kana' => 'ミョウジ',
                'location1' => null,
                'location2' => null,
                'phone_number' => null,
                'postal_code' => null,
                'remarks' => null,
                'rh_type' => null,
                'spacial_address_flag' => 0,
                'start_date' => '2022/08/01'
            ],
            [
                'after_out_status' => null,
                'before_in_status' => [
                    'before_in_status' => 2,
                    'before_in_status_id' => 1,
                    'before_in_status_name' => '医療機関',
                    'before_in_statuses_end_date' => '9999/12/31',
                    'before_in_statuses_start_date' => '2021/08/01'
                ],
                'birth_day' => '1970/01/01',
                'blood_type' => null,
                'cell_phone_number' => null,
                'consent_date' => null,
                'consenter' => null,
                'consenter_phone_number' => null,
                'death_date' => null,
                'death_reason' => null,
                'diagnosis_date' => null,
                'diagnostician' => null,
                'end_date' => null,
                'facility_user_id' => 6,
                'first_name' => '名前',
                'first_name_kana' => 'ナマエ',
                'gender' => 2,
                'insured_no' => 'H12345',
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'last_name' => '名字',
                'last_name_kana' => 'ミョウジ',
                'location1' => null,
                'location2' => null,
                'phone_number' => null,
                'postal_code' => null,
                'remarks' => null,
                'rh_type' => null,
                'spacial_address_flag' => 0,
                'start_date' => '2022/08/01'
            ],
            [
                'after_out_status' => [
                    'after_out_status' => 1,
                    'after_out_status_id' => 1,
                    'after_out_status_end_date' => '9999/12/31',
                    'after_out_status_name' => '居宅',
                    'after_out_status_start_date' => '2021/8/1'
                ],
                'before_in_status' => [
                    'before_in_status' => 2,
                    'before_in_status_id' => 1,
                    'before_in_status_name' => '医療機関',
                    'before_in_statuses_end_date' => '9999/12/31',
                    'before_in_statuses_start_date' => '2021/08/01'
                ],
                'birth_day' => '1970/01/01',
                'blood_type' => null,
                'cell_phone_number' => null,
                'consent_date' => null,
                'consenter' => null,
                'consenter_phone_number' => null,
                'death_date' => null,
                'death_reason' => null,
                'diagnosis_date' => null,
                'diagnostician' => null,
                'end_date' => '2022/11/29',
                'facility_user_id' => 7,
                'first_name' => '名前',
                'first_name_kana' => 'ナマエ',
                'gender' => 1,
                'insured_no' => '123456',
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'last_name' => '名字',
                'last_name_kana' => 'ミョウジ',
                'location1' => null,
                'location2' => null,
                'phone_number' => null,
                'postal_code' => null,
                'remarks' => null,
                'rh_type' => null,
                'spacial_address_flag' => 0,
                'start_date' => '2022/11/02'
            ],
            [
                'after_out_status' => null,
                'before_in_status' => [
                    'before_in_status' => 2,
                    'before_in_status_id' => 1,
                    'before_in_status_name' => '医療機関',
                    'before_in_statuses_end_date' => '9999/12/31',
                    'before_in_statuses_start_date' => '2021/08/01'
                ],
                'birth_day' => '1970/01/01',
                'blood_type' => null,
                'cell_phone_number' => null,
                'consent_date' => '2022/11/29',
                'consenter' => '同意者',
                'consenter_phone_number' => '01234567899',
                'death_date' => '2022/11/29',
                'death_reason' => null,
                'diagnosis_date' => '2022/11/29',
                'diagnostician' => '診断者',
                'end_date' => null,
                'facility_user_id' => 8,
                'first_name' => '名前',
                'first_name_kana' => 'ナマエ',
                'gender' => 1,
                'insured_no' => '123456',
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'last_name' => '名字',
                'last_name_kana' => 'ミョウジ',
                'location1' => null,
                'location2' => null,
                'phone_number' => null,
                'postal_code' => null,
                'remarks' => null,
                'rh_type' => null,
                'spacial_address_flag' => 0,
                'start_date' => '2022/11/01'
            ],
            [
                'after_out_status' => null,
                'before_in_status' => [
                    'before_in_status' => 2,
                    'before_in_status_id' => 1,
                    'before_in_status_name' => '医療機関',
                    'before_in_statuses_end_date' => '9999/12/31',
                    'before_in_statuses_start_date' => '2021/08/01'
                ],
                'birth_day' => '1970/01/01',
                'blood_type' => null,
                'cell_phone_number' => null,
                'consent_date' => null,
                'consenter' => null,
                'consenter_phone_number' => null,
                'death_date' => null,
                'death_reason' => null,
                'diagnosis_date' => null,
                'diagnostician' => null,
                'end_date' => null,
                'facility_user_id' => 9,
                'first_name' => '名前',
                'first_name_kana' => 'ナマエ',
                'gender' => 1,
                'insured_no' => '123456',
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'last_name' => '名字',
                'last_name_kana' => 'ミョウジ',
                'location1' => null,
                'location2' => null,
                'phone_number' => null,
                'postal_code' => null,
                'remarks' => null,
                'rh_type' => null,
                'spacial_address_flag' => 0,
                'start_date' => '2022/11/01'
            ],
            [
                'after_out_status' => null,
                'before_in_status' => [
                    'before_in_status' => 1,
                    'before_in_status_id' => 1,
                    'before_in_status_name' => '居宅',
                    'before_in_statuses_end_date' => '9999/12/31',
                    'before_in_statuses_start_date' => '2021/08/01'
                ],
                'birth_day' => '1970/01/01',
                'blood_type' => null,
                'cell_phone_number' => null,
                'consent_date' => null,
                'consenter' => null,
                'consenter_phone_number' => null,
                'death_date' => null,
                'death_reason' => null,
                'diagnosis_date' => null,
                'diagnostician' => null,
                'end_date' => null,
                'facility_user_id' => 10,
                'first_name' => '名前',
                'first_name_kana' => 'ナマエ',
                'gender' => 1,
                'insured_no' => '123456',
                'insurer_no' => '123456',
                'invalid_flag' => 0,
                'last_name' => '名字',
                'last_name_kana' => 'ミョウジ',
                'location1' => null,
                'location2' => null,
                'phone_number' => null,
                'postal_code' => null,
                'remarks' => null,
                'rh_type' => null,
                'spacial_address_flag' => 0,
                'start_date' => '2022/10/01'
            ]
        ];
    }
}
