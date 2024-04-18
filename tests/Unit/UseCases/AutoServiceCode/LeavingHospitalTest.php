<?php

namespace Tests\Unit\UseCases\AutoServiceCode;

use App\Lib\ApplicationBusinessRules\UseCases\Interactors\AutoServiceCodeGetInteractor;
use App\Lib\Entity\FacilityUser\StayOut;
use App\Lib\Entity\FacilityUser\StayOutRecord;
use App\Lib\InMemoryRepository\FacilityUser\StayOutRecordInMemoryRepository;
use App\Lib\MockRepository\DataSets\FacilityDataSets;
use App\Lib\MockRepository\DataSets\FacilityUserDataSets;
use App\Lib\MockRepository\CareRewardHistoryMockRepository;
use App\Lib\MockRepository\FacilityMockRepository;
use App\Lib\MockRepository\FacilityUserCareRecordMockRepository;
use App\Lib\MockRepository\FacilityUserIndependenceMockRepository;
use App\Lib\MockRepository\FacilityUserMockRepository;
use App\Lib\MockRepository\FacilityUserServiceRecordMockRepository;
use App\Lib\MockRepository\ServiceCodeConditionalBranchMockRepository;
use App\Lib\MockRepository\ServiceItemCodesMockRepository;
use PHPUnit\Framework\TestCase;

class LeavingHospitalTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            // テスト内容: 特定施設退院退所時連携加算 が入居日、外泊日、退去日に実績が消える。
            'case_1' => [
                [
                    [
                        'date_daily_rate' => '0110000000111111111100000001100',
                        'date_daily_rate_one_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_two_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_schedule' => '011111111111111111111111111111',
                        'facility_name_kanji' => '事業所',
                        'facility_number' => '0000000001',
                        'service_count_date' => 14,
                        'service_item_code' => '1111',
                        'service_item_code_id' => 148,
                        'service_item_name' => '特定施設生活介護１',
                        'service_type_code' => '33',
                        'target_date' => '2022-11-01',
                        'unit_number' => 538
                    ],
                    [
                        'date_daily_rate' => '0110000000111111111100000001100',
                        'date_daily_rate_one_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_two_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_schedule' => '011111111111111111111111111111',
                        'facility_name_kanji' => '事業所',
                        'facility_number' => '0000000001',
                        'service_count_date' => 14,
                        'service_item_code' => '6330',
                        'service_item_code_id' => 780,
                        'service_item_name' => '特定施設退院退所時連携加算',
                        'service_type_code' => '33',
                        'target_date' => '2022-11-01',
                        'unit_number' => 30
                    ]
                ],
                FacilityDataSets::USE_CASE_AUTO_SERVICE_CODE_TEST,
                FacilityUserDataSets::USE_CASE_AUTO_SERVICE_CODE_TEST_1,
                new StayOutRecord([
                    new StayOut('2022/11/10 23:59:00', 7, null, 0, 0, 0, 0, 0, 0, 0, 0, '2022/11/4 00:00:00', 3, '備考', '備考'),
                    new StayOut('2022/11/28 23:58:00', 7, null, 0, 0, 0, 0, 0, 0, 0, 0, '2022/11/20 00:01:00', 3, '備考', '備考')
                ]),
                2022,
                11
            ],
            // テスト内容: 特定施設退院退所時連携加算 が看取り日に実績が消える。
            'case_2' => [
                [
                    [
                        'date_daily_rate' => '1111111111111111111111111111100',
                        'date_daily_rate_one_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_two_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_schedule' => '111111111111111111111111111111',
                        'facility_name_kanji' => '事業所',
                        'facility_number' => '0000000001',
                        'service_count_date' => 29,
                        'service_item_code' => '1111',
                        'service_item_code_id' => 148,
                        'service_item_name' => '特定施設生活介護１',
                        'service_type_code' => '33',
                        'target_date' => '2022-11-01',
                        'unit_number' => 538
                    ],
                    [
                        'date_daily_rate' => '1111111111111111111111111111100',
                        'date_daily_rate_one_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_two_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_schedule' => '111111111111111111111111111111',
                        'facility_name_kanji' => '事業所',
                        'facility_number' => '0000000001',
                        'service_count_date' => 29,
                        'service_item_code' => '6330',
                        'service_item_code_id' => 780,
                        'service_item_name' => '特定施設退院退所時連携加算',
                        'service_type_code' => '33',
                        'target_date' => '2022-11-01',
                        'unit_number' => 30
                    ],
                    [
                        'date_daily_rate' => '0000000000000000000000000000100',
                        'date_daily_rate_one_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_two_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_schedule' => '000000000000000000000000000000',
                        'facility_name_kanji' => '事業所',
                        'facility_number' => '0000000001',
                        'service_count_date' => 1,
                        'service_item_code' => '6127',
                        'service_item_code_id' => 784,
                        'service_item_name' => '特定施設看取り介護加算Ⅰ４',
                        'service_type_code' => '33',
                        'target_date' => '2022-11-01',
                        'unit_number' => 1280
                    ]
                ],
                FacilityDataSets::USE_CASE_AUTO_SERVICE_CODE_TEST,
                FacilityUserDataSets::USE_CASE_AUTO_SERVICE_CODE_TEST_2,
                new StayOutRecord([]),
                2022,
                11
            ],
            // テスト内容: 特定施設退院退所時連携加算 が認定情報の変化を受けない。
            'case_3' => [
                [
                    [
                        'date_daily_rate' => '1111111111000000000000000000000',
                        'date_daily_rate_one_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_two_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_schedule' => '111111111111111111111111111111',
                        'facility_name_kanji' => '事業所',
                        'facility_number' => '0000000001',
                        'service_count_date' => 10,
                        'service_item_code' => '1111',
                        'service_item_code_id' => 148,
                        'service_item_name' => '特定施設生活介護１',
                        'service_type_code' => '33',
                        'target_date' => '2022-11-01',
                        'unit_number' => 538
                    ],
                    [
                        'date_daily_rate' => '0000000000111111111111111111110',
                        'date_daily_rate_one_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_two_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_schedule' => '111111111111111111111111111111',
                        'facility_name_kanji' => '事業所',
                        'facility_number' => '0000000001',
                        'service_count_date' => 20,
                        'service_item_code' => '1121',
                        'service_item_code_id' => 149,
                        'service_item_name' => '特定施設生活介護２',
                        'service_type_code' => '33',
                        'target_date' => '2022-11-01',
                        'unit_number' => 604
                    ],
                    [
                        'date_daily_rate' => '1111111111111111111111111111110',
                        'date_daily_rate_one_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_two_month_ago' => '0000000000000000000000000000000',
                        'date_daily_rate_schedule' => '111111111111111111111111111111',
                        'facility_name_kanji' => '事業所',
                        'facility_number' => '0000000001',
                        'service_count_date' => 30,
                        'service_item_code' => '6330',
                        'service_item_code_id' => 780,
                        'service_item_name' => '特定施設退院退所時連携加算',
                        'service_type_code' => '33',
                        'target_date' => '2022-11-01',
                        'unit_number' => 30
                    ]
                ],
                FacilityDataSets::USE_CASE_AUTO_SERVICE_CODE_TEST,
                FacilityUserDataSets::USE_CASE_AUTO_SERVICE_CODE_TEST_3,
                new StayOutRecord([]),
                2022,
                11
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testUseCase(
        array $correct,
        int $facilityId,
        int $facilityUserId,
        StayOutRecord $stayOutRecord,
        int $year,
        int $month
    ): void {
        // モックリポジトリを作成する。
        $careRewardHistoryRepository = new CareRewardHistoryMockRepository();
        $facilityRepository = new FacilityMockRepository();
        $facilityUserCareRecordRepository = new FacilityUserCareRecordMockRepository;
        $facilityUserRepository = new FacilityUserMockRepository();
        $facilityUserIndependenceRepository = new FacilityUserIndependenceMockRepository();
        $facilityUserServiceRecordRepository = new FacilityUserServiceRecordMockRepository();
        $serviceCodeConditionalBranchRepository = new ServiceCodeConditionalBranchMockRepository();
        $serviceItemCodesRepository = new ServiceItemCodesMockRepository();
        $stayOutRecordRepository = new StayOutRecordInMemoryRepository();

        $stayOutRecordRepository->insert($stayOutRecord);

        // ユースケースから出力データを取得する。
        $usecase = new AutoServiceCodeGetInteractor(
            $careRewardHistoryRepository,
            $facilityRepository,
            $facilityUserCareRecordRepository,
            $facilityUserIndependenceRepository,
            $facilityUserRepository,
            $facilityUserServiceRecordRepository,
            $serviceCodeConditionalBranchRepository,
            $serviceItemCodesRepository,
            $stayOutRecordRepository
        );
        $outputData = $usecase->handle($facilityId, $facilityUserId, $year, $month);
        $outputData = $outputData->getData();

        // 出力データと正解が一致するかを確認する。
        $this->assertEquals(count($correct), count($outputData));
        foreach ($outputData as $index => $record) {
            foreach ($record as $key => $value) {
                $this->assertEquals($correct[$index][$key], $value);
            }
        }
    }
}
