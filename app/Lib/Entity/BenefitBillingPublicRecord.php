<?php

namespace App\Lib\Entity;

use Carbon\CarbonImmutable;

/**
 * 国保連請求csvの介護給付費請求情報(公費分)レコードクラス。
 * 介護給付費請求情報レコードは事業所の情報。ヘッダとして機能する。
 */
class BenefitBillingPublicRecord
{
    private array $record;

    /**
     * コンストラクタ
     * 各引数の詳しい知識はプロパティの方に記載する。
     * @param string $blankValue ブランクの値
     * @param Facility $facility 事業所
     * @param string $legalNumber 法別番号
     * @param int $serialNumber 連番
     * @param ServiceResult[] $targetServiceResultTotals 対象のサービス実績の合計
     * @param string $targetYm
     */
    public function __construct(
        string $blankValue,
        Facility $facility,
        string $legalNumber,
        int $serialNumber,
        array $targetServiceResultTotals,
        string $targetYm
    ) {
        // 請求情報区分コード を計算する。
        $billingClassificationCode = $legalNumber === '12' ? '01' : '00';

        // サービス費用/件数 を取得する。
        $serviceTargetFacilityUserIds = array_map(function ($total) {
                return $total->getFacilityUserId();
        }, $targetServiceResultTotals);
        $serviceTargetFacilityUserCount = count(array_unique($serviceTargetFacilityUserIds));

        // サービス費用 を計算する。
        $publicExpenditureUnit = 0;
        $publicSpendingAmount = 0;
        $totalCost = 0;
        foreach ($targetServiceResultTotals as $index => $serviceResultTotal) {
            if ($serviceResultTotal->isSpecialMedical() || $serviceResultTotal->isService()) {
                $publicExpenditureUnit += $serviceResultTotal->getPublicExpenditureUnit();
                $publicSpendingAmount += $serviceResultTotal->getPublicSpendingAmount();
                $totalCost += $serviceResultTotal->getTotalCost();
            }
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
            foreach ($targetServiceResultTotals as $billing) {
                if ($billing->isIncompetentResident()) {
                    $specificServiceResultTotals[] = $billing;
                }
            }

            // 特定入所者介護サービス費の対象の施設利用者の数を取得する。
            $specificTargetFacilityUserIds = array_map(function ($total) {
                return $total->getFacilityUserId();
            }, $specificServiceResultTotals);
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
            '2',
            // 7 法別番号
            $legalNumber,
            // 8 請求情報区分コード
            $billingClassificationCode,

            // 9 サービス費用/件数
            $serviceTargetFacilityUserCount,
            // 10 サービス費用/単位数
            $publicExpenditureUnit,
            // 11 サービス費用/費用合計
            $totalCost,
            // 12 サービス費用/保険請求額 マジックナンバー
            '0',
            // 13 サービス費用/公費請求額
            $publicSpendingAmount,
            // 14 サービス費用/利用者負担 マジックナンバー
            '0',

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
