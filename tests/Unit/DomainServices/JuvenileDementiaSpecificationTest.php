<?php

namespace Tests\Unit\DomainServices;

use App\Lib\DomainService\JuvenileDementiaSpecification;
use App\Lib\Factory\ResultFlagFactory;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Tests\Factory\TestFacilityUserFactory;
use Tests\Factory\TestCareRewardHistoryFactory;

/**
 * 若年性認知症受入加算の仕様のテスト。
 */
class JuvenileDementiaSpecificationTest extends TestCase
{
    public function dataProvider(): array
    {
        $resultFlagFactory = new ResultFlagFactory();
        $resultFlagNoResults = $resultFlagFactory->generateInitial();
        return [
            // テスト目的: 40歳の誕生日の前日がないので加算できないことを確認する。
            'case_1' => [
                '1970-1-1',
                false,
                $resultFlagNoResults,
                2009,
                11
            ],
            // テスト目的: 40歳の誕生日の前日から加算できることを確認する。
            'case_2' => [
                '1970-1-1',
                true,
                new ResultFlag(
                    '0000000000000000000000000000001',
                    '0000000000000000000000000000000',
                    '0000000000000000000000000000000',
                    1
                ),
                2009,
                12
            ],
            // テスト目的: 65歳の誕生日の前々日まで加算できることを確認する。
            'case_3' => [
                '1970-1-1',
                true,
                new ResultFlag(
                    '1111111111111111111111111111110',
                    '0000000000000000000000000000000',
                    '0000000000000000000000000000000',
                    30
                ),
                2034,
                12
            ],
            // テスト目的: 65歳の誕生日の前々日がないので加算できないことを確認する。
            'case_4' => [
                '1970-1-1',
                false,
                $resultFlagNoResults,
                2035,
                1
            ],
            // 閏年の2月に加算の開始日がある場合の挙動を確認する。
            'case_5' => [
                '1972-3-1',
                true,
                new ResultFlag(
                    '0000000000000000000000000000100',
                    '0000000000000000000000000000000',
                    '0000000000000000000000000000000',
                    1
                ),
                2012,
                2
            ]
        ];
    }

    /**
     * 仕様をテストする。
     * @dataProvider dataProvider
     * @param string $birthDay 生年月日。yyyy-mm-dd。
     * @param bool $isAvailableCorrect 取得可能かの正解。
     * @param ResultFlag $resultFlagObjectCorrect 実績フラグの正解。
     * @param int $year 対象年
     * @param int $month 対象月
     */
    public function testSpecification(
        string $birthDay,
        bool $isAvailableCorrect,
        ResultFlag $resultFlagObjectCorrect,
        int $year,
        int $month
    ): void {
        // 施設利用者を生年月日指定で作成する。
        $facilityUser = (new TestFacilityUserFactory())->generateByBirthday(
            $birthDay,
            (new Carbon("${year}-${month}-1"))->format('Y-m-d')
        );

        // 若年性認知症受入加算のある事業所を生成する。
        $careRewardHistory = (new TestCareRewardHistoryFactory())->generateJuvenileDementia(
            JuvenileDementiaSpecification::ADDITIONAL,
            $year,
            $month
        );

        // 加算可能かを判定する。
        $isAvailable = JuvenileDementiaSpecification::isAvailable($careRewardHistory, $facilityUser, $year, $month);
        // 加算可能かを正しく判定するかをテストする。
        $this->assertEquals($isAvailableCorrect, $isAvailable);

        // 若年性認知症受入加算の実績フラグを取得する。
        $resultFlagObject = JuvenileDementiaSpecification::getResultFlag($facilityUser, $year, $month);

        // 日割対象日 を正しく計算しているかテストする。
        $this->assertEquals(
            $resultFlagObjectCorrect->getDateDailyRate(),
            $resultFlagObject->getDateDailyRate()
        );

        // 日割対象日(1月前) を正しく計算しているかテストする。
        $this->assertEquals(
            $resultFlagObjectCorrect->getDateDailyRateOneMonthAgo(),
            $resultFlagObject->getDateDailyRateOneMonthAgo()
        );

        // 日割対象日(2月前) を正しく計算しているかテストする。
        $this->assertEquals(
            $resultFlagObjectCorrect->getDateDailyRateTwoMonthAgo(),
            $resultFlagObject->getDateDailyRateTwoMonthAgo()
        );

        // 回数／日数 を正しく計算しているかテストする。
        $this->assertEquals(
            $resultFlagObjectCorrect->getServiceCountDate(),
            $resultFlagObject->getServiceCountDate()
        );
    }
}
