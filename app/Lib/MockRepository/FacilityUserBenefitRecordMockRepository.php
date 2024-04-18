<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserBenefitRecordRepositoryInterface;
use App\Lib\Entity\FacilityUserBenefit;
use App\Lib\Entity\FacilityUserBenefitRecord;
use App\Lib\MockRepository\DataSets\FacilityUserBenefitRecordDataSets;

/**
 * 施設利用者の給付率の記録のモックリポジトリクラス。
 */
class FacilityUserBenefitRecordMockRepository implements FacilityUserBenefitRecordRepositoryInterface
{
    /**
     * 施設利用者の給付率を返す。
     * TODO: 対象年月で絞っていない。
     * @param int $facilityUserId 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserBenefitRecord
     */
    public function find(int $facilityUserId, int $year, int $month): FacilityUserBenefitRecord
    {
        // 施設利用者の給付率を全て取得する。
        $dataSets = FacilityUserBenefitRecordDataSets::get();
        $facilityUserBenefits = [];
        foreach ($dataSets as $record) {
            if ($record['facility_user_id'] !== $facilityUserId) {
                continue;
            }

            $facilityUserBenefit = new FacilityUserBenefit(
                $record['benefit_information_id'],
                $record['benefit_rate'],
                $record['benefit_type'],
                $record['effective_start_date'],
                $record['expiry_date'],
                $record['facility_user_id']
            );
            $facilityUserBenefits[] = $facilityUserBenefit;
        }

        $facilityUserBenefitRecord = new FacilityUserBenefitRecord($facilityUserId, $facilityUserBenefits);

        return $facilityUserBenefitRecord;
    }
}
