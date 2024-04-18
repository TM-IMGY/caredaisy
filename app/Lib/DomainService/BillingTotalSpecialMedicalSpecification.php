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
 * 国保連請求の合計計算のサービスの仕様のクラス。
 */
class BillingTotalSpecialMedicalSpecification
{
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
        // 給付率
        // 給付率がない場合は0として扱う。
        $benefitRate = $facilityUserBenefitRecord->hasRecord() ? $facilityUserBenefitRecord->getLatest()->getBenefitRate() : 0;
        $digits = NationalHealthBillingSpecification::SIGNIFICANT_DIGITS;
        // 回数／日数
        $resultFlagObject = (new ResultFlagFactory())->generateInitial();
        $serviceCountDate = $resultFlagObject->getServiceCountDate();
        // 施設利用者の最新のサービスを取得する。
        $latestFacilityUserService = $facilityUserServiceRecord->getLatest();
        $serviceId = $latestFacilityUserService->getServiceId();
        $serviceTypeCode = $latestFacilityUserService->getServiceTypeCode()->getServiceTypeCode();
        // システム時刻を取得する。
        $timestamp = Carbon::now()->format('Y/m/d H:i:s');
        // 単位数
        $unitNumber = 0;
        // 単位数単価
        $unitPrice = ServiceResult::UNIT_PRICE_SPECIAL_MEDICAL_CODE;

        // 計算対象のサービス実績を取得する(特別診療費コードは個別か合計しかない)。
        $calcTargets = array_filter($serviceResults, function ($result) {
            return $result->isIndividual() && $result->isSpecialMedical();
        });
        // インデックスをリセットする。
        $calcTargets = array_values($calcTargets);

        // 単位数合計
        $serviceUnitAmount = 0;
        foreach ($calcTargets as $target) {
            $serviceUnitAmount += $target->getServiceUnitAmount();
        }

        // 費用 = 単位数合計 * 単位数単価 / 100
        $totalCost = floor(
            bcdiv(bcmul($serviceUnitAmount, $unitPrice, $digits), 100, $digits)
        );

        // 区分支給限度基準内単位数
        // 種類33が外部サービスを外したことにより全て単位数合計を四捨五入してセットする。
        $classificationSupportLimitInRange = round($serviceUnitAmount);

        // 区分支給限度基準超過単位数
        // 種類33が外部サービスを外したことにより全てnullをセットする。
        $classificationSupportLimitOver = null;

        // 保険給付額
        $insuranceBenefit = self::getInsuranceBenefit($benefitRate, $serviceTypeCode, $totalCost);

        // 利用者負担額
        $partPayment = self::getPartPayment($insuranceBenefit, $serviceTypeCode, $totalCost);

        $publicBenefitRate = null;
        $publicExpenditureUnit = null;
        $publicPayment = null;
        $publicSpendingAmount = null;
        $publicSpendingCount = null;
        $publicSpendingUnitNumber = null;
        $publicUnitPrice = null;
        // 施設利用者に公費がある場合。
        if ($facilityUserPublicExpenseRecord !== null) {
            $applicablePublicExpense = $facilityUserPublicExpenseRecord->getApplicablePublicExpense();

            // 公費対象単位数 = 単位数
            $publicSpendingUnitNumber = $unitNumber;

            // 公費分回数等 = 回数／日数
            $publicSpendingCount = $serviceCountDate;

            // 公費単位数合計
            $publicExpenditureUnit = 0;
            foreach ($calcTargets as $target) {
                $publicExpenditureUnit += $target->getPublicExpenditureUnit();
            }

            // 公費給付率 = 公費マスタ給付率
            $publicBenefitRate = $applicablePublicExpense->getBenefitRate();

            // 費用総額(公費対象分) = (公費単位数合計 * 単位数単価 / 100)
            $publicSpendingTotalCost = floor(
                bcdiv(bcmul($publicExpenditureUnit, $unitPrice, $digits), 100, $digits)
            );

            // 費用総額(公費対象分の内の保険支払い分) = (費用総額(公費対象分) * 保険給付率 / 100)
            $publicSpendingInsurance = floor(
                bcdiv(bcmul($publicSpendingTotalCost, $benefitRate, $digits), 100, $digits)
            );

            // 公費請求額 = (費用総額(公費対象分) - 費用総額(公費対象分の内の保険支払い分)) * 公費給付率 / 100 - 本人支払い額
            $publicSpendingAmount = bcsub($publicSpendingTotalCost, $publicSpendingInsurance, $digits);
            $publicSpendingAmount = bcmul($publicSpendingAmount, $publicBenefitRate, $digits);
            $publicSpendingAmount = bcdiv($publicSpendingAmount, 100, $digits);
            $publicSpendingAmount = floor($publicSpendingAmount);
            $publicSpendingAmount = $publicSpendingAmount > 0 ? $publicSpendingAmount : 0;

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
            ServiceResult::RESULT_KIND_SPECIAL_MEDICAL,
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
            // special_medical_code_id
            null,
            // target_date
            "${year}-${month}-1",
            $totalCost,
            $unitNumber,
            $unitPrice
        );

        return $serviceResult;
    }

    /**
     * 保険給付額を計算して返す。
     * サービス種類によって分岐することが予想されたため切り離されている。
     * 種類33は外部サービスを外しているため種類32と同じように扱う。
     * @param ?int $benefitRate 給付率
     * @param string $serviceTypeCode サービス種類
     * @param int $totalCost 費用
     * @return int
     */
    public static function getInsuranceBenefit(?int $benefitRate, string $serviceTypeCode, int $totalCost): int
    {
        $digits = NationalHealthBillingSpecification::SIGNIFICANT_DIGITS;

        // 給付率がない場合は0として扱う。
        if ($benefitRate === null) {
            $benefitRate = 0;
        }

        $insuranceBenefit = 0;
        if (in_array($serviceTypeCode, ['32', '33', '35', '36', '37', '55'])) {
            // 費用 * 給付率 / 100
            $insuranceBenefit = floor(
                bcdiv(bcmul($totalCost, $benefitRate, $digits), 100, $digits)
            );
        }

        return $insuranceBenefit;
    }

    /**
     * 利用者負担額を計算して返す。
     * サービス種類によって分岐することが予想されたため切り離されている。
     * 種類33は外部サービスを外しているため種類32と同じように扱う。
     * @param int $insuranceBenefit 保険給付額
     * @param string $serviceTypeCode サービス種類コード
     * @param int $totalCost 費用
     * @return int
     */
    public static function getPartPayment(int $insuranceBenefit, string $serviceTypeCode, int $totalCost): int
    {
        $digits = NationalHealthBillingSpecification::SIGNIFICANT_DIGITS;

        $partPayment = 0;
        if (in_array($serviceTypeCode, ['32', '33', '35', '36', '37', '55'])) {
            // 費用 - 保険給付額
            $partPayment = floor(
                bcsub($totalCost, $insuranceBenefit, $digits)
            );
        }

        return $partPayment;
    }
}
