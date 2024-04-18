<?php

namespace App\Lib\Entity;

/**
 * 介護情報クラス。
 */
class CareLevel
{
    private int $careLevelId;
    private int $careLevel;
    private string $careLevelName;
    private int $classificationSupportLimitUnits;
    // care_level_id
    // 非該当
    private const NOT_APPLICABLE_CARE_LEVEL_ID = 1;

    /**
     * @param int $careLevelId
     * @param int $careLevel
     * @param string $careLevelName
     * @param int $classificationSupportLimitUnits
     */
    public function __construct(
        int $careLevelId,
        int $careLevel,
        string $careLevelName,
        int $classificationSupportLimitUnits
    ) {
        $this->careLevelId = $careLevelId;
        $this->careLevel = $careLevel;
        $this->careLevelName = $careLevelName;
        $this->classificationSupportLimitUnits = $classificationSupportLimitUnits;
    }

    /**
     * 介護度を返す。
     * @return int
     */
    public function getCareLevel(): int
    {
        return $this->careLevel;
    }

    /**
     * 介護度IDを返す。
     * @return int
     */
    public function getCareLevelId(): int
    {
        return $this->careLevelId;
    }

    /**
     * を返す。
     * @return int
     */
    public function getClassificationSupportLimitUnits(): int
    {
        return $this->classificationSupportLimitUnits;
    }

    /**
     * 要介護度が非該当かどうか返す。
     * TODO: 正確には非該当は要介護度の一レベルではなくテーブル設計から見直す必要がある。
     */
    public function isNonApplicable(): bool
    {
        return $this->careLevelId === self::NOT_APPLICABLE_CARE_LEVEL_ID;
    }
}
