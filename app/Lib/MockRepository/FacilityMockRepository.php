<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityRepositoryInterface;
use App\Lib\Entity\Facility;
use App\Lib\Entity\FacilityService;
use App\Lib\MockRepository\DataSets\FacilityDataSets;

/**
 * 事業所のモックリポジトリ。
 */
class FacilityMockRepository implements FacilityRepositoryInterface
{
    /**
     * 事業所を返す。
     * @param int $facilityId 事業所ID
     * @return ?Facility
     */
    public function find(int $facilityId): ?Facility
    {
        $dataSets = FacilityDataSets::get();
        $facilityRecord = null;
        foreach ($dataSets as $record) {
            if ($record['facility_id'] === $facilityId) {
                $facilityRecord = $record;
                break;
            }
        }

        // 事業所のサービスを作成する。
        $facilityServiceRecords = $facilityRecord['services'];
        $facilityServices = [];
        foreach ($facilityServiceRecords as $facilityServiceRecord) {
            $facilityServices[] = new FacilityService(
                $facilityServiceRecord['area'],
                $facilityServiceRecord['area_unit_price_1'],
                $facilityServiceRecord['area_unit_price_2'],
                $facilityServiceRecord['area_unit_price_3'],
                $facilityServiceRecord['area_unit_price_4'],
                $facilityServiceRecord['area_unit_price_5'],
                $facilityServiceRecord['area_unit_price_6'],
                $facilityServiceRecord['area_unit_price_7'],
                $facilityServiceRecord['area_unit_price_8'],
                $facilityServiceRecord['area_unit_price_9'],
                $facilityServiceRecord['area_unit_price_10'],
                $facilityServiceRecord['change_date'],
                $facilityServiceRecord['facility_id'],
                $facilityServiceRecord['first_plan_input'],
                $facilityServiceRecord['id'],
                $facilityServiceRecord['service_end_date'],
                $facilityServiceRecord['service_start_date'],
                $facilityServiceRecord['service_type_code'],
                $facilityServiceRecord['service_type_code_id'],
                $facilityServiceRecord['service_type_name']
            );
        }

        // 事業所を作成する。
        $facility = new Facility(
            $facilityRecord['abbreviation'],
            $facilityRecord['allow_transmission'],
            $facilityRecord['area'],
            $facilityRecord['facility_id'],
            $facilityRecord['facility_manager'],
            $facilityRecord['facility_name_kana'],
            $facilityRecord['facility_name_kanji'],
            $facilityRecord['facility_number'],
            $facilityRecord['fax_number'],
            $facilityRecord['insurer_no'],
            $facilityRecord['invalid_flag'],
            $facilityRecord['institution_id'],
            $facilityRecord['location'],
            $facilityRecord['phone_number'],
            $facilityRecord['postal_code'],
            $facilityRecord['remarks'],
            $facilityServices
        );

        return $facility;
    }
}
