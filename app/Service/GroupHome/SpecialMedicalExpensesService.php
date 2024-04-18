<?php

namespace App\Service\GroupHome;

use \App\Models\CareReward;
use \App\Models\CareRewardHistory;
use App\Models\SpecialMedicalCode;
use App\Models\SpecialMedicalDetail;
use App\Models\SpecialMedicalSelect;

/**
 * 加算状況画面・特別診療費タブにおける各種処理を記載
 */
class SpecialMedicalExpensesService
{
    // 種類55 特別診療費タブで自動チェックを入れる対象となるカラム
    const SPECIAL_MEDICAL_EXPEMSES_ITEM = [
        "severe_skin_ulcer",
        "drug_guidance",
        "group_communication_therapy",
        "physical_therapy",
        "occupational_therapy",
        "speech_hearing_therapy",
        "psychiatric_occupational_therapy",
        "other_rehabilitation_provision",
        "dementia_short_rehabilitation",
    ];


    // 特別診療費コードマスタテーブルから取得するデータの対象サービスコード
    // 2022年8月4日現在では80のデータのみを取得する
    // todo 定数ファイル作成したらそちらに移動予定
    const SERVICE_TYPE_CODE = 80;

    /**
     * 加算状況に紐づく特別診療費の履歴リストを取得
     */
    public function getHistories($careRewardId, $careRewardHistoryId)
    {
        $spMedicalSelect = SpecialMedicalSelect::
            where('care_rewards_id', $careRewardId)
            ->orderBy('start_month', 'desc')
            ->get()
            ->toArray();

        // 対象加算状況履歴の開始月・終了月を取得
        $careRewardHistory = CareRewardHistory::
            where('id', $careRewardHistoryId)
            ->select('start_month', 'end_month')
            ->first()
            ->toArray();

        $checkedFlg = CareRewardHistory::
            where('care_reward_id', $careRewardId)
            ->select(self::SPECIAL_MEDICAL_EXPEMSES_ITEM)
            ->first()
            ->toArray();

        $res = [
            'spMedicalSelect' => $spMedicalSelect,
            'careRewardHistory' => $careRewardHistory,
            'autoCheckedFlg' => $checkedFlg
        ];

        return $res;
    }

    /**
     * 選択された履歴情報を取得
     */
    public function getSpecialMedicalInformation($id)
    {
        $useList = SpecialMedicalDetail::
            where('special_medical_selects_id', $id)
            ->pluck('special_medical_code_id');

        $codesInfo = SpecialMedicalCode::
            whereIn('id', $useList)
            ->select('id', 'service_type_code', 'identification_num')
            ->get();

        $spMedicalSelect = SpecialMedicalSelect::
            where('id', $id)
            ->first();

        $res['codesInfo'] = $codesInfo;
        $res['selectInfo'] = $spMedicalSelect;

        return $res;
    }

    /**
     * 更新処理
     */
    public function update($requestData)
    {
        $specialMedicalSelectsId = $requestData['special_medical_selects_id'];
        $codesNum = SpecialMedicalCode::
            whereIn('identification_num', $requestData['checked'])
            ->where('service_type_code', self::SERVICE_TYPE_CODE)
            ->date($requestData['start_month'], $requestData['end_month'])
            ->pluck('id');

        \DB::beginTransaction();
        try {
            // 履歴情報を削除する
            SpecialMedicalDetail::
                where('special_medical_selects_id', $requestData['special_medical_selects_id'])
                ->delete();

            $selectInfo = SpecialMedicalSelect::
                where('id', $specialMedicalSelectsId)
                ->update([
                    'start_month' => $requestData['start_month'],
                    'end_month' => $requestData['end_month'],
                ]);

            foreach ($codesNum as $value) {
                $res = SpecialMedicalDetail::create([
                    'special_medical_selects_id' => $specialMedicalSelectsId,
                    'special_medical_code_id' => $value,
                    'code_value' => 1,
                ]);
            }

            \DB::commit();
            return $selectInfo;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * 新規登録処理
     */
    public function insert($requestData)
    {
        $codesNum = SpecialMedicalCode::
            whereIn('identification_num', $requestData['checked'])
            ->where('service_type_code', self::SERVICE_TYPE_CODE)
            ->date($requestData['start_month'], $requestData['end_month'])
            ->pluck('id');

        \DB::beginTransaction();
        try {
            $selectInfo = SpecialMedicalSelect::
                create([
                    'care_rewards_id'  => $requestData['care_reward_id'],
                    'start_month' => $requestData['start_month'],
                    'end_month' => $requestData['end_month'],
                ]);

            foreach ($codesNum as $value) {
                $res = SpecialMedicalDetail::create([
                    'special_medical_selects_id' => $selectInfo->id,
                    'special_medical_code_id' => $value,
                    'code_value' => 1,
                ]);
            }

            \DB::commit();
            return $selectInfo;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
