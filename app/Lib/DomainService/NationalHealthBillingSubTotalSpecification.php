<?php

namespace App\Lib\DomainService;

use App\Lib\DomainService\NationalHealthBillingSpecification;
use App\Lib\Entity\Facility;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\FacilityUserBenefitRecord;
use App\Lib\Entity\FacilityUserPublicExpenseRecord;
use App\Lib\Entity\FacilityUserServiceRecord;
use App\Lib\Entity\ServiceItemCode;
use App\Lib\Entity\ServiceResult;
use App\Lib\Factory\ResultFlagFactory;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Carbon\Carbon;

/**
 * 国保連請求の小計計算の仕様。
 */
class NationalHealthBillingSubTotalSpecification
{
    /**
     * 計算して返す。
     */
    public static function calculate(
        Facility $facility,
        FacilityUser $facilityUser,
        FacilityUserBenefitRecord $facilityUserBenefitRecord,
        ?FacilityUserPublicExpenseRecord $facilityUserPublicExpenseRecord,
        FacilityUserServiceRecord $facilityUserServiceRecord,
        ResultFlag $resultFlagCopy,
        int $serviceCount,
        ServiceItemCode $serviceItemCode,
        array $serviceResults,
        int $year,
        int $month
    ): ServiceResult {
        $benefitRate = $facilityUserBenefitRecord->hasRecord() ? $facilityUserBenefitRecord->getLatest()->getBenefitRate() : 0;
        $digits = NationalHealthBillingSpecification::SIGNIFICANT_DIGITS;

        // 施設利用者のサービスを取得する。
        $latestFacilityUserService = $facilityUserServiceRecord->getLatest();
        $serviceId = $latestFacilityUserService->getServiceId();
        $serviceTypeCode = $latestFacilityUserService->getServiceTypeCode()->getServiceTypeCode();

        $unitNumber = 0;
        $unitPrice = $facility->findService($serviceId)->getAreaUnitPrice();

        $resultFlag = (new ResultFlagFactory())->generateTargetYm($resultFlagCopy->getDateDailyRate(), 0);
        $serviceCountDate = $resultFlag->getServiceCountDate();

        $timestamp = Carbon::now()->format('Y/m/d H:i:s');

        // 計算対象の実績種別がサービスの個別計算と事業所計算を取得する。
        // 特別診療費と特定入所者サービスは小計がない。
        $calcTargets = array_filter($serviceResults, function ($result) {
            return $result->isService() && ($result->isIndividual() || $result->isFacility());
        });
        $calcTargets = array_values($calcTargets);

        // 計算対象のサービス実績の小計を計算する。
        $serviceUnitAmountTotal = 0;
        foreach ($calcTargets as $target) {
            $serviceUnitAmountTotal += $target->getServiceUnitAmount();
        }

        // 単位数合計
        // 本来はサービス種類が一致するものでまとめるがケアデイジーは事業所に複数のサービス種類を持たない。
        $serviceUnitAmount = $serviceUnitAmountTotal;

        // 費用 = 単位数合計 * 単位数単価 / 100
        $totalCost = floor(
            bcdiv(bcmul($serviceUnitAmount, $unitPrice, $digits), 100, $digits)
        );

        // 保険給付額 = 費用 * (給付率 / 100)
        $insuranceBenefit = floor(
            bcdiv(bcmul($totalCost, $benefitRate, $digits), 100, $digits)
        );

        // 利用者負担額 = 費用 - 保険給付額
        $partPayment = floor(bcsub($totalCost, $insuranceBenefit, $digits));

        $publicBenefitRate = null;
        $publicExpenditureUnit = null;
        $publicPayment = null;
        $publicSpendingAmount = null;
        $publicSpendingCount = null;
        $publicSpendingUnitNumber = null;
        $publicUnitPrice = null;
        // 施設利用者に公費がある場合。
        if ($facilityUserPublicExpenseRecord !== null) {
            // 施設利用者の適用可能な公費を取得する。
            $applicablePublicExpense = $facilityUserPublicExpenseRecord->getApplicablePublicExpense();

            // 公費対象単位数 = 単位数
            $publicSpendingUnitNumber = $unitNumber;

            // 公費分回数等を作成する。
            $publicSpendingCount = $serviceCountDate;

            // 公費単位数合計
            $publicExpenditureUnit = 0;
            foreach ($calcTargets as $target) {
                $publicExpenditureUnit += $target->getPublicExpenditureUnit();
            }

            // 公費給付率 = 公費マスタ給付率
            $publicBenefitRate = $applicablePublicExpense->getBenefitRate();

            // 費用総額(公費対象分) = (公費単位数合計 * 単位数単価 / 100)
            $publicSpendingTotalCost = floor(bcdiv(bcmul($publicExpenditureUnit, $unitPrice, $digits), 100, $digits));

            // 費用総額(公費対象分の内の保険支払い分) = (費用総額(公費対象分) * 保険給付率 / 100)
            $publicSpendingInsurance = floor(bcdiv(bcmul($publicSpendingTotalCost, $benefitRate, $digits), 100, $digits));

            // 公費請求額 = (費用総額(公費対象分) - 費用総額(公費対象分の内の保険支払い分) * 公費給付率 / 100
            $publicSpendingAmount = bcsub($publicSpendingTotalCost, $publicSpendingInsurance, $digits);
            $publicSpendingAmount = bcmul($publicSpendingAmount, $publicBenefitRate, $digits);
            $publicSpendingAmount = bcdiv($publicSpendingAmount, 100, $digits);
            $publicSpendingAmount = floor($publicSpendingAmount);

            // 公費利用者負担額 = 費用総額(公費対象分) - 費用総額(公費対象分の内の保険支払い分) - 公費請求額
            $publicPayment = bcsub($publicSpendingTotalCost, $publicSpendingInsurance, $digits);
            $publicPayment = bcsub($publicPayment, $publicSpendingAmount, $digits);
            $publicPayment = floor($publicPayment);

            // 公費単位数単価 = 単位数単価
            $publicUnitPrice = $unitPrice;

            // 利用者負担額 = 利用者負担額 - 公費請求額 - 公費利用者負担額
            $partPayment = bcsub($partPayment, $publicSpendingAmount, $digits);
            $partPayment = bcsub($partPayment, $publicPayment, $digits);
            $partPayment = floor($partPayment);
            $partPayment = $partPayment > 0 ? $partPayment : 0;
        }

        // サービス実績を作成する。
        $serviceResult = new ServiceResult(
            ServiceResult::NOT_APPROVAL,
            $benefitRate,
            // burden_limit 。特定入所者サービス以外では値は入りえない。
            null,
            ServiceResult::CALC_KIND_SUBTOTAL,
            // classification_support_limit_in_range,
            $serviceUnitAmount,
            // document_create_date
            $timestamp,
            $facility->getFacilityId(),
            $facility->getFacilityNameKanji(),
            $facility->getFacilityNumber(),
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
            $resultFlag,
            ServiceResult::RESULT_KIND_SERVICE,
            $serviceCount,
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
