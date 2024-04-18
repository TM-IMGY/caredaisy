<?php

namespace App\Lib\Entity;

/**
 * 入居前状況。
 */
class BeforeInStatus
{
    private int $beforeInStatus;
    private int $beforeInStatusId;
    private string $beforeInStatusName;
    private ?string $beforeInStatusesEndDate;
    private string $beforeInStatusesStartDate;

    public function __construct(
        int $beforeInStatus,
        int $beforeInStatusId,
        string $beforeInStatusName,
        ?string $beforeInStatusesEndDate,
        string $beforeInStatusesStartDate
    ) {
        $this->beforeInStatus = $beforeInStatus;
        $this->beforeInStatusId = $beforeInStatusId;
        $this->beforeInStatusName = $beforeInStatusName;
        $this->beforeInStatusesEndDate = $beforeInStatusesEndDate;
        $this->beforeInStatusesStartDate = $beforeInStatusesStartDate;
    }

    public function getBeforeInStatus(): int
    {
        return $this->beforeInStatus;
    }
}
