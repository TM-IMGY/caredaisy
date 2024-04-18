<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityRepositoryInterface;
use App\Lib\Entity\Facility;
use App\Lib\Entity\FacilityService;
use DB;

/**
 * 事業所のリポジトリクラス。
 * TODO: プロジェクト構造のチームの最終的なアサインが取れていないためApp\Lib配下に置かれることになっている。
 */
class FacilityRepository implements FacilityRepositoryInterface
{
    /**
     * 事業所を返す。
     * @param int $facilityId 事業所ID
     * @return ?Facility
     */
    public function find(int $facilityId): ?Facility
    {
        // 事業所レコードを取得する。
        $facilityRecord = DB::table('i_facilities')->where('facility_id', $facilityId)->first();

        // 事業所が見つからない場合はnullを返す。
        if ($facilityRecord === null) {
            return null;
        }

        // 事業所のサービスレコードを取得する。
        $facilityServiceRecords = DB::table('i_services')
            ->where('facility_id', $facilityId)
            ->join('m_service_types', 'i_services.service_type_code_id', '=', 'm_service_types.service_type_code_id')
            ->get();

        // 事業所のサービスを作成する。
        $facilityServices = [];
        foreach ($facilityServiceRecords as $record) {
            $facilityServices[] = new FacilityService(
                $record->area,
                $record->area_unit_price_1,
                $record->area_unit_price_2,
                $record->area_unit_price_3,
                $record->area_unit_price_4,
                $record->area_unit_price_5,
                $record->area_unit_price_6,
                $record->area_unit_price_7,
                $record->area_unit_price_8,
                $record->area_unit_price_9,
                $record->area_unit_price_10,
                $record->change_date,
                $record->facility_id,
                $record->first_plan_input,
                $record->id,
                $record->service_end_date,
                $record->service_start_date,
                $record->service_type_code,
                $record->service_type_code_id,
                $record->service_type_name
            );
        }

        // 事業所を作成する。
        $facility = new Facility(
            $facilityRecord->abbreviation,
            $facilityRecord->allow_transmission,
            $facilityRecord->area,
            $facilityRecord->facility_id,
            $facilityRecord->facility_manager,
            $facilityRecord->facility_name_kana,
            $facilityRecord->facility_name_kanji,
            $facilityRecord->facility_number,
            $facilityRecord->fax_number,
            $facilityRecord->insurer_no,
            $facilityRecord->invalid_flag,
            $facilityRecord->institution_id,
            $facilityRecord->location,
            $facilityRecord->phone_number,
            $facilityRecord->postal_code,
            $facilityRecord->remarks,
            $facilityServices
        );

        return $facility;
    }
}
