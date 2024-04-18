<?php

namespace Tests\Unit\DomainServices;

use App\Lib\DomainService\CareAdditionSpecification;
use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUserIndependence;
use App\Lib\Factory\FacilityUserIndependenceFactory;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Tests\Factory\TestCareRewardHistoryFactory;

/**
 * ケア加算の仕様のテスト。
 */
class CareAdditionSpecificationTest extends TestCase
{
    /**
     * 仕様のテスト。
     * @dataProvider dataProvider
     * @param CareRewardHistory $careRewardHistory
     * @param FacilityUserIndependence $facilityUserIndependence
     * @param bool $isAvailableCorrect
     */
    public function testSpecification(
        CareRewardHistory $careRewardHistory,
        FacilityUserIndependence $facilityUserIndependence,
        bool $isAvailableCorrect
    ): void {
        // ケア加算の仕様を取得する。
        $careAdditionSpecification = new CareAdditionSpecification();

        // ケア加算が利用可能か判定する。
        $isAvailable = $careAdditionSpecification->isAvailable($careRewardHistory, $facilityUserIndependence);

        $this->assertEquals($isAvailableCorrect, $isAvailable);
    }

    /**
     * データプロバイダ。
     */
    public function dataProvider(): array
    {
        $careRewardHistoryFactory = new TestCareRewardHistoryFactory();
        $facilityUserIndependenceFactory = new FacilityUserIndependenceFactory();

        return [
            // テストの目的: ケア加算がない場合の動きを確認する。
            'case_1' => [
                // ケア加算なし。
                $careRewardHistoryFactory->generateInitial('2022-12-01', '2022-12-31'),
                // 施設利用者はケア加算の対象者
                $facilityUserIndependenceFactory->generateCareAddition(),
                // ケア加算を利用できない。
                false
            ],

            // テストの目的: ケア加算がある場合の動きを確認する。
            // 加えて、施設利用者がケア加算の対象でない場合の動きを確認する。
            'case_2' => [
                $careRewardHistoryFactory->generateCareAddition('2022-12-01', '2022-12-31'),
                $facilityUserIndependenceFactory->generateCareAdditionDenial(),
                // ケア加算を利用できない。
                false
            ],

            // テストの目的: 施設利用者がケア加算の対象の場合の動きを確認する。
            'case_3' => [
                $careRewardHistoryFactory->generateCareAddition('2022-12-01', '2022-12-31'),
                $facilityUserIndependenceFactory->generateCareAddition(),
                // ケア加算を利用できる。
                true
            ]
        ];
    }
}
