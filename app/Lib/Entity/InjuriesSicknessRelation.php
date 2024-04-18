<?php

namespace App\Lib\Entity;

/**
 * 傷病名関連クラス。
 * 関連とは特別診療費との関連を指す。
 */
class InjuriesSicknessRelation
{
    /**
     * @var int ID
     */
    private int $relationId;

    /**
     * @var int 選択位置
     */
    private int $selectedPosition;

    /**
     * @var int 特別診療費コードマスタID
     */
    private int $specialMedicalCodeId;

    /**
     * コンストラクタ。
     * 各引数の詳しい知識はプロパティの方に記載する。
     * @param int $relationId
     * @param int $selectedPosition 選択位置
     * @param int $specialMedicalCodeId 特別診療費コードマスタID
     */
    public function __construct(
        int $relationId,
        int $selectedPosition,
        int $specialMedicalCodeId
    ) {
        $this->relationId = $relationId;
        $this->selectedPosition = $selectedPosition;
        $this->specialMedicalCodeId = $specialMedicalCodeId;
    }

    /**
     * 選択位置
     * @return int
     */
    public function getSelectedPosition(): int
    {
        return $this->selectedPosition;
    }

    /**
     * 特別診療コードIDを返す。
     * @return int
     */
    public function getSpecialMedicalCodeId(): int
    {
        return $this->specialMedicalCodeId;
    }
}
