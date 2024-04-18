<?php

namespace App\Lib\Factory;

use App\Lib\Entity\FacilityUserService;

/**
 * 施設利用者のサービスのファクトリ。
 */
class FacilityUserServiceFactory
{
    /**
     * 種類32のテストデータを生成する。
     * TODO: 現状テストコードからのみ参照しているが将来的にはアプリ側から利用できるように換装する。
     * @param string $endDate 終了日
     * @param string $startDate 開始日
     */
    public function generate32Test(string $endDate, string $startDate): FacilityUserService
    {
        return new FacilityUserService(
            1090,
            1072,
            1068,
            1054,
            1045,
            1027,
            1014,
            1000,
            null,
            null,
            // facility_id
            1,
            // facility_user_id
            1,
            '9999/12/01',
            // service_id
            1,
            '2021/04/01',
            '32',
            // service_type_code_id
            1,
            '認知症対応型共同生活介護',
            // usage_situation
            1,
            $endDate,
            // user_facility_service_information_id
            1,
            $startDate
        );
    }

    /**
     * 種類33のテストデータを生成する。
     * TODO: 現状テスト側からの利用だが、追ってビジネスロジックから利用されるのでここに配置としている。
     * @param string $endDate 終了日
     * @param string $startDate 開始日
     */
    public function generate33Test(string $endDate, string $startDate): FacilityUserService
    {
        return new FacilityUserService(
            1090,
            1072,
            1068,
            1054,
            1045,
            1027,
            1014,
            1000,
            null,
            null,
            // facility_id
            1,
            // facility_user_id
            1,
            '9999/12/01',
            // service_id
            1,
            '2021/04/01',
            '33',
            // service_type_code_id
            3,
            '特定施設入居者生活介護',
            // usage_situation
            1,
            $endDate,
            // user_facility_service_information_id
            1,
            $startDate
        );
    }

    /**
     * 種類36のテストデータを生成する。
     * TODO: 現状テスト側からの利用だが、追ってビジネスロジックから利用されるのでここに配置としている。
     * @param string $endDate 終了日
     * @param string $startDate 開始日
     */
    public function generate36Test(string $endDate, string $startDate): FacilityUserService
    {
        return new FacilityUserService(
            1090,
            1072,
            1068,
            1054,
            1045,
            1027,
            1014,
            1000,
            null,
            null,
            // facility_id
            1,
            // facility_user_id
            1,
            '9999/12/01',
            // service_id
            1,
            '2021/04/01',
            '36',
            // service_type_code_id
            3,
            '地域密着型特定施設入居者生活介護',
            // usage_situation
            1,
            $endDate,
            // user_facility_service_information_id
            1,
            $startDate
        );
    }
}
