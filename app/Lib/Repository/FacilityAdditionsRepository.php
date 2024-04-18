<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityAdditionsRepositoryInterface;
use App\Lib\Entity\FacilityAddition;
use App\Lib\Entity\FacilityAdditions;
use App\Lib\Entity\ServiceItemCode;
use Carbon\CarbonImmutable;
use DB;

/**
 * 事業所加算の集まりのリポジトリクラス。
 * テーブル名がm_facility_additionsとなっているがマスタという訳ではなくユーザーが情報を登録していく。
 */
class FacilityAdditionsRepository implements FacilityAdditionsRepositoryInterface
{
    // 事業所加算で除外するサービス項目コードID。
    // TODO: これが仕様によるものなのかテーブル構造の都合の問題なのかはっきりさせる。
    public const EXCLUSION_SERVICE_ITEM_CODE_IDS = ['47', '48'];

    /**
     * 事業所加算の集まりを返す。
     * @param int $facilityId 事業所ID
     * @param int $serviceTypeCodeId サービス種類コードID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityAdditions
     */
    public function getByFacilityId(int $facilityId, int $serviceTypeCodeId, int $year, int $month): FacilityAdditions
    {
        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $records = DB::table('m_facility_additions')
            ->where('facility_id', $facilityId)
            ->where('service_type_code_id', $serviceTypeCodeId)
            ->whereDate('addition_start_date', '<=', $targetMonthEndDate)
            ->whereDate('addition_end_date', '>=', $targetMonthStartDate)
            ->whereDate('service_start_date', '<=', $targetMonthEndDate)
            ->whereDate('service_end_date', '>=', $targetMonthStartDate)
            ->whereNotIn('m_facility_additions.service_item_code_id', self::EXCLUSION_SERVICE_ITEM_CODE_IDS)
            ->join('m_service_codes', 'm_facility_additions.service_item_code_id', '=', 'm_service_codes.service_item_code_id')
            ->select([
                'm_facility_additions.*',
                'm_service_codes.classification_support_limit_flg',
                'm_service_codes.rank',
                'm_service_codes.service_calcinfo_1',
                'm_service_codes.service_calcinfo_2',
                'm_service_codes.service_calcinfo_3',
                'm_service_codes.service_calcinfo_4',
                'm_service_codes.service_calcinfo_5',
                'm_service_codes.service_calculation_unit',
                'm_service_codes.service_end_date',
                'm_service_codes.service_item_code',
                'm_service_codes.service_item_name',
                'm_service_codes.service_kind',
                'm_service_codes.service_start_date',
                'm_service_codes.service_synthetic_unit',
                'm_service_codes.service_type_code',
                'm_service_codes.synthetic_unit_input_flg',
            ])
            ->orderBy('service_item_code_id', 'asc')
            ->get();

        // 事業所加算を作成する。
        $facilityAdditions = [];
        foreach ($records as $record) {
            // サービス項目コードを作成する。
            $serviceItemCode = new ServiceItemCode(
                $record->classification_support_limit_flg,
                $record->rank,
                $record->service_calcinfo_1,
                $record->service_calcinfo_2,
                $record->service_calcinfo_3,
                $record->service_calcinfo_4,
                $record->service_calcinfo_5,
                $record->service_calculation_unit,
                $record->service_end_date,
                $record->service_item_code,
                $record->service_item_code_id,
                $record->service_item_name,
                $record->service_kind,
                $record->service_start_date,
                $record->service_synthetic_unit,
                $record->service_type_code,
                $record->synthetic_unit_input_flg
            );

            $facilityAdditions[] = new FacilityAddition(
                $record->addition_end_date,
                $record->addition_start_date,
                $record->facility_addition_id,
                $record->facility_id,
                $serviceItemCode,
                $record->service_type_code_id
            );
        }

        return new FacilityAdditions($facilityAdditions);
    }
}
