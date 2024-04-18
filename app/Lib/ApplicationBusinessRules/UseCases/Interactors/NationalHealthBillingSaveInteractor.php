<?php

namespace App\Lib\ApplicationBusinessRules\UseCases\Interactors;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityAdditionsRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserBenefitRecordRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserCareRecordRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserPublicExpenseRecordRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserServiceRecordRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\NationalHealthBillingRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\ServiceItemCodesRepositoryInterface;
use App\Lib\ApplicationBusinessRules\DataAccessInterface\SpecialMedicalCodesRepositoryInterface;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\FacilityNotFoundException;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\InvalidFacilityUserBenefitRecordException;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\InvalidFacilityUserCareRecordException;
use App\Lib\ApplicationBusinessRules\NationalHealthBilling\Exceptions\InvalidFacilityUserServiceRecordException;
use App\Lib\DomainService\BillingTotalIncompetentResidentSpecification;
use App\Lib\DomainService\BillingTotalServiceSpecification;
use App\Lib\DomainService\BillingTotalSpecialMedicalSpecification;
use App\Lib\DomainService\NationalHealthBillingFacilitySpecification;
use App\Lib\DomainService\NationalHealthBillingIndividualSpecification;
use App\Lib\DomainService\NationalHealthBillingSpecailSpecification;
use App\Lib\DomainService\NationalHealthBillingSubTotalSpecification;
use App\Lib\Entity\ServiceItemCode;
use App\Lib\Factory\ResultFlagFactory;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Exception;

/**
 * 実績登録タブの保存ボタンのユースケース。
 */
class NationalHealthBillingSaveInteractor
{
    private FacilityRepositoryInterface $facilityRepository;

    private FacilityAdditionsRepositoryInterface $facilityAdditionsRepository;

    private FacilityUserRepositoryInterface $facilityUserRepository;

    private FacilityUserBenefitRecordRepositoryInterface $facilityUserBenefitRecordRepository;

    private FacilityUserCareRecordRepositoryInterface $facilityUserCareRecordRepository;

    private FacilityUserPublicExpenseRecordRepositoryInterface $facilityUserPublicExpenseRecordRepository;

    private FacilityUserServiceRecordRepositoryInterface $facilityUserServiceRecordRepository;

    private NationalHealthBillingRepositoryInterface $nationalHealthBillingRepository;

    private ServiceItemCodesRepositoryInterface $serviceItemCodesRepository;

    private SpecialMedicalCodesRepositoryInterface $specialMedicalCodesRepository;

    public function __construct(
        FacilityRepositoryInterface $facilityRepository,
        FacilityAdditionsRepositoryInterface $facilityAdditionsRepository,
        FacilityUserRepositoryInterface $facilityUserRepository,
        FacilityUserBenefitRecordRepositoryInterface $facilityUserBenefitRecordRepository,
        FacilityUserCareRecordRepositoryInterface $facilityUserCareRecordRepository,
        FacilityUserPublicExpenseRecordRepositoryInterface $facilityUserPublicExpenseRecordRepository,
        FacilityUserServiceRecordRepositoryInterface $facilityUserServiceRecordRepository,
        NationalHealthBillingRepositoryInterface $nationalHealthBillingRepository,
        ServiceItemCodesRepositoryInterface $serviceItemCodesRepository,
        SpecialMedicalCodesRepositoryInterface $specialMedicalCodesRepository
    ) {
        $this->facilityRepository = $facilityRepository;
        $this->facilityAdditionsRepository = $facilityAdditionsRepository;
        $this->facilityUserRepository = $facilityUserRepository;
        $this->facilityUserBenefitRecordRepository = $facilityUserBenefitRecordRepository;
        $this->facilityUserCareRecordRepository = $facilityUserCareRecordRepository;
        $this->facilityUserPublicExpenseRecordRepository = $facilityUserPublicExpenseRecordRepository;
        $this->facilityUserServiceRecordRepository = $facilityUserServiceRecordRepository;
        $this->nationalHealthBillingRepository = $nationalHealthBillingRepository;
        $this->serviceItemCodesRepository = $serviceItemCodesRepository;
        $this->specialMedicalCodesRepository = $specialMedicalCodesRepository;
    }

