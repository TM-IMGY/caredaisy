<?php

namespace App\Lib\Factory;

use App\Lib\Entity\CareLevel;
use App\Lib\Entity\FacilityUserCare;

/**
 * 施設利用者の介護情報のファクトリ。
 */
class FacilityUserCareFactory
{
    /**
     * 要介護1のテストデータを生成する。
     * TODO: 現状テスト側からの利用だが、追ってビジネスロジックから利用されるのでここに配置としている。
     * @param string $endDate 終了日
     * @param string $startDate 開始日
     */
    public function generateCareLevel1Test(string $endDate, string $startDate): FacilityUserCare
    {
        return new FacilityUserCare(
            new CareLevel(6, 21, '要介護１', 16140),
            $endDate,
            $startDate,
            2,
            null,
            null,
            1,
            $startDate,
            1
        );
    }

    /**
     * 要介護5のテストデータを生成する。
     * TODO: 現状テスト側からの利用だが、追ってビジネスロジックから利用されるのでここに配置としている。
     * @param string $endDate 終了日
     * @param string $startDate 開始日
     */
    public function generateCareLevel5Test(string $endDate, string $startDate): FacilityUserCare
    {
        return new FacilityUserCare(
            new CareLevel(10, 25, '要介護５', 24210),
            $endDate,
            $startDate,
            2,
            null,
            null,
            1,
            $startDate,
            1
        );
    }

    /**
     * 非該当のテストデータを生成する。
     * TODO: 現状テスト側からの利用だが、追ってビジネスロジックから利用されるのでここに配置としている。
     * @param string $endDate 終了日
     * @param string $startDate 開始日
     */
    public function generateNotApplicableTest(string $endDate, string $startDate): FacilityUserCare
    {
        return new FacilityUserCare(
            new CareLevel(1, 1, '非該当', 0),
            $endDate,
            $startDate,
            2,
            null,
            null,
            1,
            $startDate,
            1
        );
    }
}
