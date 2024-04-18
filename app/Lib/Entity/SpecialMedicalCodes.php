<?php

namespace App\Lib\Entity;

/**
 * 特別診療費コードの集まりのクラス。
 * 特別診療費コードを単体ではなく、ある程度のまとまりとして扱う想定が増えたため作成した。
 */
class SpecialMedicalCodes
{
    /**
     * @var SpecialMedicalCode[] 特別診療費コードの集まり。
     */
    private array $specialMedicalCodes;

    /**
     * コンストラクタ
     * @param SpecialMedicalCode[] 特別診療費コードの集まり。
     */
    public function __construct(array $specialMedicalCodes)
    {
        $this->specialMedicalCodes = $specialMedicalCodes;
    }

    /**
     * 単位を返す。
     * @param int $id
     * @return SpecialMedicalCode
     */
    public function find(int $id): SpecialMedicalCode
    {
        $find = null;
        foreach ($this->specialMedicalCodes as $specialMedicalCode) {
            if ($specialMedicalCode->getId() === $id) {
                $find = $specialMedicalCode;
                break;
            }
        }
        return $find;
    }
}
