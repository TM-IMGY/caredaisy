<?php

namespace App\Lib\ApplicationBusinessRules\UseCases\Interactors;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserServiceRecordRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\InjuriesSicknessRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\NationalHealthBillingRepositoryInterface;
use App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries\GetFormInputBoundary;
use App\Lib\ApplicationBusinessRules\OutputData\GetFormDetailOutputData;
use App\Lib\ApplicationBusinessRules\OutputData\GetFormIncompetentResidentOutputData;
use App\Lib\ApplicationBusinessRules\OutputData\GetFormOutputData;
use App\Lib\ApplicationBusinessRules\OutputData\GetFormSpecialMedicalOutputData;
use App\Lib\ApplicationBusinessRules\OutputData\GetFormTotalIncompetentResidentOutputData;
use App\Lib\ApplicationBusinessRules\OutputData\GetFormTotalOutputData;
use App\Lib\ApplicationBusinessRules\OutputData\GetFormTotalSpecialMedicalOutputData;
use Exception;

/**
 * 国保連請求の様式データの取得のユースケースの実装。
 */
class GetFormInteractor implements GetFormInputBoundary
{
    private FacilityUserServiceRecordRepositoryInterface $facilityUserServiceRecordRepository;

    private NationalHealthBillingRepositoryInterface $nationalHealthBillingRepository;

    private InjuriesSicknessRepositoryInterface $injuriesSicknessRepository;

    /**
     * コンストラクタ
     * @param FacilityUserServiceRecordRepositoryInterface $facilityUserServiceRecordRepository
     * @param NationalHealthBillingRepositoryInterface $nationalHealthBillingRepository
     * @param InjuriesSicknessRepositoryInterface $injuriesSicknessRepository
     */
    public function __construct(
        FacilityUserServiceRecordRepositoryInterface $facilityUserServiceRecordRepository,
        NationalHealthBillingRepositoryInterface $nationalHealthBillingRepository,
        InjuriesSicknessRepositoryInterface $injuriesSicknessRepository
    ) {
        $this->facilityUserServiceRecordRepository = $facilityUserServiceRecordRepository;
        $this->nationalHealthBillingRepository = $nationalHealthBillingRepository;
        $this->injuriesSicknessRepository = $injuriesSicknessRepository;
    }

