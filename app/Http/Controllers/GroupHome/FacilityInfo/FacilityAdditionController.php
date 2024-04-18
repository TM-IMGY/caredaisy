<?php

namespace App\Http\Controllers\GroupHome\FacilityInfo;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\FacilityAdditionGetCareRewardHistoriesRequest;
use App\Http\Requests\GroupHome\Service\FacilityAdditionGetCareRewardHistoryRequest;
use App\Http\Requests\GroupHome\Service\FacilityAdditionInsertCareRewardHistoryRequest;
use App\Http\Requests\GroupHome\Service\FacilityAdditionUpdateCareRewardHistoryRequest;
use App\Service\GroupHome\AdditionStatusService;
use Illuminate\Http\Exceptions\HttpResponseException;

class FacilityAdditionController extends Controller
{
    /**
     * 介護報酬履歴のデータを全て返す
     * @param FacilityAdditionGetCareRewardHistoriesRequest $request
     */
    public function getCareRewardHistories(FacilityAdditionGetCareRewardHistoriesRequest $request)
    {
        $params = [
            'facility_id' => $request->facility_id,
            'service_type_code_id' => $request->service_type_code_id,
        ];

        $additionStatusService = new AdditionStatusService();
        $data = $additionStatusService->getCareRewardHistories($params);

        return $data;
    }

    /**
     * 介護報酬履歴のデータを返す
     * @param FacilityAdditionGetCareRewardHistoryRequest $request
     */
    public function getCareRewardHistory(FacilityAdditionGetCareRewardHistoryRequest $request)
    {
        $params = [
            'id' => $request->id,
            'facility_id' => $request->facility_id,
            'service_type_code_id' => $request->service_type_code_id,
        ];

        $additionStatusService = new AdditionStatusService();
        $data = $additionStatusService->getCareRewardHistory($params);

        return $data;
    }

    /**
     * 最新の介護報酬履歴のデータを返す
     * @param FacilityAdditionGetCareRewardHistoriesRequest $request
     */
    public function geLatestCareRewardHistory(FacilityAdditionGetCareRewardHistoriesRequest $request)
    {
        $params = [
            'facility_id' => $request->facility_id,
            'service_type_code_id' => $request->service_type_code_id,
        ];

        $additionStatusService = new AdditionStatusService();
        $data = $additionStatusService->getLatestCareRewardHistory($params);

        return $data;
    }

    /**
     * 更新処理。成功すれば投入した介護報酬履歴のデータを返す
     * @param FacilityAdditionUpdateCareRewardHistoryRequest $request
     */
    public function updateCareRewardHistory(FacilityAdditionUpdateCareRewardHistoryRequest $request)
    {
        // パラメーターを取得する
        $params = [
            // 介護報酬履歴
            'care_reward_history' => $request->care_reward_history,
            // 事業所加算
            'facility_addition' => [
                'addition_start_date' => $request->care_reward_history['start_month'],
                'addition_end_date' => $request->care_reward_history['end_month'],
                'before_start_date' => $request->start_date,
                'before_end_date' => $request->end_date,
                'care_reward_histories_id' => $request->care_reward_history['care_reward_histories_id'],
                'facility_id' => $request->facility_id,
                'service_type_code_id' => $request->service_type_code_id
            ]
        ];

        try {
            // 介護報酬履歴の更新をする
            $additionStatusService = new AdditionStatusService();
            $additionStatusService->updateCareRewardHistory($params['care_reward_history'], $params['facility_addition']);
        } catch (\Exception $e) {
            $res = response()->json([
                'errors' => array('message' => ['保存に失敗しました。入力情報を確認してください。']),
            ], 400);
            throw new HttpResponseException($res);
        }

        return $params['care_reward_history'];
    }

    /**
     * 新規挿入処理。成功すれば投入した介護報酬履歴のデータを返す
     * @param FacilityAdditionInsertCareRewardHistoryRequest $request
     */
    public function insertCareRewardHistory(FacilityAdditionInsertCareRewardHistoryRequest $request)
    {
        // パラメーターを取得する
        $params = [
            // 介護報酬履歴
            'care_reward_history' => $request->care_reward_history,
            // 事業所加算
            'facility_addition' => [
                'addition_start_date' => $request->care_reward_history['start_month'],
                'addition_end_date' => $request->care_reward_history['end_month'],
                'before_start_date' => $request->care_reward_history['start_month'],
                'before_end_date' => $request->care_reward_history['end_month'],
                'facility_id' => $request->facility_id,
                'service_type_code_id' => $request->service_type_code_id,
            ],
            'service_id' => $request->service_id
        ];

        try {
            // 介護報酬履歴の新規挿入をする
            $additionStatusService = new AdditionStatusService();
            $additionStatusService->insertCareRewardHistory(
                $params['care_reward_history'],
                $params['facility_addition'],
                $params['service_id']
            );

            return $params['care_reward_history'];
        } catch (\Exception $e) {
            $res = response()->json([
                'errors' => array('message' => ['保存に失敗しました。入力情報を確認してください。']),
            ], 400);
            throw new HttpResponseException($res);
        }
    }
}
