<?php

namespace App\Lib\Entity;

/**
 * 基本摘要クラス。
 */
class BasicRemark
{
    /**
     * @var string DPCコード
     */
    private string $dpcCode;

    /**
     * @var string 適用終了日
     */
    private string $endDate;

    private int $facilityUserId;

    private int $id;

    /**
     * @var string 適用開始日
     */
    private string $startDate;

    /**
     * @var ?string 利用者状況等コード
     */
    private ?string $userCircumstanceCode;

    /**
     * コンストラクタ。
     * 各引数の詳しい知識はプロパティの方に記載する。
     * @param string $dpcCode DPCコード
     * @param string $endDate 適用終了日
     * @param int $facilityUserId 適用開始日
     * @param int $id
     * @param string $startDate
     * @param ?string $userCircumstanceCode 利用者状況等コード
     */
    public function __construct(
        string $dpcCode,
        string $endDate,
        int $facilityUserId,
        int $id,
        string $startDate,
        ?string $userCircumstanceCode
    ) {
        $this->dpcCode = $dpcCode;
        $this->endDate = $endDate;
        $this->facilityUserId = $facilityUserId;
        $this->id = $id;
        $this->startDate = $startDate;
        $this->userCircumstanceCode = $userCircumstanceCode;
    }

    /**
     * DPCコードを返す。
     * @return string
     */
    public function getDpcCode(): string
    {
        return $this->dpcCode;
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
     * 施設利用者状況等コードを返す。
     * @return ?string
     */
    public function getUserCircumstanceCode(): ?string
    {
        return $this->userCircumstanceCode;
    }

    /**
     * 施設利用者状況等コードをもつかを返す。
     * @return bool
     */
    public function hasUserCircumstanceCode(): bool
    {
        return $this->userCircumstanceCode !== null;
    }
}
