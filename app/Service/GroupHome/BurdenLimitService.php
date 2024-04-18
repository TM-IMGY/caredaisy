<?php

namespace App\Service\GroupHome;

use App\Models\FacilityUser;
use App\Models\FacilityUserBurdenLimit;
use Exception;

/**
 * 負担限度額画面の各種処理を管理する
 */
class BurdenLimitService
{
    /**
     * 履歴のリストを取得する
     * @param int $facilityUserId
     */
    public function getHistories($facilityUserId)
    {
        $histories = FacilityUserBurdenLimit::histories($facilityUserId);

        if (!empty($histories)) {
            return $histories->toArray();
        }

        return [];
    }

    /**
     * 利用者の最新履歴・入居日を作成する
     */
    public function createUserInformation($facilityUserId)
    {
        $startDate = self::getUserStartDate($facilityUserId);
        $latestHistory = self::getLatestBurdenLimitHistory($facilityUserId);
        $information = [
            'start_date' => $startDate,
            'latest_history' => $latestHistory
        ];
        return $information;
    }

    /**
     * 入居日を取得する
     */
    public function getUserStartDate($facilityUserId)
    {
        // 利用者の入居日を取得
        $date = FacilityUser::find($facilityUserId)->toArray()['start_date'];
        return $date;
    }

    /**
     * 最新履歴を取得する
     */
    public function getLatestBurdenLimitHistory($facilityUserId)
    {
        $latestHistory = FacilityUserBurdenLimit::
            where('facility_user_id', $facilityUserId)
            ->orderBy('end_date', 'DESC')
            ->first();

        if (isset($latestHistory)) {
            $latestHistory = $latestHistory->toArray();
        }
        return $latestHistory;
    }

    /**
     * 登録処理
     * @param $params
     */
    public function save($params)
    {
        \DB::beginTransaction();
        try {
            if (isset($params['id'])) {
                FacilityUserBurdenLimit::
                    where('id', $params['id'])
                    ->where('facility_user_id', $params['facility_user_id'])
                    ->update([
                        'start_date' => $params['start_date'],
                        'end_date' => $params['end_date'],
                        'food_expenses_burden_limit' => $params['food_expenses_burden_limit'],
                        'living_expenses_burden_limit' => $params['living_expenses_burden_limit'],
                    ]);
            } else {
                FacilityUserBurdenLimit::create([
                    'facility_user_id' => $params['facility_user_id'],
                    'start_date' => $params['start_date'],
                    'end_date' => $params['end_date'],
                    'food_expenses_burden_limit' => $params['food_expenses_burden_limit'],
                    'living_expenses_burden_limit' => $params['living_expenses_burden_limit'],
                ]);
            }

            \DB::commit();
            return self::getHistories($params['facility_user_id']);
        } catch (\Exception $e) {
            \DB::rollBack();
            report($e);
            throw new Exception($e);
        }
    }
}
