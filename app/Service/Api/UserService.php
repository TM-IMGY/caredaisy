<?php

namespace App\Service\Api;

use DB;
use Crypt;
use Exception;
use Log;
use App\Lib\Common\Consts;

class UserService extends ApiCommonService
{
    const CSV_VERSION = '202201';


    /**
     * $lastSynchronized以降に更新のあったサービスが紐づく利用者IDのリストを取得する
     *
     * @param   ?string $lastSynchronized
     * @param   string  $facilityId
     * @return  array
     */
    private function getTargetByServiceUpdated($lastSynchronized, $facilityId)
    {
        $list = [];

        $query = DB::table('i_user_facility_service_informations')
            ->where('facility_id', $facilityId)
            ->groupBy('facility_user_id')
            ->select([
                'facility_user_id',
            ]);

        if (!is_null($lastSynchronized)) {
            $query->where('updated_at', '>=', $lastSynchronized);
        }

        $result = $query->get();

        if (!$result->isEmpty()) {
            foreach ($result as $row) {
                $list[] = $row->facility_user_id;
            }
        }

        return $list;
    }

    /**
     * $lastSynchronized以降に更新のあった利用者IDのリストを取得する
     *
     * @param string $lastSynchronized
     * @param string $facilityId
     *
     * @return array
     */
    private function getTargetByUserCareUpdated($lastSynchronized, $facilityId)
    {
        $list = [];

        $query = DB::table('i_user_care_informations AS iuci')
            ->join('i_user_facility_informations AS iufi', 'iuci.facility_user_id', '=', 'iufi.facility_user_id')
            ->where('iufi.facility_id', $facilityId)
            ->groupBy('iuci.facility_user_id')
            ->select([
                'iuci.facility_user_id',
            ]);

        if (!is_null($lastSynchronized)) {
            $query->where('iuci.updated_at', '>=', $lastSynchronized);
        }

        $result = $query->get();

        if (!$result->isEmpty()) {
            foreach ($result as $row) {
                $list[] = $row->facility_user_id;
            }
        }

        return $list;
    }

    /**
     * $lastSynchronized以降に更新のあった利用者IDのリストを取得する
     *
     * @param string $lastSynchronized
     * @param string $facilityId
     *
     * @return array
     */
    private function getTargetByServicePlansUpdated($lastSynchronized, $facilityId)
    {
        $list = [];

        $query = DB::table('i_service_plans AS isp')
            ->join('i_user_facility_informations AS iufi', 'isp.facility_user_id', '=', 'iufi.facility_user_id')
            ->where('iufi.facility_id', $facilityId)
            ->groupBy('isp.facility_user_id')
            ->select([
                'isp.facility_user_id',
            ]);

        if (!is_null($lastSynchronized)) {
            $query->where('isp.updated_at', '>=', $lastSynchronized);
        }

        $result = $query->get();

        if (!$result->isEmpty()) {
            foreach ($result as $row) {
                $list[] = $row->facility_user_id;
            }
        }

        return $list;
    }

