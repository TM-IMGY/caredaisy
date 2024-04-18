<?php

namespace Tests\Unit\DomainServices;

use App\Lib\DomainService\StayOutSpecification;
use App\Lib\Entity\FacilityUser\StayOut;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\Factory\ResultFlagFactory;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use PHPUnit\Framework\TestCase;

/**
 * 算定の外泊の仕様のテスト。
 */
class StayOutSpecificationTest extends TestCase
{
    /**
     * データプロバイダ。
     */
    public function dataProvider(): array
    {
        // 実績フラグのファクトリを作成する。
        $resultFlagFactory = new ResultFlagFactory();

        return [
            // テストの目的: 外泊を登録しない場合の動きを確認する。
            'case_1' => [
                // 退去日
                null,
                // 実績フラグ正解
                [
                    // 外泊の考慮前と変わらない
                    $resultFlagFactory->generateTargetYm('0111111111111111111111111111100', 28)
                ],
                // 実績フラグ外泊考慮前
                [
                    $resultFlagFactory->generateTargetYm('0111111111111111111111111111100', 28),
                ],
                // 外泊
                [],
                2022,
                9
            ],
            // テストの目的: 外泊を複数登録する場合の動きを確認する。
            // 加えて、分まで登録する場合の動きを確認する。
            // 加えて、状態別の動きを確認する。
            // 加えて、月を跨ぐ場合の動きを確認する。
            // 加えて、終了日のみ登録する場合の動きを確認する。
            'case_2' => [
                // 退去日
                null,
                // 実績フラグ正解
                [
                    $resultFlagFactory->generateTargetYm('1000000001000000000000000000000', 2),
                    $resultFlagFactory->generateTargetYm('0111111110000000000000000000000', 8)
                ],
                // 実績フラグ外泊考慮前
                [
                    $resultFlagFactory->generateTargetYm('1111111111111111111111111111110', 30),
                    $resultFlagFactory->generateTargetYm('1111111111111111111111111111111', 31)
                ],
                // 外泊
                [
                    new StayOut('2022-09-10 23:58:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022-09-01 00:01:00', 1, '', ''),
                    new StayOut('2022-09-11 23:59:00', 1, 2, 0, 0, 0, 0, 0, 0, 0, 0, '2022-09-11 00:00:00', 2, '', ''),
                    new StayOut('2022-09-12 23:59:00', 1, 3, 0, 0, 0, 0, 0, 0, 0, 0, '2022-09-12 00:00:00', 3, '', ''),
                    new StayOut('2022-09-13 23:59:00', 1, 4, 0, 0, 0, 0, 0, 0, 0, 0, '2022-09-13 00:00:00', 4, '', ''),
                    new StayOut('2022-09-14 23:59:00', 1, 5, 0, 0, 0, 0, 0, 0, 0, 0, '2022-09-14 00:00:00', 5, '', ''),
                    new StayOut('2022-10-01 23:59:00', 1, 6, 0, 0, 0, 0, 0, 0, 0, 0, '2022-09-15 00:00:00', 1, '', ''),
                    new StayOut(null, 1, 7, 0, 0, 0, 0, 0, 0, 0, 0, '2022-10-10 00:00:00', 1, '', ''),
                ],
                2022,
                9
            ],
            // テストの目的: 退去日がある場合の動きを確認する。
            // 加えて、退去日より外泊日が前の場合の動きを確認する。
            // 加えて、退去日と外泊日が同じ場合の動きを確認する。
            // 加えて、退去日より外泊日が後の場合の動きを確認する。
            'case_3' => [
                // 退去日
                '2022-09-10',
                // 実績フラグ正解
                [
                    // 退去日の削除については外泊の仕様に含まれないので注意する。
                    $resultFlagFactory->generateTargetYm('0000000000111111111111111111110', 20)
                ],
                // 実績フラグ外泊考慮前
                [
                    $resultFlagFactory->generateTargetYm('1111111111111111111111111111110', 30)
                ],
                // 外泊
                [
                    new StayOut('2022-09-09 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022-09-01 00:00:00', 1, '', ''),
                    new StayOut('2022-09-10 23:59:00', 1, 2, 0, 0, 0, 0, 0, 0, 0, 0, '2022-09-10 00:00:00', 1, '', ''),
                    new StayOut('2022-09-30 23:59:00', 1, 3, 0, 0, 0, 0, 0, 0, 0, 0, '2022-09-11 00:00:00', 1, '', '')
                ],
                2022,
                9
            ]
        ];
    }

    /**
     * 仕様のテスト。
     * @dataProvider dataProvider
     * @param ?string $endDate 施設利用者の退去日
     * @param ResultFlag[] $resultFlagCorrects 実績フラグ正解全て
     * @param ResultFlag[] $resultFlagObjects 実績フラグ外泊考慮前全て
     * @param StayOut[] $stayOuts 外泊全て
     * @param int $year 対象年
     * @param int $month 対象月
     */
    public function testSpecification(
        ?string $endDate,
        array $resultFlagCorrects,
        array $resultFlagObjects,
        array $stayOuts,
        int $year,
        int $month
    ): void {
        // 外泊の仕様を取得する。
        $stayOutSpecification = new StayOutSpecification();

        // 実績フラグ正解を全て参照する。
        foreach ($resultFlagCorrects as $index => $correct) {
            // 外泊の仕様から実績フラグを取得する。
            $testTarget = $stayOutSpecification->deleteByStayOut(
                $endDate,
                $resultFlagObjects[$index],
                new StayOutRecord($stayOuts),
                $year,
                $month + $index
            );

            // 取得した実績フラグの日割対象日が正しいかテストする。
            $this->assertEquals($correct->getDateDailyRate(), $testTarget->getDateDailyRate());

            // 取得した実績フラグの日割対象日(1月前)が正しいかテストする。
            $this->assertEquals($correct->getDateDailyRateOneMonthAgo(), $testTarget->getDateDailyRateOneMonthAgo());

            // 取得した実績フラグの日割対象日(2月前)が正しいかテストする。
            $this->assertEquals($correct->getDateDailyRateTwoMonthAgo(), $testTarget->getDateDailyRateTwoMonthAgo());

            // 取得した実績フラグの回数／日数が正しいかテストする。
            $this->assertEquals($correct->getServiceCountDate(), $testTarget->getServiceCountDate());
        }
    }
}
