<?php

namespace App\Service\Api;

use DB;
use Exception;
use Log;
use App\Utility\S3;
use App\Lib\Common\Consts;
use App\Models\ServicePlan;

class ServicePlanService extends ApiCommonService
{
    /**
     * 出力する介護計画書（1表）の情報を取得する
     *
     * @param   string $servicePlanId
     * @return  array
     */
    public function getFirstServicePlan($servicePlanId)
    {
        $row = DB::table('i_first_service_plans')
        ->where('service_plan_id', $servicePlanId)
            ->select([
                'service_plan_id',
                'plan_division',
                'living_alone',
                'handicapped',
                'other',
                'other_reason',
                'title1',
                'content1',
                'title2',
                'content2',
                'title3',
                'content3',
                'title4',
                'content4',
            ])
            ->first();

        if (empty($row)) {
            throw new Exception("i_first_service_plans not found. service_plan_id:$servicePlanId");
        }

        if ($row->living_alone == Consts::VALID) {
            $life_assistance_center = '1';
        } elseif ($row->handicapped == Consts::VALID) {
            $life_assistance_center = '2';
        } elseif ($row->other == Consts::VALID) {
            $life_assistance_center = '3';
        } else {
            $life_assistance_center = '';
        }

        return [
            'service_plan_id' => (string) $this->convert($row->service_plan_id),
            'plan_division' => (string) $this->convert($row->plan_division),
            'life_assistance_center' => $life_assistance_center,
            'other_reason'           => $this->convert($row->other_reason),
            'title1'                 => $this->convert($row->title1),
            'content1'               => $this->convert($row->content1),
            'title2'                 => $this->convert($row->title2),
            'content2'               => $this->convert($row->content2),
            'title3'                 => $this->convert($row->title3),
            'content3'               => $this->convert($row->content3),
            'title4'                 => $this->convert($row->title4),
            'content4'               => $this->convert($row->content4),
        ];
    }

    /**
     * i_facility_user_idがcareDaisyFacilityUserIdと一致する交付済みの全ての介護計画のIDを取得する
     *
     * @param   string $processTime
     * @param   string $facilityId
     * @param   string $careDaisyFaclityUserId
     * @return  array
     */
    public function getServicePlanIdsByFacilityUserId($facilityId, $careDaisyFacilityUserId)
    {
        $ids = [];
        // service_plan_idのリストを優先順に取得する
        $query = DB::table('i_service_plans AS isp')
        ->join('i_user_facility_informations AS iufi', 'isp.facility_user_id', '=', 'iufi.facility_user_id')
        ->where('iufi.facility_id', $facilityId)
            ->where('isp.facility_user_id', $careDaisyFacilityUserId)
            ->where('isp.status', 4)
            ->select([
                'isp.id AS service_plan_id',
            ])
            ->orderBy('isp.start_date')
            ->orderBy('isp.id');

        $result = $query->get();
        if (!$result->isEmpty()) {
            foreach ($result as $row) {
                $ids[] = $row->service_plan_id;
            }
        }

        return $ids;
    }


