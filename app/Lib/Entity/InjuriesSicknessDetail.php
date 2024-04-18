<?php

namespace App\Lib\Entity;

/**
 * 傷病名詳細クラス。
 */
class InjuriesSicknessDetail
{
    private int $detailId;

    private int $group;

    /**
     * @var string 名前
     */
    private string $name;

    /**
     * @var InjuriesSicknessRelation[] 傷病名関連
     */
    private array $injuriesSicknessRelations;

    /**
     * コンストラクタ。
     * 各引数の詳しい知識はプロパティの方に記載する。
     * @param int $detailId
     * @param int $group
     * @param InjuriesSicknessRelation[] $injuriesSicknessRelations 傷病名関連
     * @param string $name 名前
     */
    public function __construct(
        int $detailId,
        int $group,
        array $injuriesSicknessRelations,
        string $name
    ) {
        $this->detailId = $detailId;
        $this->group = $group;
        $this->injuriesSicknessRelations = $injuriesSicknessRelations;
        $this->name = $name;
    }

    /**
     * 傷病名関連を返す。
     * @param int $specialMedicalCodeId 特別診療コードID
     * @return ?InjuriesSicknessRelation
     */
    public function findRelation(int $specialMedicalCodeId): ?InjuriesSicknessRelation
    {
        foreach ($this->injuriesSicknessRelations as $relation) {
            if ($relation->getSpecialMedicalCodeId() === $specialMedicalCodeId) {
                return $relation;
            }
        }
        // 見つからない場合はnullを返す。
        return null;
    }

    /**
     * グループ
     * @return int
     */
    public function getGroup(): int
    {
        return $this->group;
    }

    /**
     * idを返す
     * @return int
     */
    public function getDetailId(): int
    {
        return $this->detailId;
    }

    /**
     * 名前を全て返す。
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 特別診療コードIDを全て返す。
     * @return int[]
     */
    public function getSpecialMedicalCodeIds(): array
    {
        $ids = [];
        foreach ($this->injuriesSicknessRelations as $relation) {
            $ids[] = $relation->getSpecialMedicalCodeId();
        }
        return $ids;
    }

    /**
     * 指定の特別診療コードIDを持つかを返す。
     * @param int $specialMedicalCodeId 特別診療コードID
     * @return bool
     */
    public function hasSpecialMedicalCodeId(int $specialMedicalCodeId): bool
    {
        return in_array($specialMedicalCodeId, $this->getSpecialMedicalCodeIds());
    }
}
