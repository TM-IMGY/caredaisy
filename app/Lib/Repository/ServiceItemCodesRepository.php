<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\ServiceItemCodesRepositoryInterface;
use App\Lib\Entity\ServiceItemCode;
use App\Lib\Entity\ServiceItemCodes;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DB;

/**
 * サービス項目コードの集まりのリポジトリクラス。
 */
class ServiceItemCodesRepository implements ServiceItemCodesRepositoryInterface
{
    /**
     * サービス項目コードを返す。
     * @param int[] $serviceItemCodeIds サービス項目コードのID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ServiceItemCodes
     */
    public function get(array $serviceItemCodeIds, int $year, int $month): ServiceItemCodes
    {
        $targetYmStartDate = "${year}-${month}-1";
        $targetYmEndDate = (new Carbon($targetYmStartDate))->endOfMonth()->format('Y-m-d');

        $records = DB::table('m_service_codes')
            ->whereDate('service_start_date', '<=', $targetYmEndDate)
            ->whereDate('service_end_date', '>=', $targetYmStartDate)
            ->whereIn('service_item_code_id', $serviceItemCodeIds)
            ->orderBy('service_item_code_id', 'asc')
            ->get();

        $data = [];
        foreach ($records as $record) {
            $data[] = new ServiceItemCode(
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
        }

        return new ServiceItemCodes($data);
    }

    public function getByServiceItemCodes(string $typeCode, array $itemCodes, int $year, int $month): ServiceItemCodes
    {
        $targetYmStartDate = "${year}-${month}-1";
        $targetYmEndDate = (new CarbonImmutable($targetYmStartDate))->endOfMonth()->format('Y-m-d');

        $records = DB::table('m_service_codes')
            ->whereDate('service_start_date', '<=', $targetYmEndDate)
            ->whereDate('service_end_date', '>=', $targetYmStartDate)
            ->where('service_type_code', $typeCode)
            ->whereIn('service_item_code', $itemCodes)
            ->orderBy('service_item_code_id', 'asc')
            ->get();

        // サービスコードを作成する。
        $data = [];
        foreach ($records as $record) {
            $data[] = new ServiceItemCode(
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
        }

        return new ServiceItemCodes($data);
    }
}
