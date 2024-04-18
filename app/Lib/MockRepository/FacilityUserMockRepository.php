<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserRepositoryInterface;
use App\Lib\Entity\AfterOutStatus;
use App\Lib\Entity\BeforeInStatus;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\InsuredNo;
use App\Lib\MockRepository\DataSets\FacilityUserDataSets;

/**
 * 施設利用者のモックリポジトリクラス。
 */
class FacilityUserMockRepository implements FacilityUserRepositoryInterface
{
    /**
     * 施設利用者を返す。getのラッパー。
     * TODO: 対象年月で絞っていない。
     * @param int $facilityUserId 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ?FacilityUser
     */
    public function find(int $facilityUserId, int $year, int $month): ?FacilityUser
    {
        $facilityUsers = $this->get([$facilityUserId], $year, $month);
        if (count($facilityUsers) === 0) {
            return null;
        }
        return $facilityUsers[0];
    }

    /**
     * 施設利用者を返す。
     * TODO: 対象年月で絞っていない。
     * @param array $facilityUserIds 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUser[]
     */
    public function get(array $facilityUserIds, int $year, int $month): ?array
    {
        // 指定の施設利用者を作成する。
        $dataSets = FacilityUserDataSets::get();
        $facilityUsers = [];
        foreach ($dataSets as $record) {
            $facilityUserId = $record['facility_user_id'];
            if (!in_array($facilityUserId, $facilityUserIds)) {
                continue;
            }

            // 施設利用者の退去状況を取得する。
            $afterOutStatus = null;
            $afterOutStatusRecord = $record['after_out_status'];
            if ($afterOutStatusRecord !== null) {
                $afterOutStatus = new AfterOutStatus(
                    $afterOutStatusRecord['after_out_status'],
                    $afterOutStatusRecord['after_out_status_id'],
                    $afterOutStatusRecord['after_out_status_end_date'],
                    $afterOutStatusRecord['after_out_status_name'],
                    $afterOutStatusRecord['after_out_status_start_date']
                );
            }

            // 施設利用者の入居状況を取得する。
            $beforeInStatusRecord = $record['before_in_status'];
            $beforeInStatus = new BeforeInStatus(
                $beforeInStatusRecord['before_in_status'],
                $beforeInStatusRecord['before_in_status_id'],
                $beforeInStatusRecord['before_in_status_name'],
                $beforeInStatusRecord['before_in_statuses_end_date'],
                $beforeInStatusRecord['before_in_statuses_start_date']
            );

            $insuredNo = new InsuredNo($record['insured_no']);

            $facilityUser = new FacilityUser(
                $afterOutStatus,
                $beforeInStatus,
                $record['birth_day'],
                $record['blood_type'],
                $record['cell_phone_number'],
                $record['consent_date'],
                $record['consenter'],
                $record['consenter_phone_number'],
                $record['death_date'],
                $record['death_reason'],
                $record['diagnosis_date'],
                $record['diagnostician'],
                $record['end_date'],
                $record['facility_user_id'],
                $record['first_name'],
                $record['first_name_kana'],
                $record['gender'],
                $insuredNo,
                $record['insurer_no'],
                $record['invalid_flag'],
                $record['last_name'],
                $record['last_name_kana'],
                $record['location1'],
                $record['location2'],
                $record['phone_number'],
                $record['postal_code'],
                $record['remarks'],
                $record['rh_type'],
                $record['spacial_address_flag'],
                $record['start_date']
            );
            $facilityUsers[] = $facilityUser;
        }

        return $facilityUsers;
    }
}
