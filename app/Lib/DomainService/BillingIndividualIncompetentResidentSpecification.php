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
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Carbon\Carbon;

/**
 * 国保連請求の計算種別個別の特定入所者介護サービスの計算の仕様。
 */
class BillingIndividualIncompetentResidentSpecification
{
    public static function calculate(
        int $burdenLimit,
        Facility $facility,
        FacilityUser $facilityUser,
        FacilityUserBenefitRecord $facilityUserBenefitRecord,
        ?FacilityUserPublicExpenseRecord $facilityUserPublicExpenseRecord,
        ResultFlag $resultFlag,
        ServiceItemCode $serviceItemCode,
        ?SpecialMedicalCode $specialMedicalCode,
        int $year,
        int $month
    ): ServiceResult {
        $benefitRate = $facilityUserBenefitRecord->hasRecord() ? $facilityUserBenefitRecord->getLatest()->getBenefitRate() : 0;
        $digits = NationalHealthBillingSpecification::SIGNIFICANT_DIGITS;
        // 施設利用者のサービス種類は59固定になる(提供を受けているサービス種類自体は55)。
        $serviceTypeCode = '59';
        $dateDailyRate = $resultFlag->getDateDailyRate();
        $serviceCountDate = $resultFlag->getServiceCountDate();
        $timestamp = Carbon::now()->format('Y/m/d H:i:s');
        $unitNumber = $serviceItemCode->getServiceSyntheticUnit();
        $unitPrice = ServiceResult::UNIT_PRICE_INCOMPETENT_RESIDENT;

        // 単位数合計 は 特定入所者サービス では金額になる(費用額 と呼ばれる)。
        // 単位数 は 特定入所者サービスでは金額になる(費用単価 と呼ばれる)。
        // 単位数合計(費用額) = 単位数(費用単価) * 日数
        $serviceUnitAmount = round(
            bcmul($unitNumber, $serviceCountDate, $digits)
        );

        // 利用者負担額 = (負担限度額 * 回数)
        $partPayment = floor(
            bcmul($burdenLimit, $serviceCountDate, $digits)
        );

        // 費用額 = 単位数合計 と 利用者負担額 の大きい方。
        // セーフティとして設けられているが 単位数合計 が 利用者負担額 を下回ることはない。
        $totalCost = $serviceUnitAmount > $partPayment ? $serviceUnitAmount : $partPayment;

        // 保険給付額 を計算する。
        $insuranceBenefit = null;
        // 保険がない場合は0になる。
        if ($facilityUser->isUnder65()) {
            $insuranceBenefit = 0;
        // 保険がある場合は 単位数合計 - 利用者負担額
        } else {
            $insuranceBenefit = floor(
                bcsub($serviceUnitAmount, $partPayment, $digits)
            );
            // 計算結果がマイナスになる場合は0とする。
            // (上述の通り)セーフティとして設けられているが 単位数合計 が 利用者負担額 を下回ることはない。
            $insuranceBenefit = $insuranceBenefit < 0 ? 0 : $insuranceBenefit;
        }

        $publicBenefitRate = null;
        $publicExpenditureUnit = null;
        $publicPayment = null;
        $publicSpendingAmount = null;
        $publicSpendingCount = null;
        $publicSpendingUnitNumber = null;
        $publicUnitPrice = null;
        // 施設利用者に特定入所者サービス(種類59)で適用可能な公費がある場合。
        if ($facilityUserPublicExpenseRecord !== null && $facilityUserPublicExpenseRecord->getApplicablePublicExpense()->getPublicExpense()->isAvailable($serviceTypeCode)) {
            // 施設利用者の適用可能な公費を取得する。
            $applicablePublicExpense = $facilityUserPublicExpenseRecord->getApplicablePublicExpense();
            // 公費の開始日と終了日を取得する。
            $startDate = $applicablePublicExpense->getEffectiveStartDate();
            $endDate = $applicablePublicExpense->getExpiryDate();

            // 公費対象単位数 = 単位数
            $publicSpendingUnitNumber = $unitNumber;

            // 対象年月の公費１対象日数・回数を計算する。
            $date = new Carbon("${year}-${month}-1");
            $dateDailyRatePublic = str_split($dateDailyRate, 1);
            $publicSpendingCount = 0;
            for ($i = 0, $daysInMonth = $date->daysInMonth; $i < $daysInMonth; $i++) {
                if ($date->between($startDate, $endDate) && $dateDailyRatePublic[$i] !== '0') {
                    $publicSpendingCount += intval($dateDailyRatePublic[$i]);
                }
                $date->addDay();
            }

            // 公費単位数合計 = 公費対象単位数 * 公費１対象日数・回数
            $publicExpenditureUnit = round(
                bcmul($publicSpendingUnitNumber, $publicSpendingCount, $digits)
            );

            // 公費給付率 = 公費マスタ給付率
            $publicBenefitRate = $applicablePublicExpense->getBenefitRate();

            // 公費請求額 と 公費利用者負担額 を計算する。
            // 保険がない場合は 公費請求額 = (費用単価 * 公費日数) * 公費給付率
            // また 公費利用者負担額 = (費用単価 * 公費日数) - 公費請求額
            if ($facilityUser->isUnder65()) {
                $publicSpendingAmount = bcmul($publicExpenditureUnit, $publicBenefitRate, $digits);
                $publicSpendingAmount = bcdiv($publicSpendingAmount, 100, $digits);

                // 給付率は100%なので処理上必ず0になる。
                $publicPayment = bcsub($publicExpenditureUnit, $publicSpendingAmount, $digits);
                $publicPayment = floor($publicPayment);

            // 保険がある場合は 公費請求額 = (負担限度額 * 公費日数) * 公費給付率
            // また 公費利用者負担額 = (負担限度額 * 公費日数) - 公費請求額
            } else {
                $publicSpendingAmount = bcmul($burdenLimit, $publicSpendingCount, $digits);
                $publicSpendingAmount = bcmul($publicSpendingAmount, $publicBenefitRate, $digits);
                $publicSpendingAmount = bcdiv($publicSpendingAmount, 100, $digits);
                $publicSpendingAmount = floor($publicSpendingAmount);

                // 給付率は100%なので処理上必ず0になる。
                $publicPayment = bcsub(bcmul($burdenLimit, $publicSpendingCount, $digits), $publicSpendingAmount, $digits);
                $publicPayment = floor($publicPayment);
            }

            // 公費単位数単価 = 単位数単価
            $publicUnitPrice = $unitPrice;

            // 利用者負担額 = 利用者負担額 - 公費請求額 - 公費利用者負担額
            $partPayment = bcsub($partPayment, $publicSpendingAmount, $digits);
            $partPayment = bcsub($partPayment, $publicPayment, $digits);
            $partPayment = floor($partPayment);
            $partPayment = $partPayment < 0 ? 0 : $partPayment;
        }

        // サービス実績を作成する。
        $serviceResult = new ServiceResult(
            ServiceResult::NOT_APPROVAL,
            $benefitRate,
            $burdenLimit,
            ServiceResult::CALC_KIND_INDIVIDUAL,
            // $classification_support_limit_in_range,
            $serviceUnitAmount,
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
            ServiceResult::RESULT_KIND_INCOMPETENT_RESIDENT,
            $serviceCountDate,
            9999,
            $serviceItemCode,
            $serviceItemCode->getServiceItemCodeId(),
            // service_result_id
            null,
            9999,
            $serviceUnitAmount,
            $timestamp,
            $specialMedicalCode,
            "${year}-${month}-1",
            $totalCost,
            $unitNumber,
            $unitPrice
        );

        return $serviceResult;
    }
}
