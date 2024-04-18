<?php

namespace App\Lib\DomainService;

use App\Lib\DomainService\NationalHealthBillingSpecification;
use App\Lib\Entity\FacilityUserPublicExpenseRecord;
use App\Lib\Entity\FacilityUserServiceRecord;
use App\Lib\Entity\ServiceItemCode;
use App\Lib\Entity\ServiceResult;
use Carbon\Carbon;

/**
 * 国保連請求の特殊計算の仕様のクラス。
 */
class NationalHealthBillingSpecailSpecification
{
    /**
     * サービス実績から計算種別特殊を再計算して返す。
     * TODO: ServiceResultの状態をセッターで変えるくらいならインスタンスを作り直した方がいいかもしれない。
     * @return ServiceResult[]
     */
    public static function reCalculate(
        ?FacilityUserPublicExpenseRecord $facilityUserPublicExpenseRecord,
        FacilityUserServiceRecord $facilityUserServiceRecord,
        array $serviceResults
    ): array {
        $digits = NationalHealthBillingSpecification::SIGNIFICANT_DIGITS;
        // 施設利用者の最新のサービスを取得する。
        $serviceTypeCode = $facilityUserServiceRecord->getLatest()->getServiceTypeCode()->getServiceTypeCode();

        // 再計算対象をランク昇順で取得する。
        $specials = array_filter($serviceResults, function ($result) {
            return $result->isFacilitySpecial();
        });
        $specials = array_values($specials);
        usort($specials, function ($a, $b) {
            return $a->getServiceItemCode()->getRank() < $b->getServiceItemCode()->getRank() ? -1 : 1;
        });

        foreach ($specials as $special) {
            foreach ($serviceResults as $comparison) {
                // 比較対象は個別、事業所(基本)、事業所(特殊)。
                if (!($comparison->isIndividual() || $comparison->isFacility() || $comparison->isFacilitySpecial())) {
                    continue;
                }

                // サービス種類が同じかつ加算ランクが小さいもののサービス単位金額を加算する。
                $isSameServiceTypeCode = $special->getServiceItemCode()->getServiceTypeCode() === $comparison->getServiceItemCode()->getServiceTypeCode();
                // 特別診療費の場合サービス種類が存在しない(登録上は55)のでサービス種類が同じと判定する。
                if ($comparison->getServiceItemCode()->isSpecialMedical()) {
                    $isSameServiceTypeCode = true;
                }
                if (!($isSameServiceTypeCode && $special->getRank() > $comparison->getRank())) {
                    continue;
                }

                $special->setServiceUnitAmount($special->getServiceUnitAmount() + $comparison->getServiceUnitAmount());
                if ($special->hasPublicExpenditureUnit()) {
                    $special->setPublicExpenditureUnit($special->getPublicExpenditureUnit() + $comparison->getPublicExpenditureUnit());
                }
            }

            // 単位数合計と公費単位数合計を再計算する。
            $calcInfo = bcdiv($special->getServiceItemCode()->getServiceCalcInfo1(), 1000, $digits);
            $unitSum = bcmul($special->getServiceUnitAmount(), $calcInfo, $digits);
            $unitSum = round($unitSum);
            $unitSumPublic = null;
            if ($special->hasPublicExpenditureUnit()) {
                $unitSumPublic = bcmul($special->getPublicExpenditureUnit(), $calcInfo, $digits);
                $unitSumPublic = round($unitSumPublic);
            }

            // 費用総額(保険対象分) = 単位数合計 * 単位数単価 / 100
            $totalCost = floor(
                bcdiv(bcmul($unitSum, $special->getUnitPrice(), $digits), 100, $digits)
            );

            // 保険給付額 = 費用 * ( 給付率 / 100)
            $insuranceBenefit = floor(
                bcmul($totalCost, bcdiv($special->getBenefitRate(), 100, $digits), $digits)
            );

            // 利用者負担額 = 費用 - 保険給付額
            $partPayment = floor(
                bcsub($totalCost, $insuranceBenefit, $digits)
            );

            // 区分支給限度基準内単位数
            $classificationSupportLimitInRange = self::getClassificationSupportLimitInRange($serviceTypeCode, $unitSum);

            $publicExpenditureUnit = null;
            $publicPayment = null;
            $publicSpendingAmount = null;
            $publicSpendingUnitNumber = null;
            // 施設利用者に公費がある場合。
            if ($facilityUserPublicExpenseRecord !== null) {
                // 施設利用者の適用可能な公費を取得する。
                $applicablePublicExpense = $facilityUserPublicExpenseRecord->getApplicablePublicExpense();

                // 公費対象単位数 = 単位数
                $publicSpendingUnitNumber = $unitSumPublic;

                // 公費単位数合計 = 公費対象単位数 * 公費分回数等
                $publicExpenditureUnit = round(
                    bcmul($publicSpendingUnitNumber, $special->getPublicSpendingCount(), $digits)
                );

                // 公費給付率 = 公費マスタ給付率
                $publicBenefitRate = $applicablePublicExpense->getBenefitRate();

                // 費用総額(公費対象分) = (公費単位数合計 * 単位数単価 / 100)
                $publicSpendingTotalCost = floor(
                    bcdiv(bcmul($publicExpenditureUnit, $special->getUnitPrice(), $digits), 100, $digits)
                );

                // 費用総額(公費対象分の内の保険支払い分) = (費用総額(公費対象分) * 保険給付率 / 100)
                $publicSpendingInsurance = floor(
                    bcdiv(bcmul($publicSpendingTotalCost, $special->getBenefitRate(), $digits), 100, $digits)
                );

                // 公費請求額 = (費用総額(公費対象分) - 費用総額(公費対象分の内の保険支払い分) * 公費給付率 / 100
                $publicSpendingAmount = bcsub($publicSpendingTotalCost, $publicSpendingInsurance, $digits);
                $publicSpendingAmount = bcmul($publicSpendingAmount, $publicBenefitRate, $digits);
                $publicSpendingAmount = bcdiv($publicSpendingAmount, 100, $digits);
                $publicSpendingAmount = floor($publicSpendingAmount);

                // 公費利用者負担額 = 費用総額(公費対象分) - 費用総額(公費対象分の内の保険支払い分) - 公費請求額
                $publicPayment = bcsub($publicSpendingTotalCost, $publicSpendingInsurance, $digits);
                $publicPayment = bcsub($publicPayment, $publicSpendingAmount, $digits);
                $publicPayment = floor($publicPayment);

                // 利用者負担額 = 利用者負担額 - 公費請求額 - 公費利用者負担額
                $partPayment = bcsub($partPayment, $publicSpendingAmount, $digits);
                $partPayment = bcsub($partPayment, $publicPayment, $digits);
                $partPayment = floor($partPayment);
                $partPayment = $partPayment > 0 ? $partPayment : 0;

                $special->setPublicExpenditureUnit($publicExpenditureUnit);
                $special->setPublicPayment($publicPayment);
                $special->setPublicSpendingAmount($publicSpendingAmount);
                $special->setPublicSpendingUnitNumber($publicSpendingUnitNumber);
            }

            $special->setClassificationSupportLimitInRange($classificationSupportLimitInRange);
            $special->setInsuranceBenefit($insuranceBenefit);
            $special->setPartPayment($partPayment);
            $special->setServiceUnitAmount($unitSum);
            $special->setTotalCost($totalCost);
            $special->setUnitNumber($unitSum);
        }

        return $serviceResults;
    }

    /**
     * 区分支給限度基準内単位数をサービス種類の仕様に沿って返す。
     * 種類33に外部サービスが含まれない仕様に沿っているので将来的に修正される。
     * @param string $serviceTypeCode サービス種類コード
     * @param int $serviceUnitAmount 単位数合計
     * @return int
     */
    public static function getClassificationSupportLimitInRange(string $serviceTypeCode, int $serviceUnitAmount): int
    {
        // 単位数合計を設定するサービスコードの場合。
        // 80は厳密にはサービス種類コードではない。
        if (in_array($serviceTypeCode, ['32', '33', '35', '36', '37', '55', '59', '80'])) {
            return $serviceUnitAmount;
        }
    }
}
