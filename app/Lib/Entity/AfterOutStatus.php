<?php

namespace App\Lib\Entity;

/**
 * 退去状況。
 */
class AfterOutStatus
{
    // 居宅。
    public const RESIDENCE = 1;

    private int $afterOutStatus;
    private int $afterOutStatusId;
    private ?string $afterOutStatusEndDate;
    private string $afterOutStatusName;
    private string $afterOutStatusStartDate;

    public function __construct(
        int $afterOutStatus,
        int $afterOutStatusId,
        ?string $afterOutStatusEndDate,
        string $afterOutStatusName,
        string $afterOutStatusStartDate
    ) {
        $this->afterOutStatus = $afterOutStatus;
        $this->afterOutStatusId = $afterOutStatusId;
        $this->afterOutStatusEndDate = $afterOutStatusEndDate;
        $this->afterOutStatusName = $afterOutStatusName;
        $this->afterOutStatusStartDate = $afterOutStatusStartDate;
    }

    /**
     * 退去後の状況を返す。
     */
    public function getAfterOutStatus(): ?string
    {
        return $this->afterOutStatus;
    }

    /**
     * 居宅かを返す。
     */
    public function isResidence(): ?string
    {
        return $this->afterOutStatus === self::RESIDENCE;
    }
}
