<?php

namespace App\Lib\Entity;

use Carbon\CarbonImmutable;

/**
 * 国保連請求csvの基本摘要情報レコードのクラス。
 */
class BasicSummaryRecord
{
    private array $record;

    /**
     * コンストラクタ
     * 各引数の詳しい知識はプロパティの方に記載する。
     * @param ?BasicRemark $basicRemark 基本摘要
     * @param string $blankValue ブランクの値
     * @param Facility $facility 事業所
     * @param FacilityUser $facilityUser 施設利用者
     * @param int $serialNumber 連番
     * @param string $targetYm 対象年月
     */
    public function __construct(
        ?BasicRemark $basicRemark,
        $blankValue,
        Facility $facility,
        FacilityUser $facilityUser,
        int $serialNumber,
        string $targetYm
    ) {
        // 摘要種類コード
        $summaryTypeCode = $blankValue;
        if ($basicRemark !== null) {
            // マジックナンバー
            $summaryTypeCode = $basicRemark->hasUserCircumstanceCode() ? '02' : '01';
        }

        // 内容
        $contents = $blankValue;
        if ($basicRemark !== null) {
            $contents = $basicRemark->hasUserCircumstanceCode() ? $basicRemark->getUserCircumstanceCode() : $basicRemark->getDpcCode();
        }

        $this->record = [
            // データレコード/レコード種別 マジックナンバー
            '2',
            // データレコード/レコード番号(連番)
            $serialNumber,
            // 交換情報識別番号 マジックナンバー
            '7196',
            // レコード種別番号 マジックナンバー
            '16',
            // サービス提供年月
            $targetYm,
            // 事業所番号
            $facility->getFacilityNumber(),
            // 証記載保険者番号
            sprintf('%08d', $facilityUser->getInsurerNo()),
            // 被保険者番号
            $facilityUser->getInsuredNo()->getValue(),
            // 摘要種類コード
            $summaryTypeCode,
            // 内容
            $contents
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
