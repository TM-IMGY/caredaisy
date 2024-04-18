<?php

namespace App\Lib\Repository;

use App\Lib\Entity\BasicRemark;
use Carbon\CarbonImmutable;
use DB;

/**
 * 基本摘要のリポジトリクラス。
 * TODO: プロジェクト構造のチームの最終的なアサインが取れていないためApp\Lib配下に置かれることになっている。
 */
class BasicRemarkRepository
{
    /**
     * 基本摘要を返す。
     * @param array $facilityUserIds
     * @param int $year
     * @param int $month
     * @return BasicRemark[]
     */
    public function get(array $facilityUserIds, int $year, int $month): array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $records = DB::table('basic_remarks')
            ->whereIn('facility_user_id', $facilityUserIds)
            ->whereDate('start_date', '<=', $targetMonthEndDate)
            ->whereDate('end_date', '>=', $targetMonthStartDate)
            ->orderBy('facility_user_id', 'asc')
            ->get();

        // 基本摘要を作成する。
        $basicRemarks = [];
        foreach ($records as $record) {
            $basicRemark = new BasicRemark(
                $record->dpc_code,
                $record->end_date,
                $record->facility_user_id,
                $record->id,
                $record->start_date,
                $record->user_circumstance_code
            );
            $basicRemarks[] = $basicRemark;
        }

        return $basicRemarks;
    }
}
