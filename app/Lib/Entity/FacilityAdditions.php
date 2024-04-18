<?php

namespace App\Lib\Entity;

/**
 * 事業所加算の集まりのクラス。
 * 事業所加算を単体ではなく、ある程度のまとまりとして扱う想定が増えたため作成した。
 */
class FacilityAdditions
{
    /**
     * @var FacilityAddition[] サービスコードの集まり。
     */
    private array $facilityAdditions;

    /**
     * コンストラクタ
     * @param FacilityAddition[] サービスコードの集まり。
     */
    public function __construct(array $facilityAdditions)
    {
        $this->facilityAdditions = $facilityAdditions;
    }

    /**
     * 事業所加算を返す。
     * @param int $facilityAdditionId
     * @return FacilityAddition
     */
    public function find(int $facilityAdditionId): FacilityAddition
    {
        $find = null;
        foreach ($this->facilityAdditions as $facilityAddition) {
            if ($facilityAddition->getFacilityAdditionId() === $facilityAdditionId) {
                $find = $facilityAddition;
                break;
            }
        }
        return $find;
    }

    /**
     * 特殊でない事業所加算のみを返す。
     * @return FacilityAddition[]
     */
    public function getOnlyBasic(): array
    {
        $basics = array_filter($this->facilityAdditions, function ($addition) {
            return !$addition->getServiceItemCode()->isFacilitySpecial();
        });
        return $basics;
    }

    /**
     * 特殊の事業所加算のみを返す。
     * @return FacilityAddition[]
     */
    public function getOnlySpecial(): array
    {
        $specials = array_filter($this->facilityAdditions, function ($addition) {
            return $addition->getServiceItemCode()->isFacilitySpecial();
        });
        return $specials;
    }

    /**
     * サービス項目コードを全て返す。
     * @return int[]
     */
    public function getServiceItemCodeIds(): array
    {
        $ids = [];
        foreach ($this->facilityAdditions as $facilityAddition) {
            $ids[] = $facilityAddition->getServiceItemCode()->getServiceItemCodeId();
        }
        return $ids;
    }
}
