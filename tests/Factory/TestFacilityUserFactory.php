<?php

namespace Tests\Factory;

use App\Lib\Entity\AfterOutStatus;
use App\Lib\Entity\BeforeInStatus;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\InsuredNo;

/**
 * テスト用の施設利用者のファクトリ。
 */
class TestFacilityUserFactory
{
    /**
     * 入居前状況が介護老人保健施設で生成する。
     * @param string $startDate 入居日
     */
    public function generateBeforeInStatusElderlyCare(string $startDate): FacilityUser
    {
        return new FacilityUser(
            // after_out_status
            null,
            new BeforeInStatus(
                // before_in_status
                4,
                // before_in_status_id
                4,
                // before_in_status_name
                '介護老人保健施設',
                // before_in_statuses_end_date
                '9999/12/31',
                // before_in_statuses_start_date
                '2022/09/01'
            ),
            // birthday
            '1950-01-01',
            // blood_type
            null,
            // cell_phone_number
            null,
            // consent_date
            null,
            // consenter
            null,
            // consenter_phone_number
            null,
            // death_date
            null,
            // death_reason
            null,
            // diagnosis_date
            null,
            // diagnostician
            null,
            // end_date
            null,
            // facility_user_id
            1,
            // first_name
            '名前',
            // first_name_kana
            'ナマエ',
            // gender
            1,
            new InsuredNo('123456'),
            // insurer_no
            '123456',
            // invalid_flag
            0,
            // last_name
            '名字',
            // last_name_kana
            'ミョウジ',
            // location1
            null,
            // location2
            null,
            // phone_number
            null,
            // postal_code
            null,
            // remarks
            null,
            // rh_type
            null,
            // spacial_address_flag
            0,
            // start_date
            $startDate
        );
    }

    /**
     * 入居前状況が医療機関で生成する。
     * @param string $startDate 入居日
     */
    public function generateBeforeInStatusMedical(string $startDate): FacilityUser
    {
        return new FacilityUser(
            // after_out_status
            null,
            new BeforeInStatus(
                // before_in_status
                2,
                // before_in_status_id
                2,
                // before_in_status_name
                '医療機関',
                // before_in_statuses_end_date
                '9999/12/31',
                // before_in_statuses_start_date
                '2022/09/01'
            ),
            // birthday
            '1950-01-01',
            // blood_type
            null,
            // cell_phone_number
            null,
            // consent_date
            null,
            // consenter
            null,
            // consenter_phone_number
            null,
            // death_date
            null,
            // death_reason
            null,
            // diagnosis_date
            null,
            // diagnostician
            null,
            // end_date
            null,
            // facility_user_id
            1,
            // first_name
            '名前',
            // first_name_kana
            'ナマエ',
            // gender
            1,
            new InsuredNo('123456'),
            // insurer_no
            '123456',
            // invalid_flag
            0,
            // last_name
            '名字',
            // last_name_kana
            'ミョウジ',
            // location1
            null,
            // location2
            null,
            // phone_number
            null,
            // postal_code
            null,
            // remarks
            null,
            // rh_type
            null,
            // spacial_address_flag
            0,
            // start_date
            $startDate
        );
    }

    /**
     * 生年月日指定で初期生成する。
     * @param string $birthday 生年月日 yyyy-mm-dd
     * @param string $startDate 入居日 yyyy-mm-dd
     */
    public function generateByBirthday(string $birthday, string $startDate): FacilityUser
    {
        return new FacilityUser(
            // after_out_status
            null,
            new BeforeInStatus(
                // before_in_status
                1,
                // before_in_status_id
                1,
                // before_in_status_name
                '居宅',
                // before_in_statuses_end_date
                '9999/12/31',
                // before_in_statuses_start_date
                '2022/09/01'
            ),
            $birthday,
            // blood_type
            null,
            // cell_phone_number
            null,
            // consent_date
            null,
            // consenter
            null,
            // consenter_phone_number
            null,
            // death_date
            null,
            // death_reason
            null,
            // diagnosis_date
            null,
            // diagnostician
            null,
            // end_date
            null,
            // facility_user_id
            1,
            // first_name
            '名前',
            // first_name_kana
            'ナマエ',
            // gender
            1,
            new InsuredNo('123456'),
            // insurer_no
            '123456',
            // invalid_flag
            0,
            // last_name
            '名字',
            // last_name_kana
            'ミョウジ',
            // location1
            null,
            // location2
            null,
            // phone_number
            null,
            // postal_code
            null,
            // remarks
            null,
            // rh_type
            null,
            // spacial_address_flag
            0,
            // start_date
            $startDate
        );
    }

