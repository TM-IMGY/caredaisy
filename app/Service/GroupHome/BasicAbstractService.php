<?php

namespace App\Service\GroupHome;

use App\Models\FacilityUser;
use App\Models\BasicRemarks;
use App\Models\MdcGroupNames;
use App\Models\UserFacilityServiceInformation;

/**
 * 基本摘要画面の各種処理を管理する
 */
class BasicAbstractService
{
    /**
     * 利用者情報を取得する
     */
    public function getUserInformation($request)
    {
        $response = [];
        $userInformation = UserFacilityServiceInformation::listFacilityUserTargetMonth(
            $request->facility_user_id,
            $request->year,
            $request->month
        );
        $response["service"] = $userInformation[0] ?? [];

        // 利用者の入居日を取得
        $startDate = FacilityUser::find($request->facility_user_id)->toArray()['start_date'];
        $response['start_date'] = $startDate;

        // 最新履歴情報を取得
        $latestHistory = BasicRemarks::
            where('facility_user_id', $request->facility_user_id)
            ->orderBy('end_date', 'DESC')
            ->first();

        if (isset($latestHistory)) {
            $latestHistory = $latestHistory->toArray();
            $response['latest_history'] = $latestHistory;
        }

        return $response;
    }

    /**
     * 登録処理
     */
    public function save($request)
    {
        \DB::beginTransaction();
        try {
            if (isset($request->id)) {
                $response = BasicRemarks::
                    where('id', $request->id)
                    ->where('facility_user_id', $request->facility_user_id)
                    ->update([
                        'dpc_code' => $request->dpc_code,
                        'user_circumstance_code' => $request->user_circumstance_code,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                    ]);
            } else {
                $response = BasicRemarks::create([
                    'facility_user_id' => $request->facility_user_id,
                    'dpc_code' => $request->dpc_code,
                    'user_circumstance_code' => $request->user_circumstance_code,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ]);
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
        return $response;
    }
}