    /**
     * @param int $facilityId 事業所ID
     * @param int $facilityUserId 施設利用者ID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return GetFormOutputData
     */
    public function handle(int $facilityId, int $facilityUserId, int $year, int $month): GetFormOutputData
    {
        // サービスの記録。
        $facilityUserServiceRecord = null;
        // 傷病。
        $injurySickness = null;
        // 国保連請求情報。
        $nationalHealthBilling = null;

        try {
            $facilityUserServiceRecord = $this->facilityUserServiceRecordRepository->find($facilityUserId, $year, $month);
            // サービスの記録がない場合は例外を投げる。
            if (!$facilityUserServiceRecord->hasRecord()) {
                throw new Exception('');
            }

            // 国保連請求情報を取得する。
            $nationalHealthBilling = $this->nationalHealthBillingRepository->find($facilityId, $facilityUserId, $year, $month);

            // 介護医療院の場合傷病名を取得する
            if ($facilityUserServiceRecord->getLatest()->isHospital()) {
                $injurySickness = $this->injuriesSicknessRepository->find($facilityUserId, $year, $month);
            }
        } catch (Exception $e) {
            // TODO: 例外処理時の設計からする。
            throw $e;
        }

        // サービスの記録から最新かつ利用中のサービス種類コードのIDを取得する。
        $latestServiceTypeCodeId = $facilityUserServiceRecord->getLatest()->getServiceTypeCode()->getServiceTypeCodeId();

        // 国保連請求からサービスの明細を取得する。
        // ない場合は出力データを返す。
        $detailServices = $nationalHealthBilling->hasDetailService() ? $nationalHealthBilling->getServiceDetails() : null;
        if ($detailServices === null) {
            return new GetFormOutputData([], null, $latestServiceTypeCodeId, [], null, [], null);
        }

        // 国保連請求からサービスの合計を取得する。
        // ない場合は、出力データを返す。
        $totalService = $nationalHealthBilling->hasTotalService() ? $nationalHealthBilling->getServiceTotal() : null;
        if ($totalService === null) {
            return new GetFormOutputData([], null, $latestServiceTypeCodeId, [], null, [], null);
        }

        // 施設利用者のサービスの記録が介護医療院である場合、特別診療費と特定入所者サービスの出力データを作成する。
        $outputDataSpecialMedicals = [];
        $outputDataSpecialMedicalTotal = null;
        $outputDataIncompetentResidents = [];
        $outputDataIncompetentResidentTotal = null;
        if ($facilityUserServiceRecord->getLatest()->isHospital()) {
            // 特別診療費を取得する。
            $specialMedicals = $nationalHealthBilling->getSpecialMedicalIndividuals($injurySickness);
            $totalSpecialMedical = $nationalHealthBilling->getSpecialMedicalTotal();
            // 特別診療費の出力データを作成する。
            foreach ($specialMedicals as $index => $specialMedical) {
                $specialMedicalCodeId = $specialMedical->getSpecialMedicalCode()->getSpecialMedicalCodeId();
                $outputDataSpecialMedicals[] = new GetFormSpecialMedicalOutputData(
                    $injurySickness->findDetail($specialMedicalCodeId)->getDetailId(),
                    $specialMedical->getSpecialMedicalCode()->getIdentificationNum(),
                    $injurySickness->getName($specialMedicalCodeId),
                    $specialMedical->getResultFlag()->getServiceCountDate(),
                    $specialMedicalCodeId,
                    $specialMedical->getSpecialMedicalCode()->getSpecialMedicalName(),
                    $specialMedical->getServiceUnitAmount(),
                    $specialMedical->getUnitNumber(),
                    $specialMedical->getPublicExpenditureUnit(),
                    $specialMedical->getPublicSpendingCount()
                );
            }
            if ($totalSpecialMedical !== null) {
                $outputDataSpecialMedicalTotal = new GetFormTotalSpecialMedicalOutputData(
                    $totalSpecialMedical->getBenefitRate(),
                    $totalSpecialMedical->getInsuranceBenefit(),
                    $totalSpecialMedical->getPartPayment(),
                    $totalSpecialMedical->getServiceUnitAmount(),
                    $totalSpecialMedical->getPublicBenefitRate(),
                    $totalSpecialMedical->getPublicExpenditureUnit(),
                    $totalSpecialMedical->getPublicPayment(),
                    $totalSpecialMedical->getPublicSpendingAmount(),
                    $totalSpecialMedical->getPublicSpendingUnitNumber()
                );
            }

            // 特定入所者介護サービス費を取得する。
            $incompetentResidents = $nationalHealthBilling->getIncompetentResidentIndividuals();
            $totalIncompetentResident = $nationalHealthBilling->getIncompetentResidentTotal();

            // 特定入所者介護サービスの出力データを作成する。
            foreach ($incompetentResidents as $index => $incompetentResident) {
                $serviceItemCode = $incompetentResident->getServiceItemCode();
                $outputDataIncompetentResidents[] = new GetFormIncompetentResidentOutputData(
                    $incompetentResident->getBurdenLimit(),
                    $incompetentResident->getInsuranceBenefit(),
                    $incompetentResident->getPartPayment(),
                    $serviceItemCode->getServiceCode(),
                    $incompetentResident->getResultFlag()->getServiceCountDate(),
                    $serviceItemCode->getServiceItemName(),
                    $incompetentResident->getTotalCost(),
                    $incompetentResident->getUnitNumber(),
                    $incompetentResident->getPublicSpendingAmount(),
                    $incompetentResident->getPublicSpendingCount()
                );
            }
            if ($totalIncompetentResident !== null) {
                $outputDataIncompetentResidentTotal = new GetFormTotalIncompetentResidentOutputData(
                    $totalIncompetentResident->getInsuranceBenefit(),
                    $totalIncompetentResident->getPartPayment(),
                    $totalIncompetentResident->getTotalCost(),
                    $totalIncompetentResident->getPublicPayment(),
                    $totalIncompetentResident->getPublicSpendingAmount()
                );
            }
        }

        // 出力データを作成する。
        $detailOutputData = [];
        foreach ($detailServices as $index => $detailService) {
            $serviceItemCode = $detailService->getServiceItemCode();
            $detailOutputData[] = new GetFormDetailOutputData(
                $serviceItemCode->getServiceCode(),
                $detailService->getResultFlag()->getServiceCountDate(),
                $serviceItemCode->getServiceItemCodeId(),
                $serviceItemCode->getServiceItemName(),
                $detailService->getServiceUnitAmount(),
                $detailService->getUnitNumber(),
                $detailService->getPublicExpenditureUnit(),
                $detailService->getPublicSpendingCount(),
                $detailService->getPublicSpendingUnitNumber()
            );
        }

        $totalOutputData = new GetFormTotalOutputData(
            $totalService->getBenefitRate(),
            $totalService->getInsuranceBenefit(),
            $totalService->getPartPayment(),
            $totalService->getServiceUnitAmount(),
            $totalService->getUnitPrice(),
            $totalService->getPublicBenefitRate(),
            $totalService->getPublicExpenditureUnit(),
            $totalService->getPublicPayment(),
            $totalService->getPublicSpendingAmount()
        );

        return new GetFormOutputData(
            $detailOutputData,
            $totalOutputData,
            $latestServiceTypeCodeId,
            $outputDataSpecialMedicals,
            $outputDataSpecialMedicalTotal,
            $outputDataIncompetentResidents,
            $outputDataIncompetentResidentTotal
        );
    }
}
