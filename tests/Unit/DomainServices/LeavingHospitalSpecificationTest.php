<?php

namespace Tests\Unit\DomainServices;

use App\Lib\DomainService\LeavingHospitalSpecification;
use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUser\StayOut;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\FacilityUserServiceRecord;
use App\Lib\Factory\FacilityUserServiceFactory;
use App\Lib\Factory\ResultFlagFactory;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use PHPUnit\Framework\TestCase;
use Tests\Factory\TestCareRewardHistoryFactory;
use Tests\Factory\TestFacilityUserFactory;

/**
 * 退院退所時相談加算の仕様のテスト。
 */
class LeavingHospitalSpecificationTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param CareRewardHistory[] $careRewardHistories 介護報酬履歴全て
     * @param FacilityUser $facilityUser 施設利用者
     * @param FacilityUserService[] $facilityUserServices 施設利用者のサービス全て
     * @param ResultFlag[] $resultFlagObjectCorrects 実績フラグの対象年月ごとの正解
     * @param StayOut[] $stayOuts 外泊全て
     * @param int $year 対象年
     * @param int $month 対象月
     */
    public function testSpecification(
        array $careRewardHistories,
        FacilityUser $facilityUser,
        array $facilityUserServices,
        array $resultFlagObjectCorrects,
        array $stayOuts,
        int $year,
        int $month
    ): void {
        // 退院退所時相談加算の仕様を作成する。
        $leavingHospitalSpecification = new LeavingHospitalSpecification();

        // 実績フラグの対象年月ごとの正解全てを参照する。
        foreach ($resultFlagObjectCorrects as $index => $correct) {
            // 実績フラグを取得する。
            $resultFlagObjectTest = $leavingHospitalSpecification->calculateLeavingHospitalResultFlag(
                new FacilityUserServiceRecord($facilityUser->getFacilityUserId(), $facilityUserServices),
                $careRewardHistories[$index],
                $facilityUser,
                new StayOutRecord($stayOuts),
                $year,
                $month + $index,
            );

            // 日割対象日 が正しいかテストする。
            $this->assertEquals(
                $correct->getDateDailyRate(),
                $resultFlagObjectTest->getDateDailyRate()
            );
            // 日割対象日(1月前) が正しいかテストする。
            $this->assertEquals(
                $correct->getDateDailyRateOneMonthAgo(),
                $resultFlagObjectTest->getDateDailyRateOneMonthAgo()
            );
            // 日割対象日(2月前) が正しいかテストする。
            $this->assertEquals(
                $correct->getDateDailyRateTwoMonthAgo(),
                $resultFlagObjectTest->getDateDailyRateTwoMonthAgo()
            );
            // 回数／日数 が正しいかテストする。
            $this->assertEquals(
                $correct->getServiceCountDate(),
                $resultFlagObjectTest->getServiceCountDate()
            );
        }
    }

    /**
     * データプロバイダ。
     */
    public function dataProvider(): array
    {
        $careRewardHistoryFactory = new TestCareRewardHistoryFactory();
        $facilityUserFactory = new TestFacilityUserFactory();
        $facilityUserServiceFactory = new FacilityUserServiceFactory();
        $resultFlagFactory = new ResultFlagFactory();

        return [
            // テストの目的: 退院退所時相談加算がない場合、ある場合の動きを確認する。
            // 加えて入居前が介護老人保健施設の場合の動きを確認する。
            'case_1' => [
                // 介護報酬履歴
                [
                    // 退院退所時相談加算なし(10/1-10/31)
                    $careRewardHistoryFactory->generateInitial('2022-10-1', '2022-10-31'),
                    // 退院退所時相談加算あり(11/1-11/30)
                    $careRewardHistoryFactory->generateLeavingHospital('2022-11-1', '2022-11-30')
                ],
                // 施設利用者(入居前 => 医療機関, 入居日 => 10/15)
                $facilityUserFactory->generateBeforeInStatusMedical('2022-10-15'),
                // 施設利用者のサービス
                [
                    // 種類33
                    $facilityUserServiceFactory->generate33Test('2022-11-30', '2022-10-1')
                ],
                // 実績フラグ
                [
                    // 実績が立たない
                    $resultFlagFactory->generateInitial(),
                    // 全てに実績が立つ
                    $resultFlagFactory->generateTargetYm('1111111111111000000000000000000', 13),
                ],
                // 外泊
                [],
                // 対象年
                2022,
                // 対象月
                10
            ],
            // テストの目的: サービス種類ごとの動きを確認する。
            'case_2' => [
                // 介護報酬履歴
                [
                    // 退院退所時相談加算あり
                    $careRewardHistoryFactory->generateLeavingHospital('2022-10-01', '2022-10-31')
                ],
                // 施設利用者(入居前 => 医療機関, 入居日 => 10/1)
                $facilityUserFactory->generateBeforeInStatusMedical('2022-10-01'),
                // 施設利用者のサービス
                [
                    // 種類32
                    $facilityUserServiceFactory->generate32Test('2022-10-10', '2022-10-01'),
                    // 種類33
                    $facilityUserServiceFactory->generate36Test('2022-10-20', '2022-10-11'),
                    // 種類36
                    $facilityUserServiceFactory->generate36Test('2022-11-01', '2022-10-21')
                ],
                // 実績フラグ(10/11-10/30に実績が立つ)
                [
                    $resultFlagFactory->generateTargetYm('0000000000111111111111111111110', 20)
                ],
                // 外泊
                [],
                // 対象年
                2022,
                // 対象月
                10
            ],
            // テストの目的: 入居前が介護老人保健施設の場合の動きを確認する。
            'case_3' => [
                // 介護報酬履歴
                [
                    // 退院退所時相談加算あり
                    $careRewardHistoryFactory->generateLeavingHospital('2022-10-01', '2022-10-31')
                ],
                // 施設利用者(入居前 => 介護老人保健施設, 入居日 => 10/1)
                $facilityUserFactory->generateBeforeInStatusElderlyCare('2022-10-01'),
                // 施設利用者のサービス
                [
                    // 種類33
                    $facilityUserServiceFactory->generate33Test('2022-10-31', '2022-10-01')
                ],
                // 実績フラグ(全てに実績が立つ)
                [
                    $resultFlagFactory->generateTargetYm('1111111111111111111111111111110', 30)
                ],
                // 外泊
                [],
                // 対象年
                2022,
                // 対象月
                10
            ],
            // テストの目的: 算定対象以外の入居前の場合の動きを確認する。
            'case_4' => [
                // 介護報酬履歴
                [
                    // 退院退所時相談加算あり
                    $careRewardHistoryFactory->generateLeavingHospital('2022-10-01', '2022-10-31')
                ],
                // 施設利用者(入居日 => 10/1)
                $facilityUserFactory->generateInitial('2022-10-01'),
                // 施設利用者のサービス
                [
                    // 種類33
                    $facilityUserServiceFactory->generate33Test('2022-10-31', '2022-10-01')
                ],
                // 実績フラグ
                [
                    // 全てに実績が立たない
                    $resultFlagFactory->generateInitial()
                ],
                // 外泊
                [],
                // 対象年
                2022,
                // 対象月
                10
            ],
            // テストの目的: 31日以上入院している場合、31日以上入所している場合の動きを確認する。
            // 加えて、算定対象でない外泊状態(外泊)で31日以上外泊している場合も見る。
            // 加えて、31日以上入院していない場合も見る。
            // 加えて、外泊が複数登録されている場合、外泊が分まで登録されている場合も見る。
            'case_5' => [
                // 介護報酬履歴
                [
                    $careRewardHistoryFactory->generateLeavingHospital('2022-07-01', '2022-07-31'),
                    $careRewardHistoryFactory->generateLeavingHospital('2022-08-01', '2022-08-31'),
                    $careRewardHistoryFactory->generateLeavingHospital('2022-09-01', '2022-09-01'),
                    $careRewardHistoryFactory->generateLeavingHospital('2022-10-01', '2022-10-31'),
                    $careRewardHistoryFactory->generateLeavingHospital('2022-11-01', '2022-11-30'),
                    $careRewardHistoryFactory->generateLeavingHospital('2022-12-01', '2022-12-31'),
                ],
                // 施設利用者(入居前 => 居宅, 入居日 => 7/1)
                $facilityUserFactory->generateInitial('2022-07-01'),
                // 施設利用者のサービス
                [
                    // 種類33
                    $facilityUserServiceFactory->generate33Test('2022-12-31', '2022-07-01')
                ],
                // 実績フラグ(7/31-8/29、9/29-10/28に実績が立つ)
                [
                    $resultFlagFactory->generateTargetYm('0000000000000000000000000000001', 1),
                    $resultFlagFactory->generateTargetYm('1111111111111111111111111111100', 29),
                    $resultFlagFactory->generateTargetYm('0000000000000000000000000000110', 2),
                    $resultFlagFactory->generateTargetYm('1111111111111111111111111111000', 28),
                    $resultFlagFactory->generateInitial(),
                    $resultFlagFactory->generateInitial(),
                ],
                // 外泊
                [
                    new StayOut('2022-07-31 23:58:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022-07-01 00:01:00', 3, '備考', '備考'),
                    new StayOut('2022-09-29 23:59:00', 1, 2, 0, 0, 0, 0, 0, 0, 0, 0, '2022-08-30 00:00:00', 5, '備考', '備考'),
                    new StayOut('2022-11-28 23:59:00', 1, 3, 0, 0, 0, 0, 0, 0, 0, 0, '2022-10-29 00:00:00', 1, '備考', '備考'),
                    new StayOut('2023-01-26 23:59:00', 1, 4, 0, 0, 0, 0, 0, 0, 0, 0, '2022-12-28 00:00:00', 3, '備考', '備考'),
                ],
                // 対象年
                2022,
                // 対象月
                7
            ],
            // テストの目的: 外泊の終了日が登録されていない場合の動きを確認する。
            'case_6' => [
                // 介護報酬履歴
                [
                    // 退院退所時相談加算あり
                    $careRewardHistoryFactory->generateLeavingHospital('2022-9-01', '2022-10-31')
                ],
                // 施設利用者(入居前 => 居宅, 入居日 => 9/1)
                $facilityUserFactory->generateInitial('2022-9-01'),
                // 施設利用者のサービス(種類33)
                [$facilityUserServiceFactory->generate33Test('2022-10-31', '2022-9-01')],
                // 実績フラグ
                [
                    $resultFlagFactory->generateInitial()
                ],
                // 外泊
                [
                    new StayOut(null, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/09/01 00:00:00', 3, '備考', '備考'),
                ],
                // 対象年
                2022,
                // 対象月
                10
            ]
        ];
    }
}
