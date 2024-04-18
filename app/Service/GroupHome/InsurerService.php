<?php

namespace App\Service\GroupHome;

use App\Models\InsurerMaster;

class InsurerService
{
    /**
     * 保険者情報を取得して返す
     * @param $insurerNo 保険者番号
     * @param $year 年
     * @param $month 月
     * @throws
     * @return array
     */
    public function get($insurerNo, $year, $month) : array
    {
        // 大した記述ではないがコントローラーに直接記載するよりはましなのでこちらに記載する。
        $insurerMaster = new InsurerMaster();
        $data = $insurerMaster->getTargetYm($insurerNo, $year, $month);
        return $data;
    }
}
