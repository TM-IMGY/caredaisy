<?php

namespace Tests\Unit\DomainServices;

use App\Lib\DomainService\EndOfLifeCareAdditionSpecification;
use App\Lib\DomainService\ResultFlagSpecification;
use App\Lib\Entity\FacilityUser\StayOut;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\Entity\CareRewardHistory;
use App\Lib\Entity\CareLevel;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\FacilityUserCare;
use App\Lib\Entity\FacilityUserCareRecord;
use App\Lib\Entity\FacilityUserServiceRecord;
use App\Lib\Factory\FacilityUserCareFactory;
use App\Lib\Factory\FacilityUserServiceFactory;
use App\Lib\Factory\ResultFlagFactory;
use App\Lib\MockRepository\ServiceItemCodesMockRepository;
use App\Lib\MockRepository\ServiceCodeConditionalBranchMockRepository;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use PHPUnit\Framework\TestCase;
use Tests\Factory\TestCareRewardHistoryFactory;
use Tests\Factory\TestFacilityUserFactory;

/**
 * 実績フラグの仕様のテスト。
 */
class ResultFlagSpecificationTest extends TestCase
{
    public function dataProvider(): array
    {
        // 介護報酬履歴のファクトリを作成する。
        $careRewardHistoryFactory = new TestCareRewardHistoryFactory();
        // 施設利用者のファクトリを作成する。
        $facilityUserFactory = new TestFacilityUserFactory();

        return [
            // テストの目的: 看取り介護加算が入居日と外泊日の影響を受けるかテストする。
            'case_1' => [
                // 介護報酬履歴(看取り加算あり)
                $careRewardHistoryFactory->generateEndOfLife(
                    '2021-8-1',
                    '2021-9-30',
                    EndOfLifeCareAdditionSpecification::ADDITIONAL
                ),
                // 施設利用者(同意日 => 8/17, 看取り日 => 9/18, 退去日 => 9/18, 入居日 => 8/18)
                $facilityUserFactory->generateEndOfLife('2021-8-17', '2021-9-18', '2021-9-18', '2021-8-18'),
                // 施設利用者の介護情報(要介護1)
                [(new FacilityUserCareFactory())->generateCareLevel1Test('2021-9-30', '2021-8-1')],
                // 施設利用者のサービス(種類32)
                [(new FacilityUserServiceFactory())->generate32Test('2021-9-30', '2021-8-1')],
                // 実績フラグ(加算Ⅰ: 8月18日のみ実績が立つ)
                (new ResultFlagFactory())->generateOneMonthAgo('0000000000000000010000000000000'),
                // サービス項目コードID(認知症対応型看取り介護加算１)
                36,
                // 施設利用者の外泊
                [
                    new StayOut('2021/8/18 23:58:00', 1, null, 0, 0, 0, 0, 0, 0, 0, 0, '2021/8/18 00:01:00', 1, '備考', '備考'),
                    new StayOut(null, 1, null, 0, 0, 0, 0, 0, 0, 0, 0, '2021/8/19 00:00:00', 1, '備考', '備考'),
                ],
                // 対象年
                2021,
                // 対象月
                9
            ],
            // テストの目的: 基本のサービスが看取り日の影響を受けるかテストする。
            'case_2' => [
                // 介護報酬履歴(加算なし)
                $careRewardHistoryFactory->generateInitial('2021-9-1', '2021-9-30'),
                // 施設利用者(同意日 => 8/17, 看取り日 => 9/18, 退去日 => 9/18, 入居日 => 8/1)
                $facilityUserFactory->generateEndOfLife('2021-8-17', '2021-9-18', '2021-9-18', '2021-8-1'),
                // 施設利用者の介護情報(要介護1)
                [(new FacilityUserCareFactory())->generateCareLevel1Test('2021-9-30', '2021-9-1')],
                // 施設利用者のサービス(種類32)
                [(new FacilityUserServiceFactory())->generate32Test('2021-9-30', '2021-9-1')],
                // 実績フラグ(9月18日まで実績が立つ)
                (new ResultFlagFactory())->generateTargetYm('1111111111111111110000000000000', 18),
                // サービス項目コードID(認知症共同生活介護Ⅰ１)
                1,
                // 施設利用者の外泊
                [],
                // 対象年
                2021,
                // 対象月
                9
            ],
            // テストの目的: 基本のサービスが退去日の影響を受けるかテストする。
            // 加えて退去日が看取り日より優先されるかテストする。
            'case_3' => [
                // 介護報酬履歴(加算なし)
                $careRewardHistoryFactory->generateInitial('2021-9-1', '2021-9-30'),
                // 施設利用者(同意日 => 8/17, 看取り日 => 9/18, 退去日 => 9/17, 入居日 => 8/1)
                $facilityUserFactory->generateEndOfLife('2021-8-17', '2021-9-18', '2021-9-17', '2021-8-1'),
                // 施設利用者の介護情報(要介護1)
                [(new FacilityUserCareFactory())->generateCareLevel1Test('2021-9-30', '2021-9-1')],
                // 施設利用者のサービス(種類32)
                [(new FacilityUserServiceFactory())->generate32Test('2021-9-30', '2021-9-1')],
                // 実績フラグ(9月17日まで実績が立つ)
                (new ResultFlagFactory())->generateTargetYm('1111111111111111100000000000000', 17),
                // サービス項目コードID(認知症共同生活介護Ⅰ１)
                1,
                // 施設利用者の外泊
                [],
                // 対象年
                2021,
                // 対象月
                9
            ],
            // テストの目的: 基本のサービスが認定情報の影響を受けるかテストする。
            'case_4' => [
                // 介護報酬履歴(加算なし)
                $careRewardHistoryFactory->generateInitial('2021-9-1', '2021-9-30'),
                // 施設利用者
                $facilityUserFactory->generateInitial('2021-09-01'),
                // 施設利用者の介護情報(非該当 => 9/6-9/10, 要介護1 => 9/11-9/20, 要介護5 => 9/21=>9/25, 要介護1 => 9/26-10/1)
                [
                    (new FacilityUserCareFactory())->generateNotApplicableTest('2021-9-6', '2021-9-10'),
                    (new FacilityUserCareFactory())->generateCareLevel1Test('2021-9-20', '2021-9-11'),
                    (new FacilityUserCareFactory())->generateCareLevel5Test('2021-9-25', '2021-9-21'),
                    (new FacilityUserCareFactory())->generateCareLevel1Test('2021-10-1', '2021-9-26'),
                ],
                // 施設利用者のサービス(種類32)
                [(new FacilityUserServiceFactory())->generate32Test('2021-9-30', '2021-9-1')],
                // 実績フラグ(要介護1の期間だけ実績が立つ)
                (new ResultFlagFactory())->generateTargetYm('0000000000111111111100000111110', 15),
                // サービス項目コードID(認知症共同生活介護Ⅰ１)
                1,
                // 施設利用者の外泊
                [],
                // 対象年
                2021,
                // 対象月
                9
            ],
            // テストの目的: 実績が立たないサービス項目コードを確認する。
            'case_5' => [
                // 介護報酬履歴(加算なし)
                $careRewardHistoryFactory->generateInitial('2021-9-1', '2021-9-30'),
                // 施設利用者
                $facilityUserFactory->generateInitial('2021-09-01'),
                // 施設利用者の介護情報(要介護1)
                [(new FacilityUserCareFactory())->generateCareLevel1Test('2021-9-30', '2021-9-1')],
                // 施設利用者のサービス(種類32)
                [(new FacilityUserServiceFactory())->generate32Test('2021-9-30', '2021-9-1')],
                // 実績フラグ
                (new ResultFlagFactory())->generateInitial(),
                // サービス項目コードID(認知症対応型生活機能向上連携加算Ⅰ)
                47,
                // 施設利用者の外泊
                [],
                // 対象年
                2021,
                // 対象月
                9
            ],
            // テストの目的: 月ごとに実績が立つサービス項目コードを確認する。
            'case_6' => [
                // 介護報酬履歴(加算なし)
                $careRewardHistoryFactory->generateInitial('2021-9-1', '2021-9-30'),
                // 施設利用者
                $facilityUserFactory->generateInitial('2021-09-01'),
                // 施設利用者の介護情報(要介護1)
                [(new FacilityUserCareFactory())->generateCareLevel1Test('2021-9-30', '2021-9-1')],
                // 施設利用者のサービス(種類32)
                [(new FacilityUserServiceFactory())->generate32Test('2021-9-30', '2021-9-1')],
                // 実績フラグ
                (new ResultFlagFactory())->generatePerMonth(),
                // サービス項目コードID(認知症対応型生活機能向上連携加算Ⅱ)
                48,
                // 施設利用者の外泊
                [],
                // 対象年
                2021,
                // 対象月
                9
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param CareRewardHistory $careRewardHistory 介護報酬履歴
     * @param FacilityUser $facilityUser 施設利用者
     * @param FacilityUserCare[] $facilityUserCares 施設利用者の介護情報
     * @param FacilityUserService[] $facilityUserServices 施設利用者のサービス
     * @param ResultFlag $resultFlag 実績フラグ
     * @param int $itemCodeId サービス項目コードID
     * @param StayOut[] $stayOuts 外泊
     * @param int $year 対象年
     * @param int $month 対象月
     */
    public function testSpecification(
        CareRewardHistory $careRewardHistory,
        FacilityUser $facilityUser,
        array $facilityUserCares,
        array $facilityUserServices,
        ResultFlag $resultFlag,
        int $itemCodeId,
        array $stayOuts,
        int $year,
        int $month
    ): void {
        // 施設利用者のサービスの記録を作成する。
        $facilityUserServiceRecord = new FacilityUserServiceRecord(
            $facilityUser->getFacilityUserId(),
            $facilityUserServices
        );

        // 施設利用者の最新のサービス種類を取得する。
        $latestTypeCode = $facilityUserServiceRecord->getLatest()->getServiceTypeCode()->getServiceTypeCode();

        // サービスコード条件分岐表を取得する。
        $conditionalBranch = (new ServiceCodeConditionalBranchMockRepository())->find($latestTypeCode, $year, $month);

        // 実績フラグを取得する。
        $resultFlagTestTarget = ResultFlagSpecification::get(
            $careRewardHistory,
            $facilityUser,
            new FacilityUserCareRecord($facilityUser->getFacilityUserId(), $facilityUserCares),
            $facilityUserServiceRecord,
            $conditionalBranch,
            (new ServiceItemCodesMockRepository())->find($itemCodeId, $year, $month),
            new StayOutRecord($stayOuts),
            $year,
            $month
        );

        // 日割対象日 が正しいかテストする。
        $this->assertEquals($resultFlag->getDateDailyRate(), $resultFlagTestTarget->getDateDailyRate());
        // 日割対象日(前月) が正しいかテストする。
        $this->assertEquals($resultFlag->getDateDailyRateOneMonthAgo(), $resultFlagTestTarget->getDateDailyRateOneMonthAgo());
        // 日割対象日(前々月) が正しいかテストする。
        $this->assertEquals($resultFlag->getDateDailyRateTwoMonthAgo(), $resultFlagTestTarget->getDateDailyRateTwoMonthAgo());
        // 回数／日数 が正しいかテストする。
        $this->assertEquals($resultFlag->getServiceCountDate(), $resultFlagTestTarget->getServiceCountDate());
    }
}
