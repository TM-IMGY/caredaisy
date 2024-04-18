<?php

namespace App\Lib\Entity;

/**
 * 傷病名情報クラス。
 */
class InjuriesSickness
{
    private string $endDate;

    private int $facilityUserId;

    private int $id;

    /**
     * @var InjuriesSicknessDetail[] 傷病名詳細
     */
    private array $injuriesSicknessDetails;

    private string $startDate;

    /**
     * コンストラクタ。
     * 各引数の詳しい知識はプロパティの方に記載する。
     * @param string $endDate
     * @param int $facilityUserId
     * @param int $id
     * @param InjuriesSicknessDetail[] $injuriesSicknessDetails 傷病名詳細
     * @param string $startDate
     */
    public function __construct(
        string $endDate,
        int $facilityUserId,
        int $id,
        array $injuriesSicknessDetails,
        string $startDate
    ) {
        $this->endDate = $endDate;
        $this->facilityUserId = $facilityUserId;
        $this->id = $id;
        $this->injuriesSicknessDetail = $injuriesSicknessDetails;
        $this->startDate = $startDate;
    }

    /**
     * 傷病名詳細を返す。
     * @param int $specialMedicalCodeId 特別診療コードID
     * @return ?InjuriesSicknessDetail
     */
    public function findDetail(int $specialMedicalCodeId): ?InjuriesSicknessDetail
    {
        foreach ($this->injuriesSicknessDetail as $detail) {
            if ($detail->hasSpecialMedicalCodeId($specialMedicalCodeId)) {
                return $detail;
            }
        }
        // 見つからない場合はnullを返す。
        return null;
    }

    /**
     * 施設利用者IDを返す。
     * @return int
     */
    public function getFacilityUserId(): int
    {
        return $this->facilityUserId;
    }

    /**
     * 名前を返す。
     * @param int $specialMedicalCodeId 特別診療コードID
     * @return ?string
     */
    public function getName(int $specialMedicalCodeId): ?string
    {
        foreach ($this->injuriesSicknessDetail as $detail) {
            if ($detail->hasSpecialMedicalCodeId($specialMedicalCodeId)) {
                return $detail->getName();
            }
        }
        // 見つからない場合はnullを返す。
        return null;
    }
}
