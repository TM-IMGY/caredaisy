<?php

namespace Tests\Unit\UseCases;

use App\Lib\ApplicationBusinessRules\UseCases\Interactors\GetFormInteractor;
use App\Lib\MockRepository\DataSets\FacilityDataSets;
use App\Lib\MockRepository\DataSets\FacilityUserDataSets;
use App\Lib\MockRepository\InjuriesSicknessMockRepository;
use App\Lib\MockRepository\FacilityUserServiceRecordMockRepository;
use App\Lib\MockRepository\NationalHealthBillingMockRepository;
use PHPUnit\Framework\TestCase;

class GetFormUseCaseTest extends TestCase
{
    /**
     * データプロバイダ
     * @return array
     */
    public function getFormProvider(): array
    {
        $year = 2022;
        $month = 8;

        return [
            // 国保連請求あり 介護医療院である 公費あり
            'hospital' => [
                FacilityDataSets::CASE_55_PATTERN_1_ID,
                FacilityUserDataSets::CASE_55_PATTERN_1_ID,
                $year,
                $month
            ],
            // 国保連請求なし
            'not_national_health' => [
                FacilityUserDataSets::CASE_32_PATTERN_2_ID,
                FacilityDataSets::CASE_32_PATTERN_2_ID,
                $year,
                $month
            ],
            // 国保連請求あり 介護医療院でない 公費なし
            'not_public_expence_not_hospital' => [
                FacilityUserDataSets::CASE_32_PATTERN_3_ID,
                FacilityDataSets::CASE_32_PATTERN_3_ID,
                $year,
                $month
            ]
        ];
    }

