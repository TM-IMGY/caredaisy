<?php

namespace Tests\Unit\DomainServices;

use App\Lib\DomainService\DementiaInitialAdditionSpecification;
use App\Lib\Entity\FacilityUser\StayOut;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\Entity\CareRewardHistory;
use App\Lib\Factory\ResultFlagFactory;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use PHPUnit\Framework\TestCase;
use Tests\Factory\TestFacilityUserFactory;
use Tests\Factory\TestCareRewardHistoryFactory;

/**
 * 認知症対応型初期加算の仕様のテスト。
 */
class DementiaInitialAdditionSpecificationTest extends TestCase
{
    /**
     * 実績フラグの計算のテスト。
     * @dataProvider dataProvider
     * @param string $startDate 入居日
     * @param StayOut[] $stayOuts 外泊全て
     * @param int $year 対象年
     * @param int $month 対象月
     * @param ResultFlag[] $resultFlagCorrects 実績フラグ(正解)
     */
    public function testSpecification(
        string $startDate,
        array $stayOuts,
        int $year,
        int $month,
        array $resultFlagCorrects
    ): void {
        // ケア加算の仕様を取得する。
        $dementiaInitialAdditionSpecification = new DementiaInitialAdditionSpecification();

        // 施設利用者のファクトリを作成する。
        $facilityUserFactory = new TestFacilityUserFactory();

        // 実績フラグ(正解)全てを参照する。
        foreach ($resultFlagCorrects as $index => $correct) {
            // 実績フラグ(テスト対象)を取得する。
            $testTarget = $dementiaInitialAdditionSpecification->calculateResultFlag(
                $facilityUserFactory->generateByBirthday('1950-01-01', $startDate),
                new StayOutRecord($stayOuts),
                $year,
                $month + $index
            );

            // 実績フラグ(テスト対象)の日割対象日が正しいかテストする。
            $this->assertEquals($correct->getDateDailyRate(), $testTarget->getDateDailyRate());

            // 実績フラグ(テスト対象)の日割対象日(1月前)が正しいかテストする。
            $this->assertEquals($correct->getDateDailyRateOneMonthAgo(), $testTarget->getDateDailyRateOneMonthAgo());

            // 実績フラグ(テスト対象)の日割対象日(2月前)が正しいかテストする。
            $this->assertEquals($correct->getDateDailyRateTwoMonthAgo(), $testTarget->getDateDailyRateTwoMonthAgo());
            // 実績フラグ(テスト対象)の回数／日数が正しいかテストする。

            $this->assertEquals($correct->getServiceCountDate(), $testTarget->getServiceCountDate());
        }
    }

    /**
     * データプロバイダ。
     */
    public function dataProvider(): array
    {
        $resultFlagFactory = new ResultFlagFactory();
        return [
            // テストの目的: 入居日以降30日に実績が立つことを確認する。
            'case_1' => [
                // 入居日
                '2022-01-31',
                // 外泊
                [],
                // 対象年
                2022,
                // 対象月
                1,
                // 実績フラグ
                [
                    $resultFlagFactory->generateTargetYm('0000000000000000000000000000001', 1),
                    $resultFlagFactory->generateTargetYm('1111111111111111111111111111000', 28),
                    $resultFlagFactory->generateTargetYm('1000000000000000000000000000000', 1)
                ]
            ],
            // テストの目的: 外泊日数が31日の場合、30日の場合の動きを確認する。
            // 加えて、外泊が複数登録されている場合の動きを確認する。
            // 加えて、外泊が分まで登録されている場合の動きを確認する。
            // 加えて、外泊が入院かそれ以外の場合の動きを確認する。
            // 加えて、外泊の終了日が登録されていない場合の動きを確認する。
            'case_2' => [
                // 入居日
                '2021-01-01',
                // 外泊日
                [
                    new StayOut('2022-01-31 23:58:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022-01-01 00:01:00', 3, '備考', '備考'),
                    new StayOut('2022-03-02 23:59:00', 1, 2, 0, 0, 0, 0, 0, 0, 0, 0, '2022-02-01 00:00:00', 3, '備考', '備考'),
                    new StayOut('2022-05-02 23:59:00', 1, 3, 0, 0, 0, 0, 0, 0, 0, 0, '2022-03-03 00:00:00', 1, '備考', '備考'),
                    new StayOut(null, 1, 4, 0, 0, 0, 0, 0, 0, 0, 0, '2022-05-03 00:00:00', 3, '備考', '備考')
                ],
                // 対象年
                2022,
                // 対象月
                1,
                // 実績フラグ(3/2-4/1、5/1-5/30に実績が立つ)
                [
                    $resultFlagFactory->generateTargetYm('0000000000000000000000000000001', 1),
                    $resultFlagFactory->generateTargetYm('1111111111111111111111111111000', 28),
                    $resultFlagFactory->generateTargetYm('1000000000000000000000000000000', 1),
                    $resultFlagFactory->generateInitial(),
                    $resultFlagFactory->generateInitial()
                ]
            ]
        ];
    }

