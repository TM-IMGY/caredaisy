<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\SpecialMedicalCodesRepositoryInterface;
use App\Lib\Entity\SpecialMedicalCode;
use App\Lib\Entity\SpecialMedicalCodes;
use Carbon\CarbonImmutable;
use DB;

/**
 * 特別診療費コードの集まりのリポジトリクラス。
 */
class SpecialMedicalCodesRepository implements SpecialMedicalCodesRepositoryInterface
{
    /**
     * 特別診療費コードを返す。
     * @param array $specialMedicalCodeIds
     * @param int $year
     * @param int $month
     * @return ?SpecialMedicalCodes
     */
    public function get(array $specialMedicalCodeIds, int $year, int $month): ?SpecialMedicalCodes
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $records = DB::table('special_medical_codes')
            ->whereDate('start_date', '<=', $targetMonthEndDate)
            ->whereDate('end_date', '>=', $targetMonthStartDate)
            ->whereIn('id', $specialMedicalCodeIds)
            // 使用されないカラムが多過ぎるためselectするカラムを制限している。
            ->select([
                'history_num',
                'id',
                'identification_num',
                'service_type_code',
                'special_medical_name',
                'start_date',
                'end_date',
                'unit'
            ])
            ->orderBy('id', 'asc')
            ->get();

        // 特別診療費コードを作成する。
        $specialMedicalCodes = [];
        foreach ($records as $record) {
            $specialMedicalCodes[] = new SpecialMedicalCode(
                $record->end_date,
                $record->history_num,
                $record->id,
                $record->identification_num,
                $record->service_type_code,
                $record->special_medical_name,
                $record->start_date,
                $record->unit
            );
        }

        return new SpecialMedicalCodes($specialMedicalCodes);
    }
}
