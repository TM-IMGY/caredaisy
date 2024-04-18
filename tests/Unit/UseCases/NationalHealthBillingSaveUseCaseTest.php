<?php

namespace Tests\Unit\UseCases;

use App\Lib\ApplicationBusinessRules\UseCases\Interactors\NationalHealthBillingSaveInteractor;
use App\Lib\MockRepository\DataSets\FacilityDataSets;
use App\Lib\MockRepository\DataSets\FacilityUserDataSets;
use App\Lib\MockRepository\FacilityAdditionsMockRepository;
use App\Lib\MockRepository\FacilityMockRepository;
use App\Lib\MockRepository\FacilityUserBenefitRecordMockRepository;
use App\Lib\MockRepository\FacilityUserCareRecordMockRepository;
use App\Lib\MockRepository\FacilityUserMockRepository;
use App\Lib\MockRepository\FacilityUserPublicExpenseRecordMockRepository;
use App\Lib\MockRepository\FacilityUserServiceRecordMockRepository;
use App\Lib\MockRepository\NationalHealthBillingMockRepository;
use App\Lib\MockRepository\ServiceItemCodesMockRepository;
use App\Lib\MockRepository\SpecialMedicalCodesMockRepository;
use PHPUnit\Framework\TestCase;

class NationalHealthBillingSaveUseCaseTest extends TestCase
{
    public function saveDataProvider(): array
    {
        return [
            'case_32_pattern_1' => [
                FacilityDataSets::CASE_32_PATTERN_1_ID,
                FacilityUserDataSets::CASE_32_PATTERN_1_ID,
                2022,
                8
            ],
            'case_32_pattern_3' => [
                FacilityDataSets::CASE_32_PATTERN_3_ID,
                FacilityUserDataSets::CASE_32_PATTERN_3_ID,
                2021,
                9
            ],
            'case_32_pattern_4' => [
                FacilityDataSets::CASE_32_PATTERN_4_ID,
                FacilityUserDataSets::CASE_32_PATTERN_4_ID,
                2021,
                9
            ],
            'case_55_pattern_1' => [
                FacilityDataSets::CASE_55_PATTERN_1_ID,
                FacilityUserDataSets::CASE_55_PATTERN_1_ID,
                2022,
                8
            ],
            'case_55_pattern_2' => [
                FacilityDataSets::CASE_55_PATTERN_2_ID,
                FacilityUserDataSets::CASE_55_PATTERN_2_ID,
                2022,
                8
            ],
            'case_55_pattern_3' => [
                FacilityDataSets::CASE_55_PATTERN_3_ID,
                FacilityUserDataSets::CASE_55_PATTERN_3_ID,
                2022,
                8
            ]
        ];
    }

