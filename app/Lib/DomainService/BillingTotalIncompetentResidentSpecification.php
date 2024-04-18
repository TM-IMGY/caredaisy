<?php

namespace App\Lib\DomainService;

use App\Lib\DomainService\NationalHealthBillingSpecification;
use App\Lib\Entity\Facility;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\FacilityUserBenefitRecord;
use App\Lib\Entity\FacilityUserPublicExpenseRecord;
use App\Lib\Entity\FacilityUserServiceRecord;
use App\Lib\Entity\ServiceItemCode;
use App\Lib\Entity\SpecialMedicalCode;
use App\Lib\Entity\ServiceResult;
use App\Lib\Factory\ResultFlagFactory;
use Carbon\Carbon;

/**
 * 国保連請求の合計計算の特定入所者介護サービスの仕様クラス。
 */
class BillingTotalIncompetentResidentSpecification
{
    /**
     * サービス実績(特定入所者サービス)を作成して返す。対象は種類59のみ。
     */
    public static function calculate(
        Facility $facility,
        FacilityUser $facilityUser,
        FacilityUserBenefitRecord $facilityUserBenefitRecord,
        ?FacilityUserPublicExpenseRecord $facilityUserPublicExpenseRecord,
        FacilityUserServiceRecord $facilityUserServiceRecord,
        ServiceItemCode $serviceItemCode,
        array $serviceResults,
        int $year,
        int $month
    ): ServiceResult {
        // 給付率を取得する。
        // 給付率がない場合は0として扱う。
        $benefitRate = $facilityUserBenefitRecord->hasRecord() ? $facilityUserBenefitRecord->getLatest()->getBenefitRate() : 0;
        // 有効桁を取得する。
        $digits = NationalHealthBillingSpecification::SIGNIFICANT_DIGITS;
        // 回数／日数
        $resultFlagObject = (new ResultFlagFactory())->generateInitial();
        $serviceCountDate = $resultFlagObject->getServiceCountDate();
        // 施設利用者のサービス種類は59固定になる(提供を受けているサービス種類自体は55)。
        $serviceTypeCode = '59';
        // システム時刻を取得する。
        $timestamp = Carbon::now()->format('Y/m/d H:i:s');
        // 単位数
        $unitNumber = 0;
        // 単位数単価
        $unitPrice = ServiceResult::UNIT_PRICE_INCOMPETENT_RESIDENT;

        // 計算対象のサービス実績を取得する。
        $calcTargets = array_filter($serviceResults, function ($result) {
            return $result->isIncompetentResident() && ($result->isIndividual() || $result->isFacilitySpecial());
        });
        // インデックスをリセットする。
        $calcTargets = array_values($calcTargets);

        // 単位数合計
        $serviceUnitAmount = 0;
        // 費用
        $totalCost = 0;
        // 保険給付額
        $insuranceBenefit = 0;
        // 利用者負担額
        $partPayment = 0;
        foreach ($calcTargets as $target) {
            $serviceUnitAmount += $target->getServiceUnitAmount();
            $totalCost += $target->getTotalCost();
            $insuranceBenefit += $target->getInsuranceBenefit();
            $partPayment += $target->getPartPayment();
        }

        // 区分支給限度基準内単位数
        $classificationSupportLimitInRange = $serviceUnitAmount;

        $publicBenefitRate = null;
        $publicExpenditureUnit = null;
        $publicPayment = null;
        $publicSpendingAmount = null;
        $publicSpendingCount = null;
        $publicSpendingUnitNumber = null;
        $publicUnitPrice = null;
        // 施設利用者に特定入所者サービス(種類59)で適用可能な公費がある場合。
        if ($facilityUserPublicExpenseRecord !== null && $facilityUserPublicExpenseRecord->getApplicablePublicExpense()->getPublicExpense()->isAvailable($serviceTypeCode)) {
            $applicablePublicExpense = $facilityUserPublicExpenseRecord->getApplicablePublicExpense();

            // 公費対象単位数 = 単位数
            $publicSpendingUnitNumber = $unitNumber;

            // 公費分回数等 = 回数／日数
            $publicSpendingCount = $serviceCountDate;

            // 公費単位数合計
            $publicExpenditureUnit = 0;
            // 公費請求額
            $publicSpendingAmount = 0;
            // 公費利用者負担額
            $publicPayment = 0;
            foreach ($calcTargets as $target) {
                $publicExpenditureUnit += $target->getPublicExpenditureUnit();
                $publicSpendingAmount += $target->getPublicSpendingAmount();
                $publicPayment += $target->getPublicPayment();
            }

            // 公費給付率 = 公費マスタ給付率
            $publicBenefitRate = $applicablePublicExpense->getBenefitRate();

            // 公費単位数単価 = 単位数単価
            $publicUnitPrice = $unitPrice;
        }

        // サービス実績を作成する。
        $serviceResult = new ServiceResult(
            ServiceResult::NOT_APPROVAL,
            $facilityUserBenefitRecord->hasRecord() ? $facilityUserBenefitRecord->getLatest()->getBenefitRate() : 0,
            // burden_limit
            null,
            ServiceResult::CALC_KIND_TOTAL,
            $classificationSupportLimitInRange,
            // document_create_date
            $timestamp,
            $facility->getFacilityId(),
            // facility_name_kanji
            null,
            // facility_number
            0,
            $facilityUser->getFacilityUserId(),
            $insuranceBenefit,
            $partPayment,
            $publicBenefitRate,
            $publicExpenditureUnit,
            $publicPayment,
            $publicSpendingAmount,
            $publicSpendingCount,
            $publicSpendingUnitNumber,
            $publicUnitPrice,
            $serviceItemCode->getRank(),
            $resultFlagObject,
            ServiceResult::RESULT_KIND_INCOMPETENT_RESIDENT,
            $serviceCountDate,
            // service_end_time
            9999,
            $serviceItemCode,
            $serviceItemCode->getServiceItemCodeId(),
            // service_result_id
            null,
            // service_start_time
            9999,
            $serviceUnitAmount,
            // service_use_date
            $timestamp,
            // special_medical_code
            null,
            // target_date
            "${year}-${month}-1",
            $totalCost,
            $unitNumber,
            $unitPrice
        );

        return $serviceResult;
    }
}
