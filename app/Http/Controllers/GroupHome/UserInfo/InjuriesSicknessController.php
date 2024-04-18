<?php

namespace App\Http\Controllers\GroupHome\UserInfo;

use App\Http\Controllers\Controller;
use App\Service\GroupHome\InjuriesSicknessService;
use App\Http\Requests\GetFaclityInformationRequest;
use App\Http\Requests\GroupHome\UserInfo\InjuriesSicknessRequest;
use App\Service\GroupHome\SpecialMedicalCodeService;
use Illuminate\Http\Request;

/**
 * 傷病名画面コントローラー
 * 2022年8月4日現在種類55でのみ使用
 */
class InjuriesSicknessController extends Controller
{
    // 特別診療費コードマスタのサービス種類コード 8月2日現在では80のみを取得
    public const SERVICE_TYPE_CODE = 80;

    /**
     * 履歴リストを取得する
     */
    public function getHistories(
        GetFaclityInformationRequest $request,
        InjuriesSicknessService $injuriesSickness
    ) {
        if ($request->facility_user_id == 'null') {
            return [];
        }
        $histories = $injuriesSickness->getHistories($request);
        return $histories;
    }

    /**
     * 履歴情報を取得する
     */
    public function getHistory(
        GetFaclityInformationRequest $request,
        InjuriesSicknessService $injuriesSickness
    ) {
        $historyDetail = $injuriesSickness->getHistoryDetail($request->id);
        return $historyDetail;
    }

    /**
     * 利用者が登録可能な特別診療費リストを取得する
     */
    public function getSpecialMedicalExpensesList(
        GetFaclityInformationRequest $request,
        InjuriesSicknessService $injuriesSickness
    ) {
        $facilityId = $request->facility_id;
        $year = $request->year;
        $month = $request->month;

        $service = new SpecialMedicalCodeService();
        $specialMedicalCodes = $service->get($facilityId, self::SERVICE_TYPE_CODE, $year, $month);

        return $specialMedicalCodes;
    }

    /**
     * 利用者の登録情報を取得する
     */
    public function getUserInformation(
        GetFaclityInformationRequest $request,
        InjuriesSicknessService $injuriesSickness
    ) {
        $response = $injuriesSickness->getUserInformation($request);
        return $response;
    }

    /**
     * 登録処理
     */
    public function save(
        InjuriesSicknessRequest $request,
        InjuriesSicknessService $injuriesSickness
    ) {
        $arrParam = $request->request_special;

        // 傷病名が空白の配列を除外
        foreach ($arrParam as $key => $value) {
            if (is_null($value['name'])) {
                unset($arrParam[$key]);
            }
            // 選択していないプルダウンの要素を除外
            foreach ($value['ids'] as $index => $id) {
                if (is_null($id)) {
                    unset($arrParam[$key]['ids'][$index]);
                }
            }
        }

        try {
            if ($request->has('id')) {
                $result = $injuriesSickness->update($arrParam, $request);
            } else {
                $result = $injuriesSickness->insert($arrParam, $request);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $result;
    }
}
