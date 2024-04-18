<?php

namespace App\Lib\InMemoryRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityRepositoryInterface;
use App\Lib\Entity\Facility;
use App\Lib\Entity\FacilityService;

/**
 * 事業所のインメモリのリポジトリ。
 */
class FacilityInMemoryRepository implements FacilityRepositoryInterface
{
    private array $db;
    private int $facilityId;
    private int $facilityServiceId;

    public function __construct()
    {
        $this->db = [];
        $this->facilityId = 0;
        $this->facilityServiceId = 0;
    }

    /**
     * 事業所を返す。
     * @param int $facilityId 事業所ID
     * @return ?Facility
     */
    public function find(int $facilityId): ?Facility
    {
        // 事業所の情報を確保する変数。
        $facilityRecord = null;

        foreach ($this->db as $record) {
            if ($record['facility_id'] === $facilityId) {
                $facilityRecord = $record;
                break;
            }
        }

        // 事業所のサービス情報を取得する。
        $facilityServiceRecords = $facilityRecord['services'];

        // 事業所のサービスを確保する変数。
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

    /**
     * 事業所を挿入する。
     * @param string $changeDate 変更日
     */
    public function insert(
        string $changeDate
    ): int {
        $this->facilityId++;
        $this->facilityServiceId++;

        $this->db[] = [
            'abbreviation' => null,
            'allow_transmission' => 1,
            'area' => 1,
            'facility_id' => $this->facilityId,
            'facility_manager' => null,
            'facility_name_kana' => 'ジギョウショ',
            'facility_name_kanji' => '事業所',
            'facility_number' => '0000000001',
            'fax_number' => null,
            'insurer_no' => '123456',
            'invalid_flag' => 0,
            'institution_id' => 1,
            'location' => '住所',
            'phone_number' => '0000000000',
            'postal_code' => '0000000',
            'remarks' => null,
            'services' => [
                [
                    'area' => 1,
                    'area_unit_price_1' => 1090,
                    'area_unit_price_2' => 1072,
                    'area_unit_price_3' => 1068,
                    'area_unit_price_4' => 1054,
                    'area_unit_price_5' => 1045,
                    'area_unit_price_6' => 1027,
                    'area_unit_price_7' => 1014,
                    'area_unit_price_8' => 1000,
                    'area_unit_price_9' => null,
                    'area_unit_price_10' => null,
                    'change_date' => $changeDate,
                    'facility_id' => $this->facilityId,
                    'first_plan_input' => 0,
                    'id' => $this->facilityServiceId,
                    'service_end_date' => '9999/12/01',
                    'service_start_date' => '2021/04/01',
                    'service_type_code' => '32',
                    'service_type_code_id' => 1,
                    'service_type_name' => '認知症対応型共同生活介護'
                ]
            ]
        ];
        return $this->facilityId;
    }
}
