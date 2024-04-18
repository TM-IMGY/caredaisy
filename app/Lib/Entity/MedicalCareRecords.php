<?php

namespace App\Lib\Entity;

use Carbon\CarbonImmutable;

/**
 * 国保連請求csvの特定診療費・特別療養費・特別診療費情報レコードクラス。
 * 複数行に渡るためRecord「s」となっていることに注意する。考えづらければ改修する。
 */
class MedicalCareRecords
{
    private array $records;
    private int $serialNumber;

    /**
     * コンストラクタ
     * 各引数の詳しい知識はプロパティの方に記載する。
     * @param string $blankValue ブランクの値
     * @param Facility $facility
     * @param FacilityUser $facilityUser 施設利用者
     * @param ?InjuriesSickness $injuriesSickness 傷病
     * @param NationalHealthBilling $nationalHealthBilling 国保連請求
     * @param int $serialNumber 連番
     * @param string $targetYm 対象年月
     */
    public function __construct(
        string $blankValue,
        Facility $facility,
        FacilityUser $facilityUser,
        ?InjuriesSickness $injuriesSickness,
        NationalHealthBilling $nationalHealthBilling,
        int $serialNumber,
        string $targetYm
    ) {
        $this->serialNumber = $serialNumber;

        // 施設利用者のサービス実績(特別診療)の個別を取得する。
        $individuals = $nationalHealthBilling->getSpecialMedicalIndividuals($injuriesSickness);

        // 施設利用者のサービス実績(特別診療)の合計を取得する。
        $total = $nationalHealthBilling->getSpecialMedicalTotal();

        // 作成するレコードを確保する領域を宣言する。
        $this->records = [];

        for ($i = 0, $cnt = count($individuals), $recordIndex = 1; $i < $cnt; $i++, $recordIndex++) {
            $serviceResult = $individuals[$i];

            $this->serialNumber++;

            // レコードの最終行は99固定になる。
            if ($recordIndex === $cnt) {
                $recordIndex = 99;
            }

            $identificationNum = substr($serviceResult->getSpecialMedicalCode()->getIdentificationNum(), -2, 2);

            // 傷病名
            $injuriesSicknessName = $blankValue;
            if ($injuriesSickness !== null) {
                $injuriesSicknessName = $injuriesSickness->getName($serviceResult->getSpecialMedicalCode()->getSpecialMedicalCodeId());
            }

            // 保険/合計単位数 と 公費１/合計単位数 はレコードのインデックスが99の場合のみ値を格納する。
            $serviceUnitAmount = $blankValue;
            $publicServiceUnitAmount = $blankValue;
            if ($recordIndex === 99) {
                $serviceUnitAmount = $total->getServiceUnitAmount();
                $publicServiceUnitAmount = $total->getPublicExpenditureUnit();
            }

            $this->records[] = [
                // 1 データレコード/レコード種別 マジックナンバー
                '2',
                // 2 データレコード/レコード番号(連番)
                $this->serialNumber,
                // 3 交換情報識別番号
                '7196',
                // 4 レコード種別コード
                '04',
                // 5 サービス提供年月
                $targetYm,
                // 6 事業所番号
                $facility->getFacilityNumber(),
                // 7 証記載保険者番号
                sprintf('%08d', $facilityUser->getInsurerNo()),
                // 8 被保険者番号
                $facilityUser->getInsuredNo()->getValue(),
                // 9 特定診療費情報レコード順番
                sprintf('%02d', $recordIndex),
                // 10 傷病名
                $injuriesSicknessName,
                // 11 識別番号(下2桁)
                $identificationNum,
                // 12 単位数
                $serviceResult->getUnitNumber(),
                // 13 保険/回数
                $serviceResult->getResultFlag()->getServiceCountDate(),
                // 14 保険/サービス単位数
                $serviceResult->getServiceUnitAmount(),
                // 15 保険/合計単位数
                $serviceUnitAmount,
                // 16 公費１/回数
                $serviceResult->getPublicSpendingCount(),
                // 17 公費１/サービス単位数
                $serviceResult->getPublicExpenditureUnit(),
                // 18 公費１/合計単位数
                $publicServiceUnitAmount,
                // 19 公費２/回数
                $blankValue,
                // 20 公費２/サービス単位数
                $blankValue,
                // 21 公費２/合計単位数
                $blankValue,
                // 22 公費３/回数
                $blankValue,
                // 23 公費３/サービス単位数
                $blankValue,
                // 24 公費３/合計単位数
                $blankValue,
                // 25 摘要
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
