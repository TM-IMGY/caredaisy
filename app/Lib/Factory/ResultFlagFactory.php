<?php

namespace App\Lib\Factory;

use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Carbon\Carbon;

class ResultFlagFactory
{
    /**
     * 初期状態を生成する。
     */
    public function generateInitial(): ResultFlag
    {
        return new ResultFlag(str_repeat('0', 31), str_repeat('0', 31), str_repeat('0', 31), 0);
    }

    /**
     * 月ごとに提供されるものを生成する。
     */
    public function generatePerMonth(): ResultFlag
    {
        return new ResultFlag('1'.str_repeat('0', 30), str_repeat('0', 31), str_repeat('0', 31), 1);
    }

    /**
     * 対象年月のみに提供されるものを生成する。
     */
    public function generateTargetYm(string $dateDailyRate, int $serviceCountDate): ResultFlag
    {
        $resultFlag = new ResultFlag($dateDailyRate, str_repeat('0', 31), str_repeat('0', 31), $serviceCountDate);
        return $resultFlag;
    }

    /**
     * 前月のみに提供されるものを生成する。
     * @param string $dateDailyRate 日割対象日
     */
    public function generateOneMonthAgo(string $dateDailyRate): ResultFlag
    {
        $serviceCountDate = mb_substr_count($dateDailyRate, '1');
        $resultFlag = new ResultFlag(str_repeat('0', 31), $dateDailyRate, str_repeat('0', 31), $serviceCountDate);
        return $resultFlag;
    }
}