    /**
     * i_facility_user_idがcareDaisyFacilityUserIdと一致する当月の交付済みの最新の介護計画のservice_plan_idを取得する。
     * 当月に該当する介護計画が見つからない場合は交付済みの最新の介護計画のservice_plan_idを取得する。
     *
     * @param   string $processTime
     * @param   string $facilityId
     * @param   string $careDaisyFaclityUserId
     * @return  ?string
     */
    public function getDefaultServicePlanId($processTime, $facilityId, $careDaisyFacilityUserId)
    {
        $currentDate = Date('Y-m-d', strtotime($processTime));

        // 当月の介護計画を取得する
        $row = DB::table('i_service_plans AS isp')
        ->join('i_user_facility_informations AS iufi', 'isp.facility_user_id', '=', 'iufi.facility_user_id')
        ->where('iufi.facility_id', $facilityId)
            ->where('isp.facility_user_id', $careDaisyFacilityUserId)
            ->where('isp.start_date', '<=', $currentDate)
            ->where('isp.end_date', '>=', $currentDate)
            ->where('isp.status', 4)
            ->select([
                'isp.id AS service_plan_id',
            ])
            ->orderBy('isp.start_date')
            ->orderBy('isp.id')
            ->first();

        if (!empty($row)) {
            return $row->service_plan_id;
        }

        // 最新の介護計画を取得する
        $row = DB::table('i_service_plans AS isp')
        ->join('i_user_facility_informations AS iufi', 'isp.facility_user_id', '=', 'iufi.facility_user_id')
        ->where('iufi.facility_id', $facilityId)
            ->where('isp.facility_user_id', $careDaisyFacilityUserId)
            ->where('isp.status', ServicePlan::STATUS_ISSUED)
            ->select([
                'isp.id AS service_plan_id',
            ])
            ->orderBy('isp.start_date')
            ->orderBy('isp.id')
            ->first();

        if (!empty($row)) {
            return $row->service_plan_id;
        }

        return null;
    }


    /**
     * 介護計画書PDF API のレスポンスを生成する
     *
     * @param   int    $facilityId
     * @param   string $facilityNumber
     * @param   string $careDaisyFaclityUserId
     * @param   string $servicePlanId
     * @param   string $pagingNo
     * @return  array
     */
    public function getPdf($facilityId, $facilityNumber, $careDaisyFacilityUserId, $servicePlanId, $pagingNo)
    {
        $response = [
            'result_info' => [
                'result'      => 'OK',
                'result_code' => '',
                'message'     => '',
            ],
            'service_plan_table_pdf' => '',
            'latest_oldest_status'   => '',
            'first_service_plans'    => '',
        ];

        $processTime = $this->getDbTimestamp();

        // $careDaisyFacilityUserIDが指定されていない場合、当月の交付済みの最新の介護プランのIDを取得する
        if (empty($servicePlanId)) {
            $servicePlanId = $this->getDefaultServicePlanId($processTime, $facilityId, $careDaisyFacilityUserId);
            if (empty($servicePlanId)) {
                // デフォルトの介護計画が見つからない場合は以降の処理をしない（該当ファイルなし警告を返す）
                return null;
            }
        }

        // i_facility_user_idがcareDaisyFacilityUserIdと一致する交付済みの全ての介護計画書のIDを取得する
        $usersServicePlanIds = $this->getServicePlanIdsByFacilityUserId($facilityId, $careDaisyFacilityUserId);
        if (empty($usersServicePlanIds)) {
            // 該当する介護計画が見つからない
            return null;
        }

        // 取得する介護計画の位置を探す
        $targetCount = count($usersServicePlanIds);
        $found = -1;
        for ($i = 0; $i < $targetCount; $i++) {
            if ($usersServicePlanIds[$i] == $servicePlanId) {
                $found = $i;
                break;
            }
        }
        if (!empty($pagingNo)) {
            $found += $pagingNo;
        }
        if (($found < 0) || $found >= $targetCount) {
            // ページング先に介護計画が存在しない
            return null;
        }

        $targetServicePlanId = $usersServicePlanIds[$found];
        $hasPrev = ($found > 0) ? true : false;
        $hasNext = ($found < $targetCount - 1) ? true : false;

        if ($targetCount == 1) {
            $response['latest_oldest_status'] = '3';
        } elseif (!$hasPrev) {
            $response['latest_oldest_status'] = '2';
        } elseif (!$hasNext) {
            $response['latest_oldest_status'] = '1';
        } else {
            $response['latest_oldest_status'] = '4';
        }

        $response['first_service_plans'] = $this->getFirstServicePlan($targetServicePlanId);
        $response['service_plan_table_pdf'] = base64_encode(S3::getRawDataOfServicePlanPdf($targetServicePlanId));

        return $response;
    }
}