    /**
     * confidential情報の取得
     *
     * @param   string  $lastSynchronized
     * @param   array   $targetIds
     * @param   string  $facilityId
     * @param   string  $facilityNumber
     * @param   array   $additionals
     * @param   string  $processTime
     * @return  array
     */
    private function getConfidentialUsers($lastSynchronized, $targetIds, $facilityId, $facilityNumber, $additionals, $processTime)
    {
        $content = [
            'result_info' => [
                'result'      => 'OK',
                'result_code' => '',
                'message'     => '',
            ],
            'facility_user_count' => 0,
        ];

        $query = DB::connection('confidential')
            ->table('i_facility_users')
            ->whereIn('facility_user_id', $targetIds)
            ->select([
                'facility_user_id',
                'insurer_no',
                'insured_no',
                'last_name',
                'first_name',
                'last_name_kana',
                'first_name_kana',
                'gender',
                DB::raw('DATE_FORMAT(birthday, \'%Y%m%d\') AS birthday'),
                'postal_code',
                'location1',
                'location2',
                'phone_number',
                DB::raw('DATE_FORMAT(start_date, \'%Y%m%d\') AS start_date'),
                DB::raw('DATE_FORMAT(end_date, \'%Y%m%d\') AS end_date'),
            ]);

        // i_facility_usersのupdated_atが最終連携時刻以降または紐づくテーブルのupdated_atが最終連携時刻以降のみを取得する
        if (!empty($lastSynchronized)) {
            if (empty($additionals)) {
                $query->where('updated_at', '>=', $lastSynchronized);
            } else {
                $query->where(function ($query) use ($lastSynchronized, $additionals) {
                    $query->whereIn('facility_user_id', $additionals)
                        ->orWhere('updated_at', '>=', $lastSynchronized);
                });
            }
        }

        $result = $query->get();

        if (!$result->isEmpty()) {
            $content['facility_user_count'] = (string) $result->count();
            $content['facility_user_get'] = [];

            foreach ($result as $row) {
                $lastNameKana = $this->decrypt($row->last_name_kana);
                $firstNameKana = $this->decrypt($row->first_name_kana);
                $lastName = $this->decrypt($row->last_name);
                $firstName = $this->decrypt($row->first_name);

                $item = [
                    'care_daisy_facility_user_id' => (string) $row->facility_user_id,
                    'user_supplementary' => [
                        'csv_version' => self::CSV_VERSION,
                        'insurer_no' => $this->decrypt($row->insurer_no),
                        'insured_no' => $this->decrypt($row->insured_no),
                        'plan_update_date' => '',
                        'name_kana' => $this->convert($lastNameKana . $firstNameKana),
                        'name' => $this->convert($lastName . $firstName),
                        'gender' => (string) $this->convert($row->gender),
                        'birthday' => $this->convert($row->birthday),
                        'postal_code' => $this->decrypt($row->postal_code),
                        'location1' => $this->decrypt($row->location1),
                        'location2' => $this->decrypt($row->location2),
                        'phone_number' => $this->decrypt($row->phone_number),
                        'recognition_date' => '',
                        'credit_limit_period_start' => '',
                        'credit_limit_period_end' => '',
                        'certification_status' => '',
                        'care_level_update_date' => '',
                        'care_level' => '',
                        'classification_support_limit' => '',

                        'service_type_code_1' => '',
                        'support_limit_1' => '',
                        'total_unit_1' => '',
                        'exceeding_limit_unit_1' => '',
                        'service_type_code_2' => '',
                        'support_limit_2' => '',
                        'total_unit_2' => '',
                        'exceeding_limit_unit_2' => '',
                        'service_type_code_3' => '',
                        'support_limit_3' => '',
                        'total_unit_3' => '',
                        'exceeding_limit_unit_3' => '',
                        'service_type_code_4' => '',
                        'support_limit_4' => '',
                        'total_unit_4' => '',
                        'exceeding_limit_unit_4' => '',
                        'service_type_code_5' => '',
                        'support_limit_5' => '',
                        'total_unit_5' => '',
                        'exceeding_limit_unit_5' => '',
                        'service_type_code_6' => '',
                        'support_limit_6' => '',
                        'total_unit_6' => '',
                        'exceeding_limit_unit_6' => '',
                        'service_type_code_7' => '',
                        'support_limit_7' => '',
                        'total_unit_7' => '',
                        'exceeding_limit_unit_7' => '',
                        'service_type_code_8' => '',
                        'support_limit_8' => '',
                        'total_unit_8' => '',
                        'exceeding_limit_unit_8' => '',
                        'service_type_code_9' => '',
                        'support_limit_9' => '',
                        'total_unit_9' => '',
                        'exceeding_limit_unit_9' => '',

                        'total_exceeding_limit_unit' => '',
                        'day_use_previous_month' => '',
                        'cumulative_day_utilized' => '',
                        'before_care_level' => '',

                        // facility_number は再取得せず、
                        // $facilityNumber は、APIのパラメータを流用。
                        // facility_idが取得できているので異常値の想定は不要。
                        'consent_form_receptionist' => $facilityNumber,
                        'updater_code' => $facilityNumber,

                        'identifier' => '',
                        'targt_date' => Date('Ym', strtotime($processTime)),
                        'user_compensation' => [
                            'last_name' => $this->convert($lastName),
                            'first_name' => $this->convert($firstName),
                            'last_name_kana' => $this->convert($lastNameKana),
                            'first_name_kana' => $this->convert($firstNameKana),
                            'care_period_start' => '',
                            'care_period_end' => '',
                            'start_date' => $this->convert($row->start_date),
                            'end_date' => $this->convert($row->end_date),
                        ],
                    ],
                ];

                $content['facility_user_get'][] = $item;
            }
        }

        return $content;
    }


