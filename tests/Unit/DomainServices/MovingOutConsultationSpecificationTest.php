<?php

namespace Tests\Unit\DomainServices;

use App\Lib\DomainService\MovingOutConsultationSpecification;
use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\FacilityUser;
use PHPUnit\Framework\TestCase;
use Tests\Factory\TestAfterOutStatusFactory;
use Tests\Factory\TestCareRewardHistoryFactory;
use Tests\Factory\TestFacilityUserFactory;

/**
 * 退居時相談援助加算のテスト。
 */
class MovingOutConsultationSpecificationTest extends TestCase
{
    public function dataProvider(): array
    {
        // 退去後の状況のファクトリ。
        $afterOutStatusFactory = new TestAfterOutStatusFactory();
        $death = $afterOutStatusFactory->generateDeath();
        $residence = $afterOutStatusFactory->generateResidence();

        // 介護報酬履歴のファクトリ。
        $careRewardHistoryFactory = new TestCareRewardHistoryFactory();

        // 施設利用者のファクトリ。
        $facilityUserFactory = new TestFacilityUserFactory();

        return [
            // テストの目的: 介護報酬履歴にない場合の動きを確認する(ある場合の動きは他で確認している)。
            'case_1' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateInitial('2022-02-01', '2022-02-28'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2022-01-01', '2022-02-01'),
                // 利用可能かの正解。
                false,
                // 対象年
                2022,
                // 対象月
                2
            ],
            // テストの目的: 施設利用者に退去日がない場合の動きを確認する(ある場合の動きは他で確認している)。
            'case_2' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateInitial('2022-02-01', '2022-02-28'),
                // 施設利用者
                $facilityUserFactory->generateInitial('2022-01-01'),
                // 利用可能かの正解。
                false,
                // 対象年
                2022,
                // 対象月
                2
            ],
            // テストの目的: 施設利用者が対象年月に退去していない場合の動きを確認する(対象年月の場合の動きは他で確認している)。
            'case_3' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-02-01', '2022-02-28'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2022-01-01', '2022-02-01'),
                // 利用可能かの正解。
                false,
                // 対象年
                2022,
                // 対象月
                1
            ],
            // テストの目的: 施設利用者の退去状況が居宅以外の場合の動きを確認する(居宅の場合の動きは他で確認している)。
            'case_4' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-02-01', '2022-02-28'),
                // 施設利用者
                $facilityUserFactory->generateEnd($death, '2022-01-01', '2022-02-01'),
                // 利用可能かの正解。
                false,
                // 対象年
                2022,
                // 対象月
                2
            ],
            // テストの目的: 施設利用者が1か月丁度利用している場合の動きを確認する。
            'case_5' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-01-01', '2022-01-31'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2022-01-01', '2022-01-31'),
                // 利用可能かの正解。
                false,
                // 対象年
                2022,
                // 対象月
                1
            ],
            // テストの目的: 施設利用者が1か月を超えて利用している場合の動きを確認する。
            'case_6' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-02-01', '2022-02-28'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2022-01-01', '2022-02-01'),
                // 利用可能かの正解。
                true,
                // 対象年
                2022,
                // 対象月
                2
            ],
            // テストの目的: 施設利用者が1か月丁度利用している場合(2月平年)の動きを確認する。
            'case_7' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-02-01', '2022-02-28'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2022-02-01', '2022-02-28'),
                // 利用可能かの正解。
                false,
                // 対象年
                2022,
                // 対象月
                2
            ],
            // テストの目的: 施設利用者が1か月を超えて利用している場合(2月平年)の動きを確認する。
            'case_8' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-03-01', '2022-03-31'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2022-02-01', '2022-03-01'),
                // 利用可能かの正解。
                true,
                // 対象年
                2022,
                // 対象月
                3
            ],
            // テストの目的: 施設利用者が1か月丁度利用している場合(2月閏年)の動きを確認する。
            'case_9' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2024-02-01', '2024-02-29'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2024-02-01', '2024-02-29'),
                // 利用可能かの正解。
                false,
                // 対象年
                2022,
                // 対象月
                2
            ],
            // テストの目的: 施設利用者が1か月を超えて利用している場合(2月閏年)の動きを確認する。
            'case_10' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2024-03-01', '2024-03-31'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2024-02-01', '2024-03-01'),
                // 利用可能かの正解。
                true,
                // 対象年
                2024,
                // 対象月
                3
            ],
            // テストの目的: 月の途中から起算し、退去月に応当日のある場合の、算定できない境界を確認する。
            'case_11' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-02-01', '2022-02-28'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2022-01-10', '2022-02-09'),
                // 利用可能かの正解。
                false,
                // 対象年
                2022,
                // 対象月
                2
            ],
            // テストの目的: 月の途中から起算し、退去月に応当日のある場合の、算定できる境界を確認する。
            'case_12' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-02-01', '2022-02-28'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2022-01-10', '2022-02-10'),
                // 利用可能かの正解。
                true,
                // 対象年
                2022,
                // 対象月
                2
            ],
            // テストの目的: 月の途中から起算し、最終月に応当日のない場合の、算定できない境界を確認する。
            'case_13' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-02-01', '2022-02-28'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2022-01-31', '2022-02-28'),
                // 利用可能かの正解。
                false,
                // 対象年
                2022,
                // 対象月
                2
            ],
            // テストの目的: 月の途中から起算し、最終月に応当日のない場合の、算定できる境界を確認する。
            'case_14' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-03-01', '2022-03-31'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2022-01-31', '2022-03-01'),
                // 利用可能かの正解。
                true,
                // 対象年
                2022,
                // 対象月
                3
            ],
            // テストの目的: 施設利用者の利用期間が年を跨いでいる場合の、算定できない境界を確認する。
            'case_15' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-01-01', '2022-01-31'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2021-12-15', '2022-01-14'),
                // 利用可能かの正解。
                false,
                // 対象年
                2022,
                // 対象月
                1
            ],
            // テストの目的: 施設利用者の利用期間が年を跨いでいる場合の、算定できる境界を確認する。
            'case_16' => [
                // 介護報酬履歴
                $careRewardHistoryFactory->generateMovingOutConsultation('2022-01-01', '2022-01-31'),
                // 施設利用者
                $facilityUserFactory->generateEnd($residence, '2021-12-15', '2022-01-15'),
                // 利用可能かの正解。
                true,
                // 対象年
                2022,
                // 対象月
                1
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param bool $isAvailableCorrect 利用可の可能かの正解。
     */
    public function testSpecification(
        CareRewardHistory $careRewardHistory,
        FacilityUser $facilityUser,
        bool $isAvailableCorrect,
        int $year,
        int $month
    ): void {
        $isAvailable = MovingOutConsultationSpecification::isAvailable(
            $careRewardHistory,
            $facilityUser,
            $year,
            $month
        );

        $this->assertEquals($isAvailableCorrect, $isAvailable);
    }
}
