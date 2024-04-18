<?php

namespace App\Http\Controllers\GroupHome\FacilityInfo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\GetFaclityUserInformationRequest;
use App\Http\Requests\GroupHome\FacilityInfo\SpecialMedicalExpensesRequest;
use App\Service\GroupHome\SpecialMedicalExpensesService;

/**
 * 7/29現在 種類55用
 * 加算状況画面内 特別診療費画面から呼び出し
 */
class SpecialMedicalExpensesController extends Controller
{
    /**
     * 事業所に紐づく登録されている履歴リストを取得する
     */
    public function getHistories(
        GetFaclityUserInformationRequest $request,
        SpecialMedicalExpensesService $spMedicalService
    ) {
        $careRewardId = $request->care_reward_id;
        $careRewardHistoryId = $request->care_reward_history_id;
        $histories = $spMedicalService->getHistories($careRewardId, $careRewardHistoryId);

        return $histories;
    }

    /**
     * 選択された履歴の登録内容を取得する
     */
    public function getSpecialMedicalInformation(
        GetFaclityUserInformationRequest $request,
        SpecialMedicalExpensesService $spMedicalService
    ) {
        $spMedicalSelectId = $request->id;
        $codesInfo = $spMedicalService->getSpecialMedicalInformation($spMedicalSelectId);
        return $codesInfo;
    }

    /**
     * 登録処理
     * イメージ的には実績登録みたいに都度全消しして再登録するイメージ
     */
    public function save(
        SpecialMedicalExpensesRequest $request,
        SpecialMedicalExpensesService $spMedicalService
    ) {
        $requestData = $request->all();

        try {
            if (isset($request->special_medical_selects_id)) {
                $res = $spMedicalService->update($requestData);
            } else {
                $res = $spMedicalService->insert($requestData);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        if ($res) {
            return $requestData;
        }
    }
}