    /**
     * @dataProvider getFormProvider
     * @param int $facilityId 事業所ID
     * @param int $facilityUserId 施設利用者ID
     * @param int $yaer 対象年
     * @param int $month 対象月
     * @return void
     */
    public function testExample(int $facilityId, int $facilityUserId, int $year, int $month): void
    {
        $facilityUserServiceRecordMockRepository = new FacilityUserServiceRecordMockRepository();
        $nationalHealthBillingMockRepository = new NationalHealthBillingMockRepository();
        $injuriesSicknessMockRepository = new InjuriesSicknessMockRepository();

        $useCase = new GetFormInteractor($facilityUserServiceRecordMockRepository, $nationalHealthBillingMockRepository, $injuriesSicknessMockRepository);
        $output = $useCase->handle($facilityId, $facilityUserId, $year, $month);
        $data = $output->getData();

        // サービスの記録取得
        $facilityUserServiceRecord = $facilityUserServiceRecordMockRepository->find($facilityUserId, $year, $month);
        // 国保連請求情報取得
        $nationalHealthBilling = $nationalHealthBillingMockRepository->find($facilityId, $facilityUserId, $year, $month);
        // 傷病名取得
        $injuriesSickness = $injuriesSicknessMockRepository->find($facilityUserId, $year, $month);

        if ($nationalHealthBilling->hasDetailService()) {
            // 給付費明細欄
            foreach ($data['details'] as $index => $record) {
                $detailCorrect = $nationalHealthBilling->getServiceDetails()[$index];
                $this->assertEquals($record['service_count_date'], $detailCorrect->getResultFlag()->getServiceCountDate());
                $this->assertEquals($record['service_item_code_id'], $detailCorrect->getServiceItemCode()->getServiceItemCodeId());
                $this->assertEquals($record['service_unit_amount'], $detailCorrect->getServiceUnitAmount());
                $this->assertEquals($record['unit_number'], $detailCorrect->getUnitNumber());
                $this->assertEquals($record['public_expenditure_unit'], $detailCorrect->getPublicExpenditureUnit());
                $this->assertEquals($record['public_spending_count'], $detailCorrect->getPublicSpendingCount());
                $this->assertEquals($record['public_spending_unit_number'], $detailCorrect->getPublicSpendingUnitNumber());
                $this->assertEquals($record['service_item_name'], $detailCorrect->getServiceItemCode()->getServiceItemName());
                $this->assertEquals($record['service_code'], $detailCorrect->getServiceItemCode()->getServiceCode());
            }

            // 請求集計欄(保険分、公費分)
            $totalCorrect = $nationalHealthBilling->getServiceTotal();
            $this->assertEquals($data['total']['benefit_rate'], $totalCorrect->getBenefitRate());
            $this->assertEquals($data['total']['insurance_benefit'], $totalCorrect->getInsuranceBenefit());
            $this->assertEquals($data['total']['part_payment'], $totalCorrect->getPartPayment());
            $this->assertEquals($data['total']['service_unit_amount'], $totalCorrect->getServiceUnitAmount());
            $this->assertEquals($data['total']['unit_price'], $totalCorrect->getUnitPrice());
            $this->assertEquals($data['total']['public_benefit_rate'], $totalCorrect->getPublicBenefitRate());
            $this->assertEquals($data['total']['public_expenditure_unit'], $totalCorrect->getPublicExpenditureUnit());
            $this->assertEquals($data['total']['public_payment'], $totalCorrect->getPublicPayment());
            $this->assertEquals($data['total']['public_spending_amount'], $totalCorrect->getPublicSpendingAmount());
        } else {
            $this->assertEquals($data['details'], $nationalHealthBilling->getServiceDetails());
            $this->assertEquals($data['total'], $nationalHealthBilling->getServiceTotal());
        }

        // サービス種別コードID
        $correctServiceTypeCodeId = $facilityUserServiceRecord->getLatest()->getServiceTypeCode()->getServiceTypeCodeId();
        $this->assertEquals($data['service_type_code_id'], $correctServiceTypeCodeId);

        if ($facilityUserServiceRecord->getLatest()->isHospital()) {
            // 特別診療費
            foreach ($data['special_medicals'] as $index => $record) {
                $specialMedicalCorrect = $nationalHealthBilling->getSpecialMedicalIndividuals($injuriesSickness)[$index];
                $this->assertEquals($record['special_medical_code_id'], $specialMedicalCorrect->getSpecialMedicalCode()->getSpecialMedicalCodeId());
                $this->assertEquals($record['name'], $injuriesSickness->getName($specialMedicalCorrect->getSpecialMedicalCode()->getSpecialMedicalCodeId()));
                $this->assertEquals($record['identification_num'], $specialMedicalCorrect->getSpecialMedicalCode()->getIdentificationNum());
                $this->assertEquals($record['special_medical_name'], $specialMedicalCorrect->getSpecialMedicalCode()->getSpecialMedicalName());
                $this->assertEquals($record['unit_number'], $specialMedicalCorrect->getUnitNumber());
                $this->assertEquals($record['service_count_date'], $specialMedicalCorrect->getResultFlag()->getServiceCountDate());
                $this->assertEquals($record['service_unit_amount'], $specialMedicalCorrect->getServiceUnitAmount());
                $this->assertEquals($record['public_spending_count'], $specialMedicalCorrect->getPublicSpendingCount());
                $this->assertEquals($record['public_expenditure_unit'], $specialMedicalCorrect->getPublicExpenditureUnit());
                $this->assertEquals($record['detail_id'], $injuriesSickness->findDetail($specialMedicalCorrect->getSpecialMedicalCode()->getSpecialMedicalCodeId())->getDetailId());
            }

            // 請求集計欄(保険分特定治療・特別診療費、公費分特定治療・特別診療費)
            $totalSpecialMedicalCorrect = $nationalHealthBilling->getSpecialMedicalTotal();
            $this->assertEquals($data['total_special_medical']['service_unit_amount'], $totalSpecialMedicalCorrect->getServiceUnitAmount());
            $this->assertEquals($data['total_special_medical']['benefit_rate'], $totalSpecialMedicalCorrect->getBenefitRate());
            $this->assertEquals($data['total_special_medical']['insurance_benefit'], $totalSpecialMedicalCorrect->getInsuranceBenefit());
            $this->assertEquals($data['total_special_medical']['part_payment'], $totalSpecialMedicalCorrect->getPartPayment());
            $this->assertEquals($data['total_special_medical']['public_spending_unit_number'], $totalSpecialMedicalCorrect->getPublicSpendingUnitNumber());
            $this->assertEquals($data['total_special_medical']['public_benefit_rate'], $totalSpecialMedicalCorrect->getPublicBenefitRate());
            $this->assertEquals($data['total_special_medical']['public_spending_amount'], $totalSpecialMedicalCorrect->getPublicSpendingAmount());
            $this->assertEquals($data['total_special_medical']['public_payment'], $totalSpecialMedicalCorrect->getPublicPayment());
            $this->assertEquals($data['total_special_medical']['public_expenditure_unit'], $totalSpecialMedicalCorrect->getPublicExpenditureUnit());

            // 特定入所者介護サービス費
            foreach ($data['incompetent_residents'] as $index => $record) {
                $incompetentResidentCorrect = $nationalHealthBilling->getIncompetentResidentIndividuals()[$index];
                $serviceItemCode = $incompetentResidentCorrect->getServiceItemCode();
                $this->assertEquals($record['service_item_name'], $serviceItemCode->getServiceItemName());
                $this->assertEquals($record['service_code'], $serviceItemCode->getServiceCode());
                $this->assertEquals($record['unit_number'], $incompetentResidentCorrect->getUnitNumber());
                $this->assertEquals($record['burden_limit'], $incompetentResidentCorrect->getBurdenLimit());
                $this->assertEquals($record['service_count_date'], $incompetentResidentCorrect->getResultFlag()->getServiceCountDate());
                $this->assertEquals($record['total_cost'], $incompetentResidentCorrect->getTotalCost());
                $this->assertEquals($record['insurance_benefit'], $incompetentResidentCorrect->getInsuranceBenefit());
                $this->assertEquals($record['public_spending_count'], $incompetentResidentCorrect->getPublicSpendingCount());
                $this->assertEquals($record['public_spending_amount'], $incompetentResidentCorrect->getPublicSpendingAmount());
                $this->assertEquals($record['part_payment'], $incompetentResidentCorrect->getPartPayment());
            }

            // 特定入所者介護サービス費 合計
            $totalIncompetentResidentCorrect = $nationalHealthBilling->getIncompetentResidentTotal();
            $this->assertEquals($data['total_incompetent_resident']['total_cost'], $totalIncompetentResidentCorrect->getTotalCost());
            $this->assertEquals($data['total_incompetent_resident']['public_spending_amount'], $totalIncompetentResidentCorrect->getPublicSpendingAmount());
            $this->assertEquals($data['total_incompetent_resident']['part_payment'], $totalIncompetentResidentCorrect->getPartPayment());
            $this->assertEquals($data['total_incompetent_resident']['insurance_benefit'], $totalIncompetentResidentCorrect->getInsuranceBenefit());
            $this->assertEquals($data['total_incompetent_resident']['public_payment'], $totalIncompetentResidentCorrect->getPublicPayment());
        } else {
            $this->assertEquals($data['special_medicals'], $nationalHealthBilling->getSpecialMedicalIndividuals($injuriesSickness));
            $this->assertEquals($data['total_special_medical'], $nationalHealthBilling->getSpecialMedicalTotal());
            $this->assertEquals($data['incompetent_residents'], $nationalHealthBilling->getIncompetentResidentIndividuals());
            $this->assertEquals($data['total_incompetent_resident'], $nationalHealthBilling->getIncompetentResidentTotal());
        }
    }
}
