<?php

/**
 * TODO: グループホーム以外のものも扱っているため名前空間を修正する。
 */
namespace App\Http\Controllers\GroupHome\ResultInfo;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\ResultInfo\ResultInfoGetServiceTypeRequest;
use App\Http\Requests\GroupHome\ResultInfo\ResultInfoGetTransmissionModeRequest;
use App\Http\Requests\GroupHome\ResultInfo\ResultInfoGetApprovedUsersRequest;
use App\Models\Facility;
use App\Models\Approval;
use App\Models\ServiceResult;
use App\Service\GroupHome\ResultInformationService;

/**
 * 実績情報画面のコントローラー。
 */
class ResultInfoController extends Controller
{
    /**
     * 施設利用者が提供を受けているサービス種別について、対象年月中のものを全て取得して返す。
     * @param ResultInfoGetServiceTypeRequest $request
     */
    public function getServiceType(ResultInfoGetServiceTypeRequest $request)
    {
        // リクエストパラメーターを取得する。
        $facilityUserId = $request->facility_user_id;
        $year = $request->year;
        $month = $request->month;

        $resultInformationService = new ResultInformationService();
        $data = $resultInformationService->listFacilityUserTargetYmService($facilityUserId, $year, $month);

        return $data;
    }

    /**
     * @param ResultInfoGetTransmissionModeRequest $request
     */
    public function getTransmissionMode(ResultInfoGetTransmissionModeRequest $request)
    {
        $facilityId = $request->facility_id;
        return [
            'facility_id' => $facilityId,
            'allow_transmission' => Facility::getTransmissionMode($facilityId)
        ];
    }

    /**
     * 国保連請求、または保険外請求が承認済みのユーザーを取得する
     * 
     * @param ResultInfoGetApprovedUsersRequest $request
     */
    public function getApprovedUsers(ResultInfoGetApprovedUsersRequest $request)
    {
        // リクエストから取得した年月をY-M-Dの形式に変換する
        $ymd = $request->year.'-'.$request->month.'-01';
        // 承認管理テーブルから保険外請求が承認済みのユーザーを取得する
        $retApprovalArray = Approval::where("facility_id", $request->facility_id)
            ->where('month', $ymd)
            ->whereIn("facility_user_id", $request->facility_user_ids)
            ->where('approval_type', Approval::UNINSURED_APPROVAL_TYPE)
            ->where('approval_flag', '1')
            ->select('facility_user_id')
            ->get()
            ->toArray();

        // 返却用ユーザーIDリスト
        $retIds = [];

        // ユーザーIDを返却用の配列に詰めなおす
        foreach ($retApprovalArray as $retApproval) {
            array_push($retIds, $retApproval);
        }

        // 実績登録テーブルから国保連請求が承認済みのユーザーを取得する
        $retServiceResultArray = ServiceResult::where("facility_id", $request->facility_id)
            ->where('target_date', $ymd)
            ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
            ->whereIn("facility_user_id", $request->facility_user_ids)
            ->where('approval', '1')
            ->select('facility_user_id')
            ->get()
            ->toArray();

        // ユーザーIDを返却用の配列に詰めなおす
        foreach ($retServiceResultArray as $retServiceResult) {
            if (in_array($retServiceResult, $retIds)) {
                // すでに返却用配列に設定されている場合は追加しない
                continue;
            }
            array_push($retIds, $retServiceResult);
        }

        // 国保連請求、または保険外請求が承認済みのユーザーリストを返却する
        return [
            'facility_user_ids' => $retIds,
        ];
    }

    public function index()
    {
        return view('group_home.result_info.result_info');
    }
}