    /**
     * @dataProvider saveDataProvider
     */
    public function testSave(int $facilityId, int $facilityUserId, int $year, int $month): void
    {
        // リポジトリを取得する。
        $facilityRepository = new FacilityMockRepository();
        $facilityAdditionsRepository = new FacilityAdditionsMockRepository();
        $facilityUserRepository = new FacilityUserMockRepository();
        $facilityUserBenefitRecordRepository = new FacilityUserBenefitRecordMockRepository();
        $facilityUserCareRecordRepository = new FacilityUserCareRecordMockRepository();
        $facilityUserPublicExpenseRecordRepository = new FacilityUserPublicExpenseRecordMockRepository();
        $facilityUserServiceRecordRepository = new FacilityUserServiceRecordMockRepository();
        $nationalHealthBillingRepository = new NationalHealthBillingMockRepository();
        $serviceItemCodesRepository = new ServiceItemCodesMockRepository();
        $specialMedicalCodesRepository = new SpecialMedicalCodesMockRepository();

        $useCase = new NationalHealthBillingSaveInteractor(
            $facilityRepository,
            $facilityAdditionsRepository,
            $facilityUserRepository,
            $facilityUserBenefitRecordRepository,
            $facilityUserCareRecordRepository,
            $facilityUserPublicExpenseRecordRepository,
            $facilityUserServiceRecordRepository,
            $nationalHealthBillingRepository,
            $serviceItemCodesRepository,
            $specialMedicalCodesRepository,
        );

        // 施設利用者の対象年月の正しい国保連請求情報を取得する。
        // これと同じものが作れるかどうかが、テストの焦点となる。
        $correct = $nationalHealthBillingRepository->find($facilityId, $facilityUserId, $year, $month);
        $correctServiceResults = $correct->getServiceResults();
        $correctIndividuals = $correct->getIndividuals();

        // 施設利用者の対象年月の正しい国保連請求情報からリクエストデータを作成する。
        $requestData = [];
        foreach ($correctIndividuals as $index => $individual) {
            $burdenLimit = null;
            $specialMedicalCodeId = null;
            if ($individual->isIncompetentResident()) {
                $burdenLimit = $individual->getBurdenLimit();
            } elseif ($individual->isSpecialMedical()) {
                $specialMedicalCodeId = $individual->getSpecialMedicalCode()->getSpecialMedicalCodeId();
            }

            $requestData[] = [
                "burden_limit" => $burdenLimit,
                "date_daily_rate" => $individual->getResultFlag()->getDateDailyRate(),
                "date_daily_rate_one_month_ago" => $individual->getResultFlag()->getDateDailyRateOneMonthAgo(),
                "date_daily_rate_two_month_ago" => $individual->getResultFlag()->getDateDailyRateTwoMonthAgo(),
                "service_count_date" => $individual->getResultFlag()->getServiceCountDate(),
                "service_item_code_id" => $individual->getServiceItemCode()->getServiceItemCodeId(),
                "special_medical_code_id" => $specialMedicalCodeId
            ];
        }

        $outputData = $useCase->handle($facilityId, $facilityUserId, $requestData, $year, $month);

        $this->assertEquals(count($correct->getServiceResults()), count($outputData));

        // 正解と出力データの中身を比較する。
        foreach ($correctServiceResults as $index => $correct) {
            // 出力データのソートは常に同じになる。
            $testTarget = $outputData[$index];
            $testTargetResultFlagObject = $testTarget->getResultFlag();

            // 承認状態は必ず0になる。
            $this->assertEquals($testTarget->getApproval(), 0);
            $this->assertEquals($testTarget->getBenefitRate(), $correct->getBenefitRate());
            $this->assertEquals($testTarget->getBurdenLimit(), $correct->getBurdenLimit());
            $this->assertEquals($testTarget->getCalcKind(), $correct->getCalcKind());
            // $this->assertEquals($testTarget->getClassificationSupportLimitInRange(), $correct->getClassificationSupportLimitInRange());
            $this->assertEquals($testTargetResultFlagObject->getDateDailyRate(), $correct->getResultFlag()->getDateDailyRate());
            $this->assertEquals($testTarget->getInsuranceBenefit(), $correct->getInsuranceBenefit());
            $this->assertEquals($testTarget->getPartPayment(), $correct->getPartPayment());
            $this->assertEquals($testTarget->getPublicBenefitRate(), $correct->getPublicBenefitRate());
            $this->assertEquals($testTarget->getPublicExpenditureUnit(), $correct->getPublicExpenditureUnit());
            $this->assertEquals($testTarget->getPublicPayment(), $correct->getPublicPayment());
            $this->assertEquals($testTarget->getPublicSpendingAmount(), $correct->getPublicSpendingAmount());
            $this->assertEquals($testTarget->getPublicSpendingCount(), $correct->getPublicSpendingCount());
            // $this->assertEquals($testTarget->getPublicSpendingUnitNumber(), $correct->getPublicSpendingUnitNumber());
            $this->assertEquals($testTarget->getPublicUnitPrice(), $correct->getPublicUnitPrice());
            // $this->assertEquals($testTarget->getRank(), $correct->getRank());
            $this->assertEquals($testTarget->getServiceCount(), $correct->getServiceCount());
            $this->assertEquals($testTargetResultFlagObject->getServiceCountDate(), $correct->getResultFlag()->getServiceCountDate());
            $this->assertEquals($testTarget->getServiceUnitAmount(), $correct->getServiceUnitAmount());
            // $this->assertEquals($testTarget->getTargetDate(), $correct->getTargetDate());
            $this->assertEquals($testTarget->getTotalCost(), $correct->getTotalCost());
            $this->assertEquals($testTarget->getUnitNumber(), $correct->getUnitNumber());
            $this->assertEquals($testTarget->getUnitPrice(), $correct->getUnitPrice());
        }
    }
}