    /**
     * 加算取得判定のテスト。
     * @dataProvider dataProviderGetJudgment
     * @param CareRewardHistory $careRewardHistory 介護報酬履歴
     * @param bool[] $corrects 加算取得判定の正解全て
     * @param string $startDate 入居日。
     * @param StayOut[] $stayOuts 外泊全て
     * @param int $year 対象年
     * @param int $month 対象月
     */
    public function testGetJudgment(
        CareRewardHistory $careRewardHistory,
        array $corrects,
        string $startDate,
        array $stayOuts,
        int $year,
        int $month
    ): void {
        // 施設利用者のファクトリを作成する。
        $facilityUserFactory = new TestFacilityUserFactory();

        // 施設利用者を生成する。
        $facilityUser = $facilityUserFactory->generateByBirthday('1950-12-01', $startDate);

        // 初期加算の仕様を取得する。
        $dementiaInitialAdditionSpecification = new DementiaInitialAdditionSpecification();

        // 正解全てを参照する。
        foreach ($corrects as $index => $c) {
            // 加算取得判定が正しいかテストする。
            $this->assertEquals(
                $c,
                $dementiaInitialAdditionSpecification->isAvailable(
                    $careRewardHistory,
                    $facilityUser,
                    new StayOutRecord($stayOuts),
                    $year,
                    $month + $index
                )
            );
        }
    }

    /**
     * データプロバイダ
     */
    public function dataProviderGetJudgment(): array
    {
        // 介護報酬履歴のファクトリを作成する。
        $careRewardHistoryFactory = new TestCareRewardHistoryFactory();

        return [
            // テストの目的: 介護報酬履歴に初期加算がない場合の動きを確認する。
            'case_1' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateInitial('2022-01-01', '2022-01-31'),
                // 正解
                [false],
                // 入居日
                '2022-01-01',
                // 外泊
                [],
                // 対象年
                2022,
                // 対象月
                1
            ],
            // テストの目的: 介護報酬履歴に初期加算がある場合の動きを確認する。
            'case_2' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateDementiaInitial('2022-01-01', '2022-01-31'),
                // 正解
                [true],
                // 入居日
                '2022-01-01',
                // 外泊
                [],
                // 対象年
                2022,
                // 対象月
                1
            ],
            // テストの目的: 施設利用者の入居日以降30日が対象年月にない場合の動きをテストする。
            'case_3' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateDementiaInitial('2022-01-01', '2022-04-31'),
                // 正解(1月-3月は取得できるが、4月は取得できない)
                [true, true, true, false],
                // 入居日
                '2022-01-31',
                // 外泊
                [],
                // 対象年
                2022,
                // 対象月
                1
            ],
            // テストの目的: 施設利用者の外泊が31日、30日の動きを確認する。
            // 加えて、外泊が複数登録されている場合の動きを確認する。
            // 加えて、外泊が分まで登録されている場合の動きを確認する。
            // 加えて、外泊の状態が入院かそれ以外の場合の動きを確認する。
            // 加えて、外泊の終了日が登録されていない場合の動きを確認する。
            'case_4' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateDementiaInitial('2022-01-01', '2022-06-30'),
                // 正解(1月-3月のみ取得できる)
                [true, true, true, false, false, false],
                // 入居日
                '2021-01-31',
                // 外泊
                [
                    new StayOut('2022-01-31 23:58:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022-01-01 00:01:00', 3, '備考', '備考'),
                    new StayOut('2022-04-30 23:59:00', 1, 2, 0, 0, 0, 0, 0, 0, 0, 0, '2022-04-01 00:00:00', 3, '備考', '備考'),
                    new StayOut('2022-05-31 23:59:00', 1, 3, 0, 0, 0, 0, 0, 0, 0, 0, '2022-05-01 00:00:00', 1, '備考', '備考'),
                    new StayOut(null, 1, 4, 0, 0, 0, 0, 0, 0, 0, 0, '2022-06-01 00:00:00', 3, '備考', '備考'),
                ],
                // 対象年
                2022,
                // 対象月
                1
            ]
        ];
    }
}
