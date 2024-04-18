<?php

namespace Tests\Unit\DomainServices;

use App\Lib\DomainService\StayOutSpecification;
use App\Lib\Entity\FacilityUser\StayOut;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\Entity\CareRewardHistory;
use PHPUnit\Framework\TestCase;
use Tests\Factory\TestCareRewardHistoryFactory;

/**
 * 国保連請求の外泊の仕様のテスト。
 */
class HospitalizationSpecificationTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testHospitalization(
        CareRewardHistory $histories,
        StayOutRecord $records,
        array $results,
        array $useResults
    ) :void {
        $year = 2022;

        $i = 0;
        foreach ($results as $key => $result) {
            $dateDailyRate = $result;
            $serviceCountDate = mb_substr_count($dateDailyRate, 1);

            // 入院の仕様を取得する
            $hospitalizationSpecification = new StayOutSpecification();
            $resultFlag = $hospitalizationSpecification->calculateHospitalizationResultFlag($records, $year, $key);

            $this->assertEquals($dateDailyRate, $resultFlag->getDateDailyRate());
            $this->assertEquals($serviceCountDate, $resultFlag->getServiceCountDate());

            // 入院時費用のサービスコード利用が可能か
            $resultServiceCode = $hospitalizationSpecification->isAvailable($histories, $records, $year, $key);
            $this->assertTrue($useResults[$i] === $resultServiceCode);
            $i++;
        }
    }

    public function dataProvider()
    {
        // 介護報酬履歴のファクトリを作成する。
        $careRewardHistory = (new TestCareRewardHistoryFactory())->generateHospitalization('2022-07-01', '2022-12-31');

        return [
            // ケース1: 対象月内で1回入退院を行う場合
            // (外泊日範囲) 7/5-7/26 (外泊理由) 入院
            // (実績フラグ範囲) 7/6-7/11 7月分満6日
            'case1' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/07/26 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/05 00:00:00', 3, '備考', '備考')
                ]),
                [
                    7 => '0000011111100000000000000000000',
                ],
                [true]
            ],
            // ケース2: 対象月内で複数回入退院を行う場合
            // (外泊日範囲) 7/5-7/7, 7/13-7/15, 7/24-7/30 (外泊理由) 入院
            // (実績フラグ範囲) 7/6, 7/14, 7/25-7/28 7月分満6日
            'case2' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/07/07 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/05 00:00:00', 3, '備考', '備考'),
                    new StayOut('2022/07/15 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/13 00:00:00', 3, '備考', '備考'),
                    new StayOut('2022/07/30 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/24 00:00:00', 3, '備考', '備考')
                ]),
                [
                    7 => '0000010000000100000000001111000',
                ],
                [true]
            ],
            // ケース3: 対象月から翌月にまたいで1回入退院を行う場合(連続加算なし)
            // (外泊日範囲) 7/24-8/9 (外泊理由) 入院
            // (実績フラグ範囲) 7/25-7/30 7月分満6日
            'case3' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/08/09 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/24 00:00:00', 3, '備考', '備考'),
                ]),
                [
                    7 => '0000000000000000000000001111110',
                    8 => '0000000000000000000000000000000',
                ],
                [true, false]
            ],
            // ケース4: 対象月から翌月にまたいで1回入退院を行う場合(連続加算あり)
            // (外泊日範囲) 7/25-8/9 (外泊理由) 入院
            // (実績フラグ範囲) 7/26-7/31 7月分満6日, 8/1-8/6 8月分満6日
            'case4' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/08/09 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/25 00:00:00', 3, '備考', '備考'),
                ]),
                [
                    7 => '0000000000000000000000000111111',
                    8 => '1111110000000000000000000000000',
                ],
                [true, true]
            ],
            // ケース5: 対象月から翌々月にまたいで1回入退院を行う場合(連続加算あり)
            // (外泊日範囲) 7/25-9/3 (外泊理由) 入院
            // (実績フラグ範囲) 7/26-7/31 7月分満6日, 8/1-8/6 8月分満6日, 9月分なし
            'case5' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/09/03 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/25 00:00:00', 3, '備考', '備考'),
                ]),
                [
                    7 => '0000000000000000000000000111111',
                    8 => '1111110000000000000000000000000',
                    9 => '0000000000000000000000000000000',
                ],
                [true, true, false]
            ],
            // ケース6: 対象月内で1回、また対象月から翌月にまたいで1回入退院を行う場合(連続加算なし)
            // (外泊日範囲) 7/7-7/9, 7/25-8/9 (外泊理由) 入院
            // (実績フラグ範囲) 7/8, 7/26-7/30 7月分満6日, 8月分なし
            'case6' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/07/09 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/07 00:00:00', 3, '備考', '備考'),
                    new StayOut('2022/08/09 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/25 00:00:00', 3, '備考', '備考'),
                ]),
                [
                    7 => '0000000100000000000000000111110',
                    8 => '1111110000000000000000000000000',
                ],
                [true, true]
            ],
            // ケース7: 対象月内で1回、また対象月から翌月にまたいで1回入退院を行う場合(連続加算あり)
            // (外泊日範囲) 7/7-7/9, 7/26-8/9 (外泊理由) 入院
            // (実績フラグ範囲) 7/8, 7/27-7/31 7月分満6日, 8/1-8/6 8月分満6日
            'case7' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/07/09 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/07 00:00:00', 3, '備考', '備考'),
                    new StayOut('2022/08/09 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/26 00:00:00', 3, '備考', '備考'),
                ]),
                [
                    7 => '0000000100000000000000000011111',
                    8 => '1111110000000000000000000000000',
                ],
                [true, true]
            ],
            // ケース8: 対象月内で1回入退院を行う場合(2日分)
            // (外泊日範囲) 7/6-7/9 (外泊理由) 入院
            // (実績フラグ範囲) 7/7-7/8 7月分2日
            'case8' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/07/09 16:01', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/06 15:01', 3, '備考', '備考'),
                ]),
                [
                    7 => '0000001100000000000000000000000',
                ],
                [true]
            ],
            // ケース9: 対象月内に入院期間が存在せず、対象月より前に入院期間が存在する場合
            // (外泊日範囲) 5/5-5/26 (外泊理由) 入院
            // (実績フラグ範囲) 対象月から外れているのでなし
            'case9' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/05/26 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/05/05 00:00:00', 3, '備考', '備考')
                ]),
                [
                    7 => '0000000000000000000000000000000',
                ],
                [false]
            ],
            // ケース10: 外泊終了日が入力されていない場合
            // (外泊日範囲) 7/5- (外泊理由) 入院
            // (実績フラグ範囲) 7/6-7/11 7月分満6日
            'case10' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut(null, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/05 00:00:00', 3, '備考', '備考')
                ]),
                [
                    7 => '0000011111100000000000000000000',
                ],
                [true]
            ],
            // ケース11: 外泊理由が入院以外の場合
            // (外泊日範囲) 7/5-7/26 (外泊理由) 外出
            // (実績フラグ範囲) 7月分なし
            'case11' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/07/26 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/05 00:00:00', 1, '備考', '備考')
                ]),
                [
                    7 => '0000000000000000000000000000000',
                ],
                [false]
            ],
            // ケース12: 対象月内で1回入退院を行う場合(日帰り入院)
            // (外泊日範囲) 7/5 (外泊理由) 入院
            // (実績フラグ範囲) 7月分なし
            'case12' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/07/05 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/05 00:00:00', 3, '備考', '備考')
                ]),
                [
                    7 => '0000000000000000000000000000000',
                ],
                [false]
            ],
            // ケース13: 対象月内で1回入退院を行う場合(0日分)
            // (外泊日範囲) 7/5-7/6 (外泊理由) 入院
            // (実績フラグ範囲) 7月分なし
            'case13' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/07/06 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/07/05 00:00:00', 3, '備考', '備考')
                ]),
                [
                    7 => '0000000000000000000000000000000',
                ],
                [false]
            ],
            // ケース14: 前月から対象月にまたいで入退院を行う場合
            // (外泊日範囲) 6/25-7/7 (外泊理由) 入院
            // (実績フラグ範囲) 7/1-7/6 7月分満6日, 6/26-6/30 6月分5日
            'case14' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/07/07 23:59:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/06/25 00:00:00', 3, '備考', '備考')
                ]),
                [
                    7 => '1111110000000000000000000000000',
                    6 => '0000000000000000000000000111110',
                ],
                [true, true]
            ],
            // ケース15: 事例
            'case15' => [
                $careRewardHistory,
                new StayOutRecord([
                    new StayOut('2022/09/29 10:48:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/09/10 12:48:00', 3, '備考', '備考'),
                    new StayOut('2022/10/27 17:04:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/10/10 17:04:00', 3, '備考', '備考'),
                    new StayOut('2022/11/05 14:16:00', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2022/11/02 14:16:00', 3, '備考', '備考')
                ]),
                [
                    9  => '0000000000111111000000000000000',
                    10 => '0000000000111111000000000000000',
                    11 => '0011000000000000000000000000000'
                ],
                [true, true, true]
            ]
        ];
    }
}
