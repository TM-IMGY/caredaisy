<?php

namespace App\Service\GroupHome;

use App\Models\SecondServicePlan;
use App\Models\ServiceLongPlan;
use App\Models\ServicePlan;
use App\Models\ServicePlanNeed;
use App\Models\ServicePlanSupport;
use App\Models\ServiceShortPlan;

class SecondServicePlanService
{
    /**
     * 介護計画書2関連レコードを新規挿入する
     * @param int $servicePlanID 介護計画書ID
     * @return array
     */
    public function insertRecord($servicePlanID) : array {
        // 介護計画書のケアプラン期間を取得する
        $servicePlan = ServicePlan::where('id', $servicePlanID)->select(['start_date','end_date'])->first()->toArray();

        $response = [];

        \DB::beginTransaction();
        try {
            // 介護計画書2作成
            $secondServicePlanID = SecondServicePlan::insertGetId(['service_plan_id' => $servicePlanID]);

            // 介護計画書2に紐づくニーズのレコードをインサート
            $servicePlanNeedID = ServicePlanNeed::insertGetId([
                'second_service_plan_id' => $secondServicePlanID,
            ]);

            // 介護計画書2に紐づく長期のレコードをインサート
            $serviceLongPlanID = ServiceLongPlan::insertGetId([
                'service_plan_need_id' => $servicePlanNeedID,
                'task_start' => $servicePlan['start_date'],
                'task_end' => $servicePlan['end_date']
            ]);

            // 介護計画書2に紐づく短期のレコードをインサート
            $serviceShortPlanID = ServiceShortPlan::insertGetId([
                'service_long_plan_id' => $serviceLongPlanID,
                'task_start' => $servicePlan['start_date'],
                'task_end' => $servicePlan['end_date']
            ]);

            // 介護計画書2に紐づく援助内容のレコードをインサート
            $servicePlanSupportID = $this->insertSupport([
                'service_short_plan_id' => $serviceShortPlanID,
                'task_start' => $servicePlan['start_date'],
                'task_end' => $servicePlan['end_date']
            ]);

            \DB::commit();

            $response = [
                'second_service_plan_id' => $secondServicePlanID,
                'service_plan_need_id' => $servicePlanNeedID,
                'service_long_plan_id' => $serviceLongPlanID,
                'service_short_plan_id' => $serviceShortPlanID,
                'service_plan_support_id' => $servicePlanSupportID,
            ];
        } catch (\Exception $e) {
            \DB::rollBack();
            report($e);
        }

        return $response;
    }

    /**
     * 介護計画書2の援助内容に新規レコードを挿入する
     * @param array $data key:service_short_plan_id
     * @return int
     */
    public function insertSupport($data) : int {
        return ServicePlanSupport::insertGetId($data);
    }

    /**
     * 介護計画書2の長期に新規レコードのリストを挿入する
     * @param array $data
     * @return array
     */
    public function insertLongList($data) : void {
        ServiceLongPlan::insert($data);
    }

    /**
     * 介護計画書2のニーズに新規レコードのリストを挿入する
     * @param array $data
     * @return array
     */
    public function insertNeedList($data) : void {
        ServicePlanNeed::insert($data);
    }

    /**
     * 介護計画書2の短期に新規レコードのリストを挿入する
     * @param array $data
     * @return array
     */
    public function insertShortList($data) : void {
        ServiceShortPlan::insert($data);
    }

    /**
     * 介護計画書2の援助内容に新規レコードのリストを挿入する
     * @param array $data
     * @return array
     */
    public function insertSupportList($data) : void {
        ServicePlanSupport::insert($data);
    }

    /**
     * i_second_service_plansのレコードを取得する
     * @param int $servicePlanID 介護計画書ID
     * @return ?array
     */
    public function get($servicePlanID) : ?array {
        $secondServicePlan = SecondServicePlan::
            where('service_plan_id', $servicePlanID)
            ->select('id')
            ->first();
        if ($secondServicePlan === null) {
            return null;
        }
        $secondServicePlanID = $secondServicePlan['id'];

        // 介護計画書2に紐づくニーズを取得
        $servicePlanNeed = ServicePlanNeed::
            where('second_service_plan_id', $secondServicePlanID)
            ->select('id', 'needs', 'task_start', 'task_end', 'sort')
            ->get()
            ->toArray();
        // 取得したニーズのidのリストを取得
        $needIDList = array_map(function($need) {
            return $need['id'];
        }, $servicePlanNeed);

        // 介護計画書2に紐づく長期を取得
        $serviceLongPlan = ServiceLongPlan::
            whereIn('service_plan_need_id', $needIDList)
            ->select('id', 'service_plan_need_id', 'goal', 'task_start', 'task_end', 'sort')
            ->get()
            ->toArray();
        // 取得した長期のidのリストを取得
        $longIDList = array_map(function($long) {
            return $long['id'];
        }, $serviceLongPlan);

        // 介護計画書2に紐づく短期を取得
        $serviceShortPlan = ServiceShortPlan::
            whereIn('service_long_plan_id', $longIDList)
            ->select('id', 'service_long_plan_id', 'goal', 'task_start', 'task_end', 'sort')
            ->get()
            ->toArray();
        // 取得した長期のidのリストを取得
        $shortIDList = array_map(function($short) {
            return $short['id'];
        }, $serviceShortPlan);

        // 介護計画書2に紐づく援助内容を取得
        $servicePlanSupport = ServicePlanSupport::
            whereIn('service_short_plan_id', $shortIDList)
            ->select('id', 'service_short_plan_id', 'task_start', 'task_end', 'service', 'staff', 'frequency', 'sort')
            ->get()
            ->toArray();
        $supportIDList = array_map(function($support) {
            return $support['id'];
        }, $servicePlanSupport);

        return [
            'second_service_plan_id' => $secondServicePlanID,
            'service_plan_need' => $servicePlanNeed,
            'service_long_plan' => $serviceLongPlan,
            'service_short_plan' => $serviceShortPlan,
            'service_plan_support' => $servicePlanSupport
        ];
    }

    public function insertNSupport($data) : int {
        return ServicePlanSupport::insertGetId($data);
    }

    public function insertNLong($data) : int {
        return ServiceLongPlan::insertGetId($data);
    }

    public function insertNShort($data) : int {
        return ServiceShortPlan::insertGetId($data);
    }

    public function insertNNeed($data) : int {
        return ServicePlanNeed::insertGetId($data);
    }
}