    /**
     * 利用者情報の連携リストを生成する
     *
     * @param   integer $facilityId
     * @param   string  $facilityNumber
     * @param   string  $employeeNumber
     * @param   string  $allGetFlg
     * @return  json
     */
    public function generateList($facilityId, $facilityNumber, $employeeNumber, $allGetFlg)
    {
        $content = null;
        $processTime = $this->getDbTimestamp();

        DB::transaction(function () use (&$content, $processTime, $employeeNumber, $facilityId, $facilityNumber, $allGetFlg) {

            // トランザクションが完了するまでi_facility_users_synchronizationをロックする。
            $sql = <<< __SQL__
                    SELECT
                        cooperation_last_date
                    FROM
                        i_facility_users_synchronization
                    WHERE
                        facility_id = :facility_id
                    FOR UPDATE
                __SQL__;

                $sql = trim(preg_replace('/\s+/', ' ', $sql));
                $row = DB::selectOne($sql, ['facility_id' => $facilityId]);

                $lastSynchronized = null;
                if (!empty($row)) {
                    $lastSynchronized = $row->cooperation_last_date;
                } else {
                    // facility_id = $facilityId のレコードがない場合はINSERTする。
                    DB::table('i_facility_users_synchronization')
                        ->insert([
                            'facility_id' => $facilityId,
                            'cooperation_last_date' => DB::raw('now()'),
                        ]);
                }

                // 全出力判定
                if ($allGetFlg == Consts::VALID) {
                    $lastSynchronized = null;
                }

            $additionals = [];

            // 利用者情報に紐づく各テーブルの更新時刻から出力対象のfacility_user_idのリストを生成する。
            $listService = $this->getTargetByServiceUpdated($lastSynchronized, $facilityId);
            $additionals = array_merge($additionals, $listService);

            $listUserCare = $this->getTargetByUserCareUpdated($lastSynchronized, $facilityId);
            $additionals = array_merge($additionals, $listUserCare);

            $listServicePlans = $this->getTargetByServicePlansUpdated($lastSynchronized, $facilityId);
            $additionals = array_merge($additionals, $listServicePlans);

            $additionals = array_unique($additionals);
            sort($additionals);

            $targetIds = $this->getFacilityUserIds($facilityId);

            $content = $this->getConfidentialUsers($lastSynchronized, $targetIds, $facilityId, $facilityNumber, $additionals, $processTime);

            if (!empty($content['facility_user_get'])) {
                foreach ($content['facility_user_get'] as &$row) {
                    $this->setPlanUpdateDate($row, $processTime);
                    $this->setCareInformation($row, $processTime);
                }
            }

            DB::table('i_facility_users_synchronization')
                ->where('facility_id', $facilityId)
                ->update([
                    'cooperation_last_date' => $processTime,
                ]);
        });

        if (is_null($content)) {
            throw new Exception('failed to generate content');
        }

        return $content;
    }


    /**
     * facility_id に紐づくfacility_user を取得する
     *
     * @param  string $facilityId
     * @return string
     */
    private function getFacilityUserIds($facilityId)
    {
        $ids = [];

        $result = DB::table('i_user_facility_informations')
            ->where('facility_id', $facilityId)
            ->select('facility_user_id')
            ->get();

        if (!$result->isEmpty()) {
            foreach ($result as $row) {
                $ids[] = $row->facility_user_id;
            }
        }

        return $ids;
    }