    /**
     * 実績登録タブの保存ボタンのユースケースを実行する。
     * 事業所のIDは、どの事業所の施設利用者として請求を行うか判断するために必要になる。
     * @return ServiceResult[]
     */
    public function handle(int $facilityId, int $facilityUserId, array $requests, int $year, int $month): array
    {
        $facility = null;
        $facilityAdditions = null;
        $facilityUser = null;
        $facilityUserBenefitRecord = null;
        $facilityUserCareRecord = null;
        $facilityUserServiceRecord = null;
        $facilityUserPublicExpenseRecord = null;
        $serviceItemCodes = null;
        $specialMedicalCodes = null;

        try {
            $facility = $this->facilityRepository->find($facilityId);

            if ($facility === null) {
                throw new FacilityNotFoundException('', -8);
            }

            $facilityUser = $this->facilityUserRepository->find($facilityUserId, $year, $month);

            // 施設利用者の給付率の履歴を取得する。
            $facilityUserBenefitRecord = $this->facilityUserBenefitRecordRepository->find(
                $facilityUserId,
                $year,
                $month
            );
            if (!($facilityUserBenefitRecord->hasRecord() || $facilityUser->isUnder65())) {
                throw new InvalidFacilityUserBenefitRecordException('', -9);
            }

            // 施設利用者の介護情報の履歴を取得する。
            $facilityUserCareRecord = $this->facilityUserCareRecordRepository->find($facilityUserId, $year, $month);
            if (!$facilityUserCareRecord->hasRecord()) {
                throw new InvalidFacilityUserCareRecordException('', -10);
            }

            // 施設利用者のサービスの履歴を取得する。
            $facilityUserServiceRecord = $this->facilityUserServiceRecordRepository->find(
                $facilityUserId,
                $year,
                $month
            );
            if (!$facilityUserServiceRecord->hasRecord()) {
                throw new InvalidFacilityUserServiceRecordException('', -4);
            }

            $facilityAdditions = $this->facilityAdditionsRepository->getByFacilityId(
                $facilityId,
                $facilityUserServiceRecord->getLatest()->getServiceTypeCode()->getServiceTypeCodeId(),
                $year,
                $month
            );

            $facilityUserPublicExpenseRecord = $this->facilityUserPublicExpenseRecordRepository->find(
                $facility,
                $facilityUserId,
                $year,
                $month
            );

            $serviceItemCodeIds = array_merge(
                array_column($requests, 'service_item_code_id'),
                $facilityAdditions->getServiceItemCodeIds(),
                // 小計計算と合計計算のサービス項目コードID。
                [ServiceItemCode::SUBTOTAL_ID, ServiceItemCode::TOTAL_ID]
            );

            $serviceItemCodes = $this->serviceItemCodesRepository->get(
                $serviceItemCodeIds,
                $year,
                $month
            );

            $specialMedicalCodes = $this->specialMedicalCodesRepository->get(
                // TODO: 施設利用者によっては特別診療費コードIDがnullの場合もある。適切にフィルタリングしたい。
                array_column($requests, 'special_medical_code_id'),
                $year,
                $month
            );
        } catch (Exception $e) {
            throw $e;
        }

        // サービス実績を作成する。
        $serviceResults = [];
        if (count($requests) > 0) {
            // サービス実績(個別)を作成する。
            for ($i = 0, $cnt = count($requests); $i < $cnt; $i++) {
                $serviceItemCode = $serviceItemCodes->find($requests[$i]['service_item_code_id']);
                $resultFlag = new ResultFlag(
                    $requests[$i]['date_daily_rate'],
                    $requests[$i]['date_daily_rate_one_month_ago'],
                    $requests[$i]['date_daily_rate_two_month_ago'],
                    $requests[$i]['service_count_date']
                );

                $burdenLimit = null;
                if ($serviceItemCode->isIncompetentResident()) {
                    $burdenLimit = $requests[$i]['burden_limit'];
                }

                $specialMedicalCode = null;
                if ($serviceItemCode->isSpecialMedical()) {
                    $specialMedicalCode = $specialMedicalCodes->find($requests[$i]['special_medical_code_id']);
                }

                $serviceResults[] = NationalHealthBillingIndividualSpecification::calculate(
                    $burdenLimit,
                    $facility,
                    $facilityUser,
                    $facilityUserBenefitRecord,
                    $facilityUserPublicExpenseRecord,
                    $facilityUserServiceRecord,
                    $resultFlag,
                    $serviceItemCode,
                    $specialMedicalCode,
                    $year,
                    $month
                );
            }

            $resultFlagCopy = $serviceResults[0]->getResultFlag();
            $serviceCountCopy = $serviceResults[0]->getServiceCount();

            // サービス実績(事業所)をランク昇順で作成する。
            foreach ($facilityAdditions->getOnlyBasic() as $facilityAddition) {
                $serviceItemCodeId = $facilityAddition->getServiceItemCode()->getServiceItemCodeId();
                $serviceItemCode = $serviceItemCodes->find($serviceItemCodeId);
                $serviceResults[] = NationalHealthBillingFacilitySpecification::calculate(
                    $facility,
                    $facilityUser,
                    $facilityUserBenefitRecord,
                    $facilityUserPublicExpenseRecord,
                    $facilityUserServiceRecord,
                    $resultFlagCopy,
                    $serviceCountCopy,
                    $serviceItemCode,
                    $year,
                    $month
                );
            }

            $serviceResults[] = NationalHealthBillingSubTotalSpecification::calculate(
                $facility,
                $facilityUser,
                $facilityUserBenefitRecord,
                $facilityUserPublicExpenseRecord,
                $facilityUserServiceRecord,
                $resultFlagCopy,
                $serviceCountCopy,
                $serviceItemCodes->findSubTotal(),
                $serviceResults,
                $year,
                $month
            );

            // サービス実績の事業所の特殊を作成する。
            foreach ($facilityAdditions->getOnlySpecial() as $facilityAddition) {
                $serviceItemCodeId = $facilityAddition->getServiceItemCode()->getServiceItemCodeId();
                $serviceItemCode = $serviceItemCodes->find($serviceItemCodeId);
                $serviceResults[] = NationalHealthBillingFacilitySpecification::calculate(
                    $facility,
                    $facilityUser,
                    $facilityUserBenefitRecord,
                    $facilityUserPublicExpenseRecord,
                    $facilityUserServiceRecord,
                    $resultFlagCopy,
                    $serviceCountCopy,
                    $serviceItemCode,
                    $year,
                    $month
                );
            }
            // 事業所の特殊は作成の後で再計算をする必要がある。
            $serviceResults = NationalHealthBillingSpecailSpecification::reCalculate(
                $facilityUserPublicExpenseRecord,
                $facilityUserServiceRecord,
                $serviceResults
            );

            $serviceResults[] = BillingTotalServiceSpecification::calculate(
                $facility,
                $facilityUser,
                $facilityUserBenefitRecord,
                $facilityUserPublicExpenseRecord,
                $facilityUserServiceRecord,
                $serviceItemCodes->findTotal(),
                $serviceResults,
                $year,
                $month
            );

            // サービス実績の合計(特別診療費)を作成する。
            if ($serviceItemCodes->hasSpecialMedical()) {
                $serviceResults[] = BillingTotalSpecialMedicalSpecification::calculate(
                    $facility,
                    $facilityUser,
                    $facilityUserBenefitRecord,
                    $facilityUserPublicExpenseRecord,
                    $facilityUserServiceRecord,
                    $serviceItemCodes->findTotal(),
                    $serviceResults,
                    $year,
                    $month
                );
            }

            // サービス実績の合計(特定入所者サービス)を作成する。
            if ($serviceItemCodes->hasIncompetentResident()) {
                $serviceResults[] = BillingTotalIncompetentResidentSpecification::calculate(
                    $facility,
                    $facilityUser,
                    $facilityUserBenefitRecord,
                    $facilityUserPublicExpenseRecord,
                    $facilityUserServiceRecord,
                    $serviceItemCodes->findTotal(),
                    $serviceResults,
                    $year,
                    $month
                );
            }
        }

        // 請求情報を保存する。
        try {
            $this->nationalHealthBillingRepository->save($facilityUserId, $serviceResults, $year, $month);
        } catch (Exception $e) {
            throw $e;
        }

        return $serviceResults;
    }
}
