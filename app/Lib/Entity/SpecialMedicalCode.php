<?php

namespace App\Lib\Entity;

/**
 * 特別診療コードクラス。
 * 国指定のものに利用されないものが多いのでプロパティを絞っている。必要であれば追加をする。
 */
class SpecialMedicalCode
{
    private ?string $endDate;
    private ?string $historyNum;
    private int $id;

    /**
     * @var ?string 識別番号
     */
    private ?string $identificationNum;

    private ?string $serviceTypeCode;
    private ?string $specialMedicalName;
    private ?string $startDate;
    private ?int $unit;

    /**
     * コンストラクタ
     * @param ?string $endDate
     * @param ?string $historyNum
     * @param int $id
     * @param ?string $identificationNum
     * @param ?string $serviceTypeCode
     * @param ?string $specialMedicalName
     * @param ?string $startDate
     * @param ?int $unit
     */
    public function __construct(
        ?string $endDate,
        ?string $historyNum,
        int $id,
        ?string $identificationNum,
        ?string $serviceTypeCode,
        ?string $specialMedicalName,
        ?string $startDate,
        ?int $unit
    ) {
        $this->endDate = $endDate;
        $this->historyNum = $historyNum;
        $this->id = $id;
        $this->identificationNum = $identificationNum;
        $this->serviceTypeCode = $serviceTypeCode;
        $this->specialMedicalName = $specialMedicalName;
        $this->startDate = $startDate;
        $this->unit = $unit;
    }

    /**
     * IDを返す。
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * 識別番号を返す。
     * @return ?string
     */
    public function getIdentificationNum(): ?string
    {
        return $this->identificationNum;
    }

    /**
     * @return int
     */
    public function getSpecialMedicalCodeId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSpecialMedicalName(): string
    {
        return $this->specialMedicalName;
    }

    /**
     * @return ?int
     */
    public function getUnit(): ?int
    {
        return $this->unit;
    }
}
