<?php

namespace App\Lib\ValueObject\NationalHealthBilling;

/**
 * 実績フラグ。
 */
class ResultFlag
{
    /**
     * 日割対象日
     * 常に31文字。
     * サービスの有無を0か1かのフラグで表現する(介護医療院では0から9)。
     * 最大を31日として日数が足りない場合は0で埋められる。
     * 例えば2月1日と2月28日にサービスを提供した場合は「1000000000000000000000000001000」になる。
     */
    private string $dateDailyRate;

    /**
     * 基本のルールは $dateDailyRate と同じ。
     * こちらは1月前の情報を持つ。
     * 看取りのサービスコードなど対象年月外に実績を持ちうるもので必要になる。
     */
    private string $dateDailyRateOneMonthAgo;

    /**
     * 基本のルールは $dateDailyRate と同じ。
     * こちらは2月前の情報を持つ。
     * 看取りのサービスコードなど対象年月外に実績を持ちうるもので必要になる。
     */
    private string $dateDailyRateTwoMonthAgo;

    /**
     * 回数／日数
     * 日割対象日から必ずしも推定できないので別個に持つ必要がある。
     */
    private int $serviceCountDate;

    public function __construct(
        string $dateDailyRate,
        string $dateDailyRateOneMonthAgo,
        string $dateDailyRateTwoMonthAgo,
        int $serviceCountDate
    ) {
        $this->dateDailyRate = $dateDailyRate;
        $this->dateDailyRateOneMonthAgo = $dateDailyRateOneMonthAgo;
        $this->dateDailyRateTwoMonthAgo = $dateDailyRateTwoMonthAgo;
        $this->serviceCountDate = $serviceCountDate;
    }

    public function getDateDailyRate(): string
    {
        return $this->dateDailyRate;
    }

    /**
     * 日割対象日を全て返す。
     * @return string[] 対象年月降順。
     */
    public function getDateDailyRates(): array
    {
        return [$this->dateDailyRate, $this->dateDailyRateOneMonthAgo, $this->dateDailyRateTwoMonthAgo];
    }

    public function getDateDailyRateOneMonthAgo(): string
    {
        return $this->dateDailyRateOneMonthAgo;
    }

    public function getDateDailyRateTwoMonthAgo(): string
    {
        return $this->dateDailyRateTwoMonthAgo;
    }

    public function getServiceCountDate(): int
    {
        return $this->serviceCountDate;
    }

    /**
     * 1日でもサービスを受けたかを返す。
     */
    public function isOfferdService(): bool
    {
        return strpos($this->dateDailyRate, '1') !== false;
    }
}
