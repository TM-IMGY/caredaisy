<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserServiceRecordRepositoryInterface;
use App\Lib\Entity\FacilityUserService;
use App\Lib\Entity\FacilityUserServiceRecord;
use App\Lib\MockRepository\DataSets\FacilityUserServiceRecordDataSets;

/**
 * 施設利用者のサービス種類の記録のモックリポジトリクラス。
 */
class FacilityUserServiceRecordMockRepository implements FacilityUserServiceRecordRepositoryInterface
{
    /**
     * 施設利用者のサービスを返す。
     * TODO: 対象年月で絞っていない。
     * @param int $facilityUserId 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserServiceRecord
     */
    public function find(int $facilityUserId, int $year, int $month): FacilityUserServiceRecord
    {
        $facilityUserServiceRecords = $this->get([$facilityUserId], $year, $month);
        return $facilityUserServiceRecords[0];
    }

    /**
     * 施設利用者のサービスを返す。
     * TODO: 対象年月で絞っていない。
     * @param array $facilityUserIds 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserServiceRecord[]
     */
    public function get(array $facilityUserIds, int $year, int $month): array
    {
        // 指定の施設利用者ごとにサービスの記録を作成する。
        $dataSets = FacilityUserServiceRecordDataSets::get();
        $facilityUserServiceRecords = [];
        foreach ($facilityUserIds as $facilityUserId) {
            // 指定の施設利用者のサービスの記録を作成する。
            $facilityUserServices = [];
            foreach ($dataSets as $record) {
                if ($record['facility_user_id'] !== $facilityUserId) {
                    continue;
                }

                $facilityUserService = new FacilityUserService(
                    $record['area_unit_price_1'],
                    $record['area_unit_price_2'],
                    $record['area_unit_price_3'],
                    $record['area_unit_price_4'],
                    $record['area_unit_price_5'],
                    $record['area_unit_price_6'],
                    $record['area_unit_price_7'],
                    $record['area_unit_price_8'],
                    $record['area_unit_price_9'],
                    $record['area_unit_price_10'],
                    $record['facility_id'],
                    $record['facility_user_id'],
                    $record['service_end_date'],
                    $record['service_id'],
                    $record['service_start_date'],
                    $record['service_type_code'],
                    $record['service_type_code_id'],
                    $record['service_type_name'],
                    $record['usage_situation'],
                    $record['use_end'],
                    $record['user_facility_service_information_id'],
                    $record['use_start']
                );

                $facilityUserServices[] = $facilityUserService;
            }

            $facilityUserServiceRecords[] = new FacilityUserServiceRecord($facilityUserId, $facilityUserServices);
        }

        return $facilityUserServiceRecords;
    }
}
