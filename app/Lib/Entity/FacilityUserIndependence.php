<?php

namespace App\Lib\Entity;

/**
 * 施設利用者の自立度クラス。
 */
class FacilityUserIndependence
{
    private int $dementiaLevel;
    private int $facilityUserId;
    private int $independenceLevel;
    private ?string $judger;
    private ?string $judgmentDate;
    private int $userIndependenceInformationsId;

    /**
     * コンストラクタ
     * @param int $dementiaLevel
     * @param int $facilityUserId
     * @param int $independenceLevel
     * @param ?string $judger
     * @param ?string $judgmentDate
     * @param int $userIndependenceInformationsId
     */
    public function __construct(
        int $dementiaLevel,
        int $facilityUserId,
        int $independenceLevel,
        ?string $judger,
        ?string $judgmentDate,
        int $userIndependenceInformationsId
    ) {
        $this->dementiaLevel = $dementiaLevel;
        $this->facilityUserId = $facilityUserId;
        $this->independenceLevel = $independenceLevel;
        $this->judger = $judger;
        $this->judgmentDate = $judgmentDate;
        $this->userIndependenceInformationsId = $userIndependenceInformationsId;
    }

    /**
     * @return int
     */
    public function getDementiaLevel(): int
    {
        return $this->dementiaLevel;
    }

    /**
     * @return int
     */
    public function getIndependenceLevel(): int
    {
        return $this->independenceLevel;
    }
}
