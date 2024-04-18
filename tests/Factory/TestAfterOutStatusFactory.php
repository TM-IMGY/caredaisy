<?php

namespace Tests\Factory;

use App\Lib\Entity\AfterOutStatus;

/**
 * テスト用の退去後の状況のファクトリ。
 */
class TestAfterOutStatusFactory
{
    /**
     * 居宅で生成する。
     */
    public function generateResidence(): AfterOutStatus
    {
        return new AfterOutStatus(
            // after_out_status
            1,
            // after_out_status_id
            1,
            // after_out_status_end_date
            '9999/12/31',
            // after_out_status_name
            '居宅',
            // after_out_status_start_date
            '2021/8/1'
        );
    }

    /**
     * 死亡で作成する。
     */
    public function generateDeath(): AfterOutStatus
    {
        return new AfterOutStatus(
            // after_out_status
            3,
            // after_out_status_id
            4,
            // after_out_status_end_date
            '9999/12/31',
            // after_out_status_name
            '死亡',
            // after_out_status_start_date
            '2021/8/1'
        );
    }
}
