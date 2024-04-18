<?php

namespace App\Service\GroupHome;

use App\Models\FacilityUser;
use App\Models\InjuriesSickness;
use App\Models\InjuriesSicknessDetail;
use App\Models\InjuriesSicknessRelation;
use App\Models\SpecialMedicalCode;
use App\Models\UserFacilityServiceInformation;

/**
 * 傷病名画面の各種処理の管理
 */
class InjuriesSicknessService
{

    /**
     * 利用者情報を取得
     * 対象：入居日、利用中のサービス、最新の傷病名履歴
     */
    public function getUserInformation($request)
    {
        // 利用中のサービスを取得
        $userInformation = UserFacilityServiceInformation::listFacilityUserTargetMonth(
            $request->facility_user_id,
            $request->year,
            $request->month
        );
        if (empty($userInformation)) {
            return [];
        }

        // 利用者の入居日を取得
        $startDate = FacilityUser::find($request->facility_user_id)->toArray()['start_date'];
        foreach ($userInformation as $key => $value) {
            $userInformation[$key]['start_date'] = $startDate;
        }
        // 最新履歴情報を取得
        $latestHistory = InjuriesSickness::
            where('facility_user_id', $request->facility_user_id)
            ->orderBy('end_date', 'DESC')
            ->first();

        if (isset($latestHistory)) {
            $latestHistory = $latestHistory->toArray();
        }

        $response = [
            'service' => $userInformation[0],
            'start_date' => $startDate,
            'latest_history' => $latestHistory
        ];

        return $response;
    }

    /**
     * 履歴リストを取得する
     */
    public function getHistories($request)
    {
        $histories = InjuriesSickness::
            where('facility_user_id', $request->facility_user_id)
            ->orderBy('start_date', 'DESC')
            ->get()
            ->toArray();

        foreach ($histories as $key => $value) {
            $details = InjuriesSicknessDetail::
                where('injuries_sicknesses_id', $value['id'])
                ->get()
                ->toArray();

            $histories[$key]['detail'] = $details;
        }

        return $histories;
    }

    /**
     * 履歴詳細を取得する
     */
    public function getHistoryDetail($id)
    {
        $basic = InjuriesSickness::
            where('id', $id)
            ->select('start_date', 'end_date')
            ->first()
            ->toArray();

        $details = InjuriesSicknessDetail::
            where('injuries_sicknesses_id', $id)
            ->get()
            ->toArray();

        foreach ($details as $key => $value) {
            $relations = InjuriesSicknessRelation::
              where('injuries_sicknesses_detail_id', $value['id'])
              ->orderBy('selected_position', 'ASC')
              ->get()
              ->toArray();

            $details[$key]['relations'] = $relations;
        }
        array_push($basic, $details);
        return $basic;
    }

    public function insert($formsParam, $request)
    {
        $facilityId = $request->facility_id;
        $facilityUserId = $request->facility_user_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        \DB::beginTransaction();
        try {
            // 傷病名情報テーブル
            $registerResult = InjuriesSickness::create([
                'facility_user_id' => $facilityUserId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            // 傷病名詳細テーブル
            foreach ($formsParam as $key => $value) {
                $detailRegisterResult = InjuriesSicknessDetail::create([
                    'injuries_sicknesses_id' => $registerResult->id,
                    'group' => $key,
                    'name' => $value['name'],
                ]);
            // 傷病名関連情報テーブル
                foreach ($value['ids'] as $index => $id) {
                    $relationRegisterResult = InjuriesSicknessRelation::create([
                        'injuries_sicknesses_detail_id' => $detailRegisterResult->id,
                        'special_medical_code_id' => $id,
                        'selected_position' => $index + 1
                    ]);
                }
            }

            \DB::commit();
            return $relationRegisterResult;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function update($formsParam, $request)
    {
        $facilityId = $request->facility_id;
        $facilityUserId = $request->facility_user_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $historyId = $request->id;

        \DB::beginTransaction();
        try {
            // 傷病名情報テーブル
            $registerResult = InjuriesSickness::where('id', $historyId)
                ->update([
                'facility_user_id' => $facilityUserId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            // 傷病名詳細テーブル・傷病名関連情報テーブルの履歴情報を削除する
            InjuriesSicknessDetail::
                where('injuries_sicknesses_id', $historyId)
                ->delete();


            // 傷病名詳細テーブル
            foreach ($formsParam as $key => $value) {
                $detailRegisterResult = InjuriesSicknessDetail::create([
                    'injuries_sicknesses_id' => $historyId,
                    'group' => $key,
                    'name' => $value['name'],
                ]);
                // 傷病名関連情報テーブル
                foreach ($value['ids'] as $index => $id) {
                    $relationRegisterResult = InjuriesSicknessRelation::create([
                        'injuries_sicknesses_detail_id' => $detailRegisterResult->id,
                        'special_medical_code_id' => $id,
                        'selected_position' => $index + 1
                    ]);
                }
            }

            \DB::commit();

            return $relationRegisterResult;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
