<?php

namespace App\Lib\Entity;

use Carbon\CarbonImmutable;

/**
 * 国保連請求csvの特定入所者介護サービス費用情報レコードクラス。
 * 複数行に渡るためRecord「s」となっていることに注意する。考えづらければ改修する。
 */
class IncompetentResidentRecords
{
    private array $records;
    private int $serialNumber;

    /**
     * コンストラクタ
     * 各引数の詳しい知識はプロパティの方に記載する。
     * @param string $blankValue 空白の値
     * @param Facility $facility 事業所
     * @param FacilityUser $facilityUser 施設利用者
     * @param NationalHealthBilling $nationalHealthBilling 国保連請求
     * @param int $serialNumber 連番
     * @param string $targetYm 対象年月
     */
    public function __construct(
        string $blankValue,
        Facility $facility,
        FacilityUser $facilityUser,
        NationalHealthBilling $nationalHealthBilling,
        int $serialNumber,
        string $targetYm
    ) {
        $this->serialNumber = $serialNumber;

        // 施設利用者のサービス実績(特定入所者介護サービス)の個別を取得する。
        $individuals = $nationalHealthBilling->getIncompetentResidentIndividuals();
        $total = $nationalHealthBilling->getIncompetentResidentTotal();

        // 作成するレコードを確保する領域を宣言する。
        $this->records = [];

        for ($i = 0, $cnt = count($individuals), $recordIndex = 1; $i < $cnt; $i++, $recordIndex++) {
            $serviceResult = $individuals[$i];

            $this->serialNumber++;

            // レコードの最終行は99固定になる。
            if ($recordIndex === $cnt) {
                $recordIndex = 99;
            }

            // レコードのインデックスが99の場合のみ格納する必要がある変数。
            $insuranceBenefit = $blankValue;
            $partPayment = $blankValue;
            $publicPayment = $blankValue;
            $publicSpendingAmount = $blankValue;
            $totalAmountOwed = $blankValue;
            $totalCost = $blankValue;
            if ($recordIndex === 99) {
                // 保険分請求額合計
                $insuranceBenefit = $total->getInsuranceBenefit();
                // 利用者負担額合計
                $partPayment = $total->getPartPayment();
                $publicPayment = $total->getPublicPayment();
                // 公費１/請求額
                $publicSpendingAmount = $total->getPublicSpendingAmount();
                $totalCost = $total->getTotalCost();

                // 公費１/負担額合計
                $totalAmountOwed = $publicPayment + $publicSpendingAmount;
            }

            $this->records[] = [
                // 1 データレコード/レコード種別 マジックナンバー
                '2',
                // 2 データレコード/レコード番号(連番)
                $this->serialNumber,
                // 3 交換情報識別番号 マジックナンバー
                '7196',
                // 4 レコード種別番号コード マジックナンバー
                '11',
                // 5 サービス提供年月
                $targetYm,
                // 6 事業所番号
                $facility->getFacilityNumber(),
                // 7 証記載保険者番号
                sprintf('%08d', $facilityUser->getInsurerNo()),
                // 8 被保険者番号
                $facilityUser->getInsuredNo()->getValue(),
                // 9 特定入所者外部サービス費用情報レコード順次番号
                sprintf('%02d', $recordIndex),
                // 10 サービス種類コード
                '59',
                // 11 サービス項目コード
                $serviceResult->getServiceItemCode()->getServiceItemCode(),
                // 12 費用単価
                $serviceResult->getUnitNumber(),
                // 13 負担限度額
                $serviceResult->getBurdenLimit(),
                // 14 日数
                $serviceResult->getResultFlag()->getServiceCountDate(),
                // 15 公費１/日数
                $serviceResult->getPublicSpendingCount(),
                // 16 公費２/日数
                $blankValue,
                // 17 公費３/日数
                $blankValue,
                // 18 費用額
                $serviceResult->getTotalCost(),
                // 19 保険分請求額
                $serviceResult->getInsuranceBenefit(),
                // 20 公費１/負担額（明細）
                $serviceResult->getPublicSpendingAmount(),
                // 21 公費２/負担額（明細）
                $blankValue,
                // 22 公費３/負担額（明細）
                $blankValue,
                // 23 利用者負担額
                $serviceResult->getPartPayment(),
                // 24 費用額合計
                $totalCost,
                // 25 保険分請求額合計
                $insuranceBenefit,
                // 26 利用者負担額合計
                $partPayment,
                // 27 公費１/負担額合計
                $totalAmountOwed,
                // 28 公費１/請求額
                $publicSpendingAmount,
                // 29 公費１/本人負担月額
                $publicPayment,
                // 30 公費２/負担額合計
                $blankValue,
                // 31 公費２/請求額
                $blankValue,
                // 32 公費２/本人負担月額
                $blankValue,
                // 33 公費３/負担額合計
                $blankValue,
                // 34 公費３/請求額
                $blankValue,
                // 35 公費３/本人負担月額
                $blankValue
            ];
        }
    }

    /**
     * レコードを返す。
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * 連番を返す。
     * @return int
     */
    public function getSerialNumber(): int
    {
        return $this->serialNumber;
    }
}
