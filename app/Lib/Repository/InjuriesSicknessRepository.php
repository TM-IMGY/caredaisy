<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\InjuriesSicknessRepositoryInterface;
use App\Lib\Entity\InjuriesSickness;
use App\Lib\Entity\InjuriesSicknessDetail;
use App\Lib\Entity\InjuriesSicknessRelation;
use Carbon\CarbonImmutable;
use DB;

/**
 * 傷病名のリポジトリクラス。
 */
class InjuriesSicknessRepository implements InjuriesSicknessRepositoryInterface
{
    /**
     * 指定の傷病名を返す。
     * @param int $facilityUserId
     * @param int $year
     * @param int $month
     * @return ?InjuriesSickness
     */
    public function find(int $facilityUserId, int $year, int $month): ?InjuriesSickness
    {
        $injuriesSicknesses = $this->get([$facilityUserId], $year, $month);
        if (count($injuriesSicknesses) === 0) {
            return null;
        }
        return $injuriesSicknesses[0];
    }

    /**
     * 傷病名を返す。
     * @param array $facilityUserIds
     * @param int $year
     * @param int $month
     * @return InjuriesSickness[]
     */
    public function get(array $facilityUserIds, int $year, int $month): array
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $injuryRecords = DB::table('injuries_sicknesses')
            ->whereIn('facility_user_id', $facilityUserIds)
            ->whereDate('start_date', '<=', $targetMonthEndDate)
            ->whereDate('end_date', '>=', $targetMonthStartDate)
            ->orderBy('facility_user_id', 'asc')
            ->get();

        // 傷病名レコードのIDを全て取得する。
        $injuryRecordIds = $injuryRecords->pluck('id');

        // 傷病名詳細レコードを全て取得する。
        $detailRecords = DB::table('injuries_sickness_details')
            ->whereIn('injuries_sicknesses_id', $injuryRecordIds)
            ->get();

        // 傷病名詳細レコードのIDを全て取得する。
        $detailRecordIds = $detailRecords->pluck('id');

        // 傷病名関連レコードを取得する。
        $relationRecords = DB::table('injuries_sickness_relations')
            ->whereIn('injuries_sicknesses_detail_id', $detailRecordIds)
            ->get();

        // 作成する傷病名を確保する領域を宣言する。
        $injuriesSicknesses = [];

        foreach ($injuryRecords as $injuryRecord) {
            // 傷病名レコードに紐づく傷病名詳細レコードを取得する。
            $targetDetailRecords = $detailRecords->filter(function ($detailRecord, $index) use ($injuryRecord) {
                return $injuryRecord->id === $detailRecord->injuries_sicknesses_id;
            });

            // 作成する傷病名詳細を確保する領域を宣言する。
            $details = [];
            foreach ($targetDetailRecords as $detailRecord) {
                // 傷病名詳細レコードに紐づく傷病名関連レコードを取得する。
                $targetRelationRecords = $relationRecords->filter(function ($relationRecord, $index) use ($detailRecord) {
                        return $detailRecord->id === $relationRecord->injuries_sicknesses_detail_id;
                })
                    ->values();

                // 傷病名関連を作成する。
                $targetRelations = $targetRelationRecords->map(function ($record, $index) {
                    return new InjuriesSicknessRelation($record->id, $record->selected_position, $record->special_medical_code_id);
                });

                // 傷病名詳細を作成する。
                $detail = new InjuriesSicknessDetail($detailRecord->id, $detailRecord->group, $targetRelations->all(), $detailRecord->name);

                $details[] = $detail;
            }

            // 傷病名を作成する。
            $injuriesSickness = new InjuriesSickness(
                $injuryRecord->end_date,
                $injuryRecord->facility_user_id,
                $injuryRecord->id,
                $details,
                $injuryRecord->start_date
            );

            $injuriesSicknesses[] = $injuriesSickness;
        }

        return $injuriesSicknesses;
    }
}
