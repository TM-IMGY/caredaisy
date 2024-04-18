<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserRepositoryInterface;
use App\Lib\Entity\AfterOutStatus;
use App\Lib\Entity\BeforeInStatus;
use App\Lib\Entity\FacilityUser;
use App\Lib\Entity\InsuredNo;
use Carbon\CarbonImmutable;
use DB;
use Illuminate\Support\Facades\Crypt;

/**
 * 施設利用者のリポジトリ。
 */
class FacilityUserRepository implements FacilityUserRepositoryInterface
{
    /**
     * 施設利用者を返す。getのラッパー。
     * @param int $facilityUserId 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
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
     * @param array $facilityUserIds 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUser[]
     */
    public function get(array $facilityUserIds, int $year, int $month): ?array
    {
        $dbName = config('database.connections.confidential.database');

        $facilityUserCount = count($facilityUserIds);
        $facilityUserIdPlaceHolder = rtrim(str_repeat('?,', $facilityUserCount), ',');
        $whereFacilityUserIds = "facility_user_id IN ( ${facilityUserIdPlaceHolder} )";

        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        $query = <<<SQL
SELECT
    after_out_status,
    after_out_status_end_date,
    fu.after_out_status_id,
    after_out_status_name,
    after_out_status_start_date,
    before_in_status,
    fu.before_in_status_id,
    before_in_status_name,
    before_in_statuses_end_date,
    before_in_statuses_start_date,
    birthday,
    blood_type,
    cell_phone_number,
    consent_date,
    consenter,
    consenter_phone_number,
    death_date,
    death_reason,
    diagnosis_date,
    diagnostician,
    end_date,
    facility_user_id,
    first_name,
    first_name_kana,
    gender,
    insured_no,
    insurer_no,
    invalid_flag,
    last_name,
    last_name_kana,
    location1,
    location2,
    phone_number,
    postal_code,
    remarks,
    rh_type,
    spacial_address_flag,
    start_date
FROM
    -- 施設利用者テーブル
    (
        SELECT
            after_out_status_id,
            before_in_status_id,
            birthday,
            blood_type,
            cell_phone_number,
            consent_date,
            consenter,
            consenter_phone_number,
            death_date,
            death_reason,
            diagnosis_date,
            diagnostician,
            end_date,
            facility_user_id,
            first_name,
            first_name_kana,
            gender,
            insured_no,
            insurer_no,
            invalid_flag,
            last_name,
            last_name_kana,
            location1,
            location2,
            phone_number,
            postal_code,
            remarks,
            rh_type,
            spacial_address_flag,
            start_date
        FROM
            {$dbName}.i_facility_users
        WHERE
            {$whereFacilityUserIds}
    ) fu
INNER JOIN
    -- 入居マスタ
    (
        SELECT
            before_in_status,
            before_in_status_id,
            before_in_status_name,
            before_in_statuses_end_date,
            before_in_statuses_start_date
        FROM
            m_before_in_statuses
        WHERE
            before_in_statuses_start_date <= ?
            AND before_in_statuses_end_date >= ?
    ) bis
ON
    fu.before_in_status_id = bis.before_in_status_id
LEFT JOIN
    -- 退去マスタ
    (
        SELECT
            after_out_status,
            after_out_status_end_date,
            after_out_status_id,
            after_out_status_name,
            after_out_status_start_date
        FROM
            m_after_out_statuses
        WHERE
            after_out_status_start_date <= ?
            AND after_out_status_end_date >= ?
    ) aos
ON
    fu.after_out_status_id = aos.after_out_status_id
ORDER BY
    fu.facility_user_id asc
SQL;

        $queryParameter = array_merge(
            $facilityUserIds,
            [$targetMonthEndDate, $targetMonthStartDate, $targetMonthEndDate, $targetMonthStartDate]
        );
        $facilityUserRecords = DB::select($query, $queryParameter);

        // 指定の施設利用者を作成する。
        $facilityUsers = [];
        foreach ($facilityUserRecords as $record) {
            // 値が必ず入っているものを復号する。
            $record->first_name = Crypt::decrypt($record->first_name);
            $record->first_name_kana = Crypt::decrypt($record->first_name_kana);
            $record->insured_no = Crypt::decrypt($record->insured_no);
            $record->insurer_no = Crypt::decrypt($record->insurer_no);
            $record->last_name = Crypt::decrypt($record->last_name);
            $record->last_name_kana = Crypt::decrypt($record->last_name_kana);

            // 値がnullの可能性があるものを復号する。
            if ($record->cell_phone_number) {
                $record->cell_phone_number = Crypt::decrypt($record->cell_phone_number);
            }
            if ($record->consenter) {
                $record->consenter = Crypt::decrypt($record->consenter);
            }
            if ($record->consenter_phone_number) {
                $record->consenter_phone_number = Crypt::decrypt($record->consenter_phone_number);
            }
            if ($record->diagnostician) {
                $record->diagnostician = Crypt::decrypt($record->diagnostician);
            }
            if ($record->location1) {
                $record->location1 = Crypt::decrypt($record->location1);
            }
            if ($record->location2) {
                $record->location2 = Crypt::decrypt($record->location2);
            }
            if ($record->phone_number) {
                $record->phone_number = Crypt::decrypt($record->phone_number);
            }
            if ($record->postal_code) {
                $record->postal_code = Crypt::decrypt($record->postal_code);
            }

            // 施設利用者の退去状況がある場合。
            $afterOutStatus = null;
            if ($record->after_out_status_id) {
                $afterOutStatus = new AfterOutStatus(
                    $record->after_out_status,
                    $record->after_out_status_id,
                    $record->after_out_status_end_date,
                    $record->after_out_status_name,
                    $record->after_out_status_start_date
                );
            }

            // 施設利用者の入居状況を取得する。
            $beforeInStatus = new BeforeInStatus(
                $record->before_in_status,
                $record->before_in_status_id,
                $record->before_in_status_name,
                $record->before_in_statuses_end_date,
                $record->before_in_statuses_start_date
            );

            $insuredNo = new InsuredNo($record->insured_no);

            $facilityUser = new FacilityUser(
                $afterOutStatus,
                $beforeInStatus,
                $record->birthday,
                $record->blood_type,
                $record->cell_phone_number,
                $record->consent_date,
                $record->consenter,
                $record->consenter_phone_number,
                $record->death_date,
                $record->death_reason,
                $record->diagnosis_date,
                $record->diagnostician,
                $record->end_date,
                $record->facility_user_id,
                $record->first_name,
                $record->first_name_kana,
                $record->gender,
                $insuredNo,
                $record->insurer_no,
                $record->invalid_flag,
                $record->last_name,
                $record->last_name_kana,
                $record->location1,
                $record->location2,
                $record->phone_number,
                $record->postal_code,
                $record->remarks,
                $record->rh_type,
                $record->spacial_address_flag,
                $record->start_date
            );
            $facilityUsers[] = $facilityUser;
        }

        return $facilityUsers;
    }
}
