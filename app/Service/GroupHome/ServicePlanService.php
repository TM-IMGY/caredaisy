<?php

namespace App\Service\GroupHome;

use App\Models\ServicePlan;
use App\Models\FacilityUser;

class ServicePlanService
{
    // 計画書1・2 PDFタイトル分類
    const TITLE_DEMENTIA = [32];
    const TITLE_FACILITY_SERVICE = [33,35,36,55];
    const TITLE_CARE_PREVENTION = [37];

    /**
     * 介護計画書の情報を返す
     * @param array $param key: id
     * @return array
     */
    public function get($param) : array
    {
        return ServicePlan::where('id', $param['id'])->select($param['clm'])->get()->toArray();
    }

    /**
     * 介護計画書のケアプラン期間を取得するユースケース
     * @param array $params key: facility_user_id
     * @return array
     */
    public function getCarePlanPeriod($params) : array
    {
        // レスポンスのプレースホルダー
        $response = [];

        // 介護計画書リポジトリから最新の交付済みの介護計画書を取得する
        $servicePlan = $this->getLatestDeliveredServicePlan($params);

        // 介護計画書が取得できた場合
        if (count($servicePlan) > 0) {
          // 終了日の翌日を開始日として設定する
            $endDate = new \DateTime($servicePlan['end_date']);
            $endDate->modify('+1 days');
            $response = [
            'start_date' => $endDate->format('Y-m-d'),
            'is_init_plan' => false
            ];
        // 取得できなかった場合は施設利用者の入居日・認定情報有効開始日・サービス有効開始日から最新日付を取得する
        } else {
            $startDate = self::compare3Dates($params);
            $response = [
            'start_date' => $startDate->format('Y-m-d'),
            'is_init_plan' => true
            ];
        }

        return $response;
    }

    /**
     * 利用者の入居日を取得する
     */
    public function getFacilityUserStartDate($params)
    {
        $facilityUserService = new FacilityUserService();
        $facilityUser = $facilityUserService->getFacilityUser($params);
        $startDate = new \DateTime($facilityUser['start_date']);
        return $startDate;
    }

    /**
     * 利用者の認定情報の最新の有効開始日を取得する
     */
    public function getLatestFacilityUserCarePeriodStart($params)
    {
        $carePeriodStart = null;
        $careInformation = \App\Models\UserCareInformation::getLatestApproval($params['facility_user_id']);
        if ($careInformation) {
            $carePeriodStart = new \DateTime($careInformation['care_period_start']);
        }
        return $carePeriodStart;
    }

    /**
     * 利用者の利用中サービスの最新の有効開始日を取得する
     */
    public function getLatestFacilityUserUseStartOfService($params)
    {
        $carePeriodStart = null;
        $serviceInformation = \App\Models\UserFacilityServiceInformation::getLatestUserFacilityServiceInformation($params['facility_user_id']);
        if ($serviceInformation) {
            $carePeriodStart = new \DateTime($serviceInformation['use_start']);
        }
        return $carePeriodStart;
    }

    /**
     * 入居日・認定情報有効開始日・サービス有効開始日を比較して最新日付を返す
     */
    public function compare3Dates($params)
    {
        // 入居日
        $startDate = self::getFacilityUserStartDate($params);
        // 認定情報の有効開始日
        $carePeriodStart = self::getLatestFacilityUserCarePeriodStart($params);
        // 利用者の利用中のサービスの有効開始日
        $useStart = self::getLatestFacilityUserUseStartOfService($params);

        $dateList = [$startDate,$carePeriodStart,$useStart];
        rsort($dateList);
        return $dateList[0];
    }

    /**
     * 最新の交付済の介護計画書を取得するためのリポジトリ
     * @param array $params key: facility_user_id
     * @return array
     */
    public function getLatestDeliveredServicePlan($params) : array
    {
        $status = ServicePlan::STATUS_ISSUED;

        $query = <<< SQL
SELECT
  `id`,
  `delivery_date`,
  `start_date`,
  `end_date`
FROM
  `i_service_plans`
WHERE
  `facility_user_id` = ?
  AND
  `status` = {$status}
ORDER BY
  `delivery_date` DESC
LIMIT
  1
SQL;

        $servicePlans = \DB::select($query, [
            $params['facility_user_id']
        ]);

        if (count($servicePlans) === 0) {
            return [];
        }

        // 最新のレコードのみを取得する
        $servicePlan = $servicePlans[0];

        return [
            'id' => $servicePlan->id,
            'delivery_date' => $servicePlan->delivery_date,
            'start_date' => $servicePlan->start_date,
            'end_date' => $servicePlan->end_date
        ];
    }

    /**
     * 介護計画書1・2のPDFのタイトルを生成する
     * @param integer $serviceType
     * @param integer $carePlanNum
     */
    public function selectPdfTitle($serviceType, $carePlanNum)
    {
        if (in_array($serviceType,self::TITLE_DEMENTIA)) {
            $title = '認知症対応型共同生活介護計画'.'('.$carePlanNum.')';
        } elseif (in_array($serviceType,self::TITLE_FACILITY_SERVICE)) {
            $title = '施設サービス計画書'.'('.$carePlanNum.')';
        } elseif (in_array($serviceType,self::TITLE_CARE_PREVENTION)) {
            $title = '介護予防認知症対応型共同生活介護計画'.'('.$carePlanNum.')';
        }
        return $title;
    }
}
