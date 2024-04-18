<?php

namespace App\Service\GroupHome;

use Carbon\Carbon;

class AdditionStatusService
{
    /**
     * 介護報酬履歴のデータを全て返す
     * @param array $params
     * @return void
     */
    public function getCareRewardHistories(array $params) : array
    {
        $additionStatusTable = new AdditionStatusTable();

        $data = $additionStatusTable->getCareRewardHistories([
            'facility_id' => $params['facility_id'],
            'service_type_code_id' => $params['service_type_code_id']
        ]);

        return $data;
    }

    /**
     * 介護報酬履歴のデータを返す
     * @param array $params
     * @return void
     */
    public function getCareRewardHistory(array $params) : array
    {
        $id = $params['id'];
        $additionStatusTable = new AdditionStatusTable();

        $careRewardHistories = $additionStatusTable->getCareRewardHistories([
            'facility_id' => $params['facility_id'],
            'service_type_code_id' => $params['service_type_code_id']
        ]);
        $findData = array_filter($careRewardHistories, function($data) use ($id){
            return $data['id'] == $id;
        });
        $findData = array_values($findData);
        if (count($findData) != 1) {
            throw new \Exception('colud not find care reward history');
        }
        return $findData[0];
    }

    /**
     * 介護報酬履歴のデータを返す
     * @param array $params
     * @return void
     */
    public function getLatestCareRewardHistory(array $params) : array
    {
        $data = self::getCareRewardHistories($params);
        if (empty($data)) {
            return [];
        }

        $endMonths = array_column($data, 'end_month');
        array_multisort($endMonths, SORT_DESC, $data);

        return $data[0];
    }

    /**
     * 介護報酬履歴の更新をする
     * @param array $careRewardHistoryParams
     * @param array $facilityAdditionParams
     * @return void
     */
    public function updateCareRewardHistory(array $careRewardHistoryParams, array $facilityAdditionParams) : void
    {
        $additionStatusTable = new AdditionStatusTable();

        // 既存のデータを取得し、更新する介護報酬履歴のデータを除外する
        $existingData = $additionStatusTable->getCareRewardHistories([
            'facility_id' => $facilityAdditionParams['facility_id'],
            'service_type_code_id' => $facilityAdditionParams['service_type_code_id']
        ]);
        $filterdExistingData = array_filter($existingData, function($data) use ($careRewardHistoryParams){
            return $data['id'] != $careRewardHistoryParams['care_reward_histories_id'];
        });
        $filterdExistingData = array_values($filterdExistingData);

        // 更新するデータと、既存のデータの期間が重複している場合はエラー
        if ($this->isDuplication($filterdExistingData, $careRewardHistoryParams)) {
            throw new \Exception('Months are duplicated.');
        }

        \DB::beginTransaction();
        try {
            // 更新する
            $additionStatusTable->updateCareRewardHistory($careRewardHistoryParams);

            // 自動サービスコード(事業所)
            $autoServiceCodeFacilityService = new AutoServiceCodeFacilityService();
            $autoServiceCodeFacilityService->save($facilityAdditionParams);

            \DB::commit();
        } catch (\Exception $th) {
            \DB::rollBack();
            throw $th;
        }
    }

    /**
     * 介護報酬履歴の新規挿入をする
     * @param array $careRewardHistoryParams
     * @param array $facilityAdditionParams
     * @param string $serviceId
     * @return void
     */
    public function insertCareRewardHistory(array $careRewardHistoryParams, array $facilityAdditionParams, string $serviceId) : void
    {
        $additionStatusTable = new AdditionStatusTable();

        // 既存のデータを取得する
        $existingData = $additionStatusTable->getCareRewardHistories([
            'facility_id' => $facilityAdditionParams['facility_id'],
            'service_type_code_id' => $facilityAdditionParams['service_type_code_id']
        ]);
        // 新規挿入するデータと、既存のデータの期間が重複している場合はエラー
        if ($this->isDuplication($existingData, $careRewardHistoryParams)) {
            throw new \Exception('Months are duplicated.');
        }

        \DB::beginTransaction();
        try {
            // 新規挿入し、挿入した介護報酬履歴データのIDを取得する
            $careRewardHistoryId = $additionStatusTable->insertCareRewardHistory($careRewardHistoryParams, $serviceId);

            // 自動サービスコード(事業所)
            $autoServiceCodeFacilityService = new AutoServiceCodeFacilityService();
            $facilityAdditionParams['care_reward_histories_id'] = $careRewardHistoryId;
            $autoServiceCodeFacilityService->save($facilityAdditionParams);

            \DB::commit();
        } catch (\Exception $th) {
            \DB::rollBack();
            throw $th;
        }
    }

    /**
     * 対象の介護報酬履歴のデータと、既存のデータの期間が重複しているかを返す
     * @param array $existingData
     * @param array $targetData 対象の介護報酬履歴のデータ
     * @return bool
     */
    public function isDuplication(array $existingData, $targetData) : bool
    {
        for ($i = 0,$cnt = count($existingData); $i < $cnt; $i++) {
            $existing = $existingData[$i];
            $startDate = new Carbon($existing['start_month']);
            $endDate = new Carbon($existing['end_month']);

            $targetStartDate = new Carbon($targetData['start_month']);
            $targetEndDate = new Carbon($targetData['end_month']);

            if ($targetStartDate->between($startDate, $endDate) || $targetEndDate->between($startDate, $endDate)) {
                return true;
            }
        }
        return false;
    }
}