    /**
     * 看取りで生成する。
     * @param string $consentDate 同意日
     * @param string $deathDate 看取り日
     * @param string $endDate 退去日
     * @param string $startDate 入居日
     */
    public function generateEndOfLife(
        string $consentDate,
        string $deathDate,
        string $endDate,
        string $startDate
    ): FacilityUser {
        return new FacilityUser(
            // after_out_status
            null,
            new BeforeInStatus(
                // before_in_status
                1,
                // before_in_status_id
                1,
                // before_in_status_name
                '居宅',
                // before_in_statuses_end_date
                '9999/12/31',
                // before_in_statuses_start_date
                '2022/09/01'
            ),
            // birthday
            '1950/01/01',
            // blood_type
            null,
            // cell_phone_number
            null,
            $consentDate,
            // consenter
            null,
            // consenter_phone_number
            null,
            $deathDate,
            // death_reason
            null,
            // diagnosis_date
            null,
            // diagnostician
            null,
            $endDate,
            // facility_user_id
            1,
            // first_name
            '名前',
            // first_name_kana
            'ナマエ',
            // gender
            1,
            new InsuredNo('123456'),
            // insurer_no
            '123456',
            // invalid_flag
            0,
            // last_name
            '名字',
            // last_name_kana
            'ミョウジ',
            // location1
            null,
            // location2
            null,
            // phone_number
            null,
            // postal_code
            null,
            // remarks
            null,
            // rh_type
            null,
            // spacial_address_flag
            0,
            // start_date
            $startDate
        );
    }

    /**
     * 退去で生成する。
     * @param AfterOutStatus $afterOutStatus 退去後の状況
     * @param string $startDate 入居日
     * @param string $endDate 退去日
     */
    public function generateEnd(
        AfterOutStatus $afterOutStatus,
        string $startDate,
        string $endDate
    ): FacilityUser {
        return new FacilityUser(
            $afterOutStatus,
            new BeforeInStatus(
                // before_in_status
                1,
                // before_in_status_id
                1,
                // before_in_status_name
                '居宅',
                // before_in_statuses_end_date
                '9999/12/31',
                // before_in_statuses_start_date
                '2022/09/01'
            ),
            // birthday
            '1950/01/01',
            // blood_type
            null,
            // cell_phone_number
            null,
            // consent_date
            null,
            // consenter
            null,
            // consenter_phone_number
            null,
            // death_date
            null,
            // death_reason
            null,
            // diagnosis_date
            null,
            // diagnostician
            null,
            $endDate,
            // facility_user_id
            1,
            // first_name
            '名前',
            // first_name_kana
            'ナマエ',
            // gender
            1,
            new InsuredNo('123456'),
            // insurer_no
            '123456',
            // invalid_flag
            0,
            // last_name
            '名字',
            // last_name_kana
            'ミョウジ',
            // location1
            null,
            // location2
            null,
            // phone_number
            null,
            // postal_code
            null,
            // remarks
            null,
            // rh_type
            null,
            // spacial_address_flag
            0,
            // start_date
            $startDate
        );
    }

    /**
     * 初期生成する。
     * @param string $startDate 入居日 yyyy-mm-dd
     */
    public function generateInitial(string $startDate): FacilityUser
    {
        return new FacilityUser(
            // after_out_status
            null,
            new BeforeInStatus(
                // before_in_status
                1,
                // before_in_status_id
                1,
                // before_in_status_name
                '居宅',
                // before_in_statuses_end_date
                '9999/12/31',
                // before_in_statuses_start_date
                '2022/09/01'
            ),
            // birthday
            '1950-01-01',
            // blood_type
            null,
            // cell_phone_number
            null,
            // consent_date
            null,
            // consenter
            null,
            // consenter_phone_number
            null,
            // death_date
            null,
            // death_reason
            null,
            // diagnosis_date
            null,
            // diagnostician
            null,
            // end_date
            null,
            // facility_user_id
            1,
            // first_name
            '名前',
            // first_name_kana
            'ナマエ',
            // gender
            1,
            new InsuredNo('123456'),
            // insurer_no
            '123456',
            // invalid_flag
            0,
            // last_name
            '名字',
            // last_name_kana
            'ミョウジ',
            // location1
            null,
            // location2
            null,
            // phone_number
            null,
            // postal_code
            null,
            // remarks
            null,
            // rh_type
            null,
            // spacial_address_flag
            0,
            // start_date
            $startDate
        );
    }
}
