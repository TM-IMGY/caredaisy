<?php

namespace App\Lib\Entity;

use Carbon\CarbonImmutable;

/**
 * 国保連請求csvの集計情報レコードのクラス。
 */
class AggregationRecord
{
    private array $record;

    /**
     * コンストラクタ
     * 各引数の詳しい知識はプロパティの方に記載する。
     * @param string $blankValue ブランクの値
     * @param string $exchangeInformationNumber 交換情報識別番号
     * @param Facility $facility 事業所
     * @param FacilityUser $facilityUser 施設利用者
     * @param FacilityUserService $facilityUserService 施設利用者のサービス
     * @param int $serialNumber 連番
     * @param ServiceResult $serviceResultTotalBasic 施設利用者のサービス実績の合計(サービス。紛らわしいのでBasicとする)。
     * @param ?ServiceResult $serviceResultTotalSpecial 施設利用者のサービス実績の合計(特別診療コード)。
     * @param string 対象年月
     */
    public function __construct(
        string $blankValue,
        string $exchangeInformationNumber,
        Facility $facility,
        FacilityUser $facilityUser,
        FacilityUserService $facilityUserService,
        int $serialNumber,
        ServiceResult $serviceResultTotalBasic,
        ?ServiceResult $serviceResultTotalSpecial,
        string $targetYm
    ) {
        // 保険分出来高医療費 と 公費1分出来高医療費
        $medicalBilling = $blankValue;
        $medicalBillingPublic = $blankValue;
        $medicalExpenses = $blankValue;
        $medicalExpensesPublic = $blankValue;
        $medicalTotalCredits = $blankValue;
        $medicalTotalCreditsPublic = $blankValue;
        // 施設利用者のサービス種類が55であり、特別診療コードがある場合。
        if ($facilityUserService->isHospital() && $serviceResultTotalSpecial !== null) {
            // 保険分出来高医療費/単位数合計
            $medicalTotalCredits = $serviceResultTotalSpecial->getServiceUnitAmount();
            // 保険分出来高医療費/請求額
            $medicalBilling = $serviceResultTotalSpecial->getInsuranceBenefit();
            // 保険分出来高医療費/出来高医療費利用者負担額
            $medicalExpenses = $serviceResultTotalSpecial->getPartPayment();
            // 公費1分出来高医療費/単位数合計
            $medicalTotalCreditsPublic = $serviceResultTotalSpecial->getPublicExpenditureUnit();
            // 公費1分出来高医療費/請求額
            $medicalBillingPublic = $serviceResultTotalSpecial->getPublicSpendingAmount();
            // 公費1分出来高医療費/出来高医療費本人負担額
            $medicalExpensesPublic = $serviceResultTotalSpecial->getPublicPayment();
        }

        $this->record = [
            // 1 データレコード/レコード種別 マジックナンバー
            '2',
            // 2 データレコード/レコード番号(連番)
            $serialNumber,
            // 3 交換情報識別番号
            $exchangeInformationNumber,
            // 4 レコード種別コード マジックナンバー
            '10',
            // 5 サービス提供年月
            $targetYm,
            // 6 事業所番号
            $facility->getFacilityNumber(),
            // 7 証記載保険者番号
            sprintf('%08d', $facilityUser->getInsurerNo()),
            // 8 被保険者番号
            $facilityUser->getInsuredNo()->getValue(),

            // 9 サービス種類コード
            $facilityUserService->getServiceTypeCode()->getServiceTypeCode(),
            // 10 サービス実日数
            $blankValue,
            // 11 計画単位数
            $blankValue,
            // 12 限度額管理対象単位数
            $blankValue,
            // 13 限度額管理対象外単位数
            $blankValue,
            // 14 短期入所計画日数
            $blankValue,
            // 15 短期入所実日数
            $blankValue,

            // 16 保険/単位数合計
            $serviceResultTotalBasic->getServiceUnitAmount(),
            // 17 保険/単位数単価
            $serviceResultTotalBasic->getUnitPrice(),
            // 18 保険/請求額
            $serviceResultTotalBasic->getInsuranceBenefit(),
            // 19 保険/利用者負担額
            $serviceResultTotalBasic->getPartPayment(),

            // 20 公費1/単位数合計
            $serviceResultTotalBasic->getPublicExpenditureUnit(),
            // 21 公費1/請求額
            $serviceResultTotalBasic->getPublicSpendingAmount(),
            // 22 公費1/本人負担額
            $serviceResultTotalBasic->getPublicPayment(),

            // 23 公費2/単位数合計
            $blankValue,
            // 24 公費2/請求額
            $blankValue,
            // 25 公費2/本人負担額
            $blankValue,

            // 26 公費3/単位数合計
            $blankValue,
            // 27 公費3/請求額
            $blankValue,
            // 28 公費3/本人負担額
            $blankValue,

            // 29 保険分出来高医療費/単位数合計
            $medicalTotalCredits,
            // 30 保険分出来高医療費/請求額
            $medicalBilling,
            // 31 保険分出来高医療費/出来高医療費利用者負担額
            $medicalExpenses,

            // 32 公費1分出来高医療費/単位数合計
            $medicalTotalCreditsPublic,
            // 33 公費1分出来高医療費/請求額
            $medicalBillingPublic,
            // 34 公費1分出来高医療費/出来高医療費利用者負担額
            $medicalExpensesPublic,

            // 35 公費2分出来高医療費/単位数合計
            $blankValue,
            // 36 公費2分出来高医療費/請求額
            $blankValue,
            // 37 公費2分出来高医療費/出来高医療費利用者負担額
            $blankValue,

            // 38 公費3分出来高医療費/単位数合計
            $blankValue,
            // 39 公費3分出来高医療費/請求額
            $blankValue,
            // 40 公費3分出来高医療費/出来高医療費利用者負担額
            $blankValue
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
