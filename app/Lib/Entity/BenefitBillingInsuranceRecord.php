<?php

namespace App\Lib\Entity;

use Carbon\CarbonImmutable;

/**
 * 国保連請求csvの介護給付費請求情報(保険分)レコードクラス。
 * 介護給付費請求情報レコードは事業所の情報。ヘッダとして機能する。
 */
class BenefitBillingInsuranceRecord
{
    private array $record;

    /**
     * コンストラクタ
     * 各引数の詳しい知識はプロパティの方に記載する。
     * @param string $blankValue ブランクの値
     * @param Facility $facility 事業所
     * @param NationalHealthBilling[] $nationalHealthBillings 国保連請求
     * @param int $serialNumber 連番
     * @param string $targetYm 対象年月
     */
    public function __construct(
        string $blankValue,
        Facility $facility,
        array $nationalHealthBillings,
        int $serialNumber,
        string $targetYm
    ) {
        // サービス費用の対象を全て取得する。
        $serviceResultTotals = [];
        foreach ($nationalHealthBillings as $billing) {
            // 保険給付率が0の場合は除外する。
            if ($billing->getServiceTotal()->getBenefitRate() === 0) {
                continue;
            }
            $serviceResultTotals[] = $billing->getServiceTotal();
            $specialMedicalTotal = $billing->getSpecialMedicalTotal();
            if ($specialMedicalTotal !== null) {
                $serviceResultTotals[] = $specialMedicalTotal;
            }
        }

        // サービス費用の対象の施設利用者の数を取得する。
        $serviceTargetFacilityUserIds = array_map(
            function ($total) {
                return $total->getFacilityUserId();
            },
            $serviceResultTotals
        );
        $serviceTargetFacilityUserCount = count(array_unique($serviceTargetFacilityUserIds));

        $insuranceBenefit = 0;
        $partPayment = 0;
        $publicPayment = 0;
        $publicSpendingAmount = 0;
        $totalCost = 0;
        $serviceUnitAmount = 0;
        foreach ($serviceResultTotals as $total) {
            $insuranceBenefit += $total->getInsuranceBenefit();
            $partPayment += $total->getPartPayment();
            $publicPayment += $total->getPublicPayment();
            $publicSpendingAmount += $total->getPublicSpendingAmount();
            $totalCost += $total->getTotalCost();
            $serviceUnitAmount += $total->getServiceUnitAmount();
        }

        // 事業所が提供するサービス種類に55が含まれる場合。
        $specificInsuranceBenefit = null;
        $specificPartPayment = null;
        $specificPublicSpendingAmount = null;
        $specificTargetFacilityUserCount = null;
        $specificTotalCost = null;
        if ($facility->hasServiceTypeCode('55')) {
            $specificInsuranceBenefit = 0;
            $specificPartPayment = 0;
            $specificPublicSpendingAmount = 0;
            $specificTotalCost = 0;

            // 特定入所者介護サービス費の対象を全て取得する。
            $specificServiceResultTotals = [];
            foreach ($nationalHealthBillings as $billing) {
                // 保険給付率がない場合は除外する。
                if ($billing->getServiceTotal()->getBenefitRate() === 0) {
                    continue;
                }
                $incompetentResidentTotal = $billing->getIncompetentResidentTotal();
                if ($incompetentResidentTotal !== null) {
                    $specificServiceResultTotals[] = $incompetentResidentTotal;
                }
            }

            // 特定入所者介護サービス費の対象の施設利用者の数を取得する。
            $specificTargetFacilityUserIds = array_map(
                function ($total) {
                    return $total->getFacilityUserId();
                },
                $specificServiceResultTotals
            );
            $specificTargetFacilityUserCount = count(array_unique($specificTargetFacilityUserIds));

            foreach ($specificServiceResultTotals as $total) {
                $specificInsuranceBenefit += $total->getInsuranceBenefit();
                $specificPartPayment += $total->getPartPayment();
                $specificPublicSpendingAmount += $total->getPublicSpendingAmount();
                $specificTotalCost += $total->getTotalCost();
            }
        } else {
            $specificInsuranceBenefit = '';
            $specificPartPayment = '';
            $specificPublicSpendingAmount = '';
            $specificTargetFacilityUserCount = '';
            $specificTotalCost = '';
        }

        $this->record = [
            // 1 データレコード/レコード種別 マジックナンバー
            '2',
            // 2 データレコード/レコード番号(連番)
            $serialNumber,
            // 3 交換情報識別番号 マジックナンバー
            '7111',
            // 4 サービス提供年月
            $targetYm,
            // 5 事業所番号
            $facility->getFacilityNumber(),
            // 6 保険・公費等区分コード マジックナンバー
            '1',
            // 7 法別番号 マジックナンバー
            '00',
            // 8 請求情報区分コード マジックナンバー
            '01',

            // 9 サービス費用/件数
            $serviceTargetFacilityUserCount,
            // 10 サービス費用/単位数
            $serviceUnitAmount,
            // 11 サービス費用/費用合計
            $totalCost,
            // 12 サービス費用/保険請求額
            $insuranceBenefit,
            // 13 サービス費用/公費請求額
            $publicSpendingAmount,
            // 14 サービス費用/利用者負担
            $partPayment + $publicPayment,

            // 15 特定入所者介護サービス費等/件数
            $specificTargetFacilityUserCount,
            // 16 特定入所者介護サービス費等/延べ日数
            $blankValue,
            // 17 特定入所者介護サービス費等/費用合計
            $specificTotalCost,
            // 18 特定入所者介護サービス費等/利用者負担
            $specificPartPayment,
            // 19 特定入所者介護サービス費等/公費請求額
            $specificPublicSpendingAmount,
            // 20 特定入所者介護サービス費等/保険請求額
            $specificInsuranceBenefit
        ];
    }

    /**
     * レコードを返す。
     * @return array
     */
    public function getRecord(): array
    {
        return $this->record;
    }
}
