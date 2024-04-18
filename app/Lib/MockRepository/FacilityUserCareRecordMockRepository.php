<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserCareRecordRepositoryInterface;
use App\Lib\Entity\CareLevel;
use App\Lib\Entity\FacilityUserCare;
use App\Lib\Entity\FacilityUserCareRecord;
use App\Lib\MockRepository\DataSets\FacilityUserCareRecordDataSets;

/**
 * 施設利用者の介護情報の記録のモックリポジトリのクラス。
 */
class FacilityUserCareRecordMockRepository implements FacilityUserCareRecordRepositoryInterface
{
    /**
     * 施設利用者の介護情報の記録を返す。getのラッパー。
     * TODO: 対象年月で絞っていない。
     * @param int $facilityUserId 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserCareRecord
     */
    public function find(int $facilityUserId, int $year, int $month): FacilityUserCareRecord
    {
        $facilityUserCareRecords = $this->get([$facilityUserId], $year, $month);
        return $facilityUserCareRecords[0];
    }

    /**
     * 施設利用者を返す。
     * TODO: 対象年月で絞っていない。
     * @param array $facilityUserIds 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserCareRecord[]
     */
    public function get(array $facilityUserIds, int $year, int $month): array
    {
        // 指定の施設利用者ごとに介護情報の記録を作成する。
        $dataSets = FacilityUserCareRecordDataSets::get();
        $facilityUserCareRecords = [];
        foreach ($facilityUserIds as $facilityUserId) {
            // 指定の施設利用者の介護情報の記録を作成する。
            $facilityUserCares = [];
            foreach ($dataSets as $record) {
                if ($record['facility_user_id'] !== $facilityUserId) {
                    continue;
                }

                // 介護情報クラスを作成する。
                $careLevel = new CareLevel(
                    $record['care_level_id'],
                    $record['care_level'],
                    $record['care_level_name'],
                    $record['classification_support_limit_units']
                );

                $facilityUserCare = new FacilityUserCare(
                    $careLevel,
                    $record['care_period_end'],
                    $record['care_period_start'],
                    $record['certification_status'],
                    $record['date_confirmation_insurance_card'],
                    $record['date_qualification'],
                    $record['facility_user_id'],
                    $record['recognition_date'],
                    $record['user_care_info_id']
                );

                $facilityUserCares[] = $facilityUserCare;
            }
            $facilityUserCareRecords[] = new FacilityUserCareRecord($facilityUserId, $facilityUserCares);
        }

        return $facilityUserCareRecords;
    }
}