    /**
     * i_user_care_informationsから取得する内容をセットする
     *
     * @param   array  &$item
     * @param   string $processTime
     */
    private function setCareInformation(&$item, $processTime)
    {

        $currentDate = Date('Y-m-d', strtotime($processTime));

        $row = DB::table('i_user_care_informations AS iuci')
            ->join('m_care_levels AS msl', 'iuci.care_level_id', '=', 'msl.care_level_id')
            ->where('iuci.facility_user_id', $item['care_daisy_facility_user_id'])
            ->where('iuci.care_period_start', '<=', $currentDate)
            ->where('iuci.care_period_end', '>=', $currentDate)
            ->select([
                DB::raw('DATE_FORMAT(iuci.recognition_date, \'%Y%m%d\') AS recognition_date'),
                DB::raw('DATE_FORMAT(iuci.care_period_start, \'%Y%m%d\') AS care_period_start'),
                DB::raw('DATE_FORMAT(iuci.care_period_end, \'%Y%m%d\') AS care_period_end'),
                'iuci.certification_status',
                'msl.care_level',
                'msl.classification_support_limit_units',
            ])
            ->orderBy('iuci.care_period_start')
            ->orderBy('iuci.care_period_end')
            ->orderBy('iuci.user_care_info_id', 'DESC')
            ->first();

        if (!empty($row)) {
            $item['user_supplementary']['certification_status'] = (string) $this->convert($row->certification_status);
            $item['user_supplementary']['recognition_date'] = $this->convert($row->recognition_date);
            $item['user_supplementary']['care_level_update_date'] = $this->convert($row->recognition_date);
            $item['user_supplementary']['care_level'] = (string) $this->convert($row->care_level);
            $item['user_supplementary']['classification_support_limit'] = (string) $this->convert($row->classification_support_limit_units);
            $item['user_supplementary']['user_compensation']['care_period_start'] = $this->convert($row->care_period_start);
            $item['user_supplementary']['user_compensation']['care_period_end'] = $this->convert($row->care_period_end);
        }

        // 前月のi_user_care_informationsレコードを取得する
        $startOfPrevMonth    = Date('Y-m-01', strtotime('-1 month', strtotime($currentDate)));
        $endOfPrevMonth      = Date('Y-m-d', strtotime('-1 day', strtotime('+1 month', strtotime($startOfPrevMonth))));
        $row = DB::table('i_user_care_informations AS iuci')
            ->join('m_care_levels AS msl', 'iuci.care_level_id', '=', 'msl.care_level_id')
            ->where('iuci.facility_user_id', $item['care_daisy_facility_user_id'])
            ->where('iuci.care_period_start', '<=', $startOfPrevMonth)
            ->where('iuci.care_period_end', '>=', $endOfPrevMonth)
            ->select([
                'msl.care_level',
            ])
            ->orderBy('iuci.care_period_start')
            ->orderBy('iuci.care_period_end')
            ->orderBy('iuci.user_care_info_id', 'DESC')
            ->first();

        if (!empty($row)) {
            $item['user_supplementary']['before_care_level'] = (string) $this->convert($row->care_level);
        }
    }


    /**
     * 居宅サービス計画作成（変更）日をセットする
     *
     * @param   array  &$item
     * @param   string $processTime
     */
    private function setPlanUpdateDate(&$item, $processTime)
    {
        $currentDate = Date('Y-m-d', strtotime($processTime));

        $row = DB::table('i_service_plans')
            ->where('facility_user_id', $item['care_daisy_facility_user_id'])
            ->where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->orderBy('start_date')
            ->orderBy('end_date')
            ->orderBy('id', 'DESC')
            ->select([
                DB::raw('DATE_FORMAT(plan_start_period, \'%Y%m%d\') AS plan_start_period'),
            ])
            ->first();

        if (!empty($row)) {
            $item['user_supplementary']['plan_update_date'] = $this->convert($row->plan_start_period);
        }
    }
}
