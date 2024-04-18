<?php

namespace App\Http\Controllers\GroupHome\UserInfo;

use App\Http\Controllers\Controller;
use App\Service\GroupHome\BasicAbstractService;
use App\Http\Requests\GetFaclityInformationRequest;
use App\Http\Requests\GroupHome\UserInfo\BasicAbstractRequest;
use Illuminate\Http\Request;

use App\Models\MdcGroupNames;
use App\Models\BasicRemarks;

/**
 * 基本摘要画面コントローラー
 * 2022年8月4日現在種類55でのみ使用
 */
class BasicAbstractController extends Controller
{
    // 利用者状況等コード
    const USER_CIRCUMSTANCE_CODE = [
        'ｲ',
        'ﾛ',
        'ﾊA',
        'ﾊB',
        'ﾊC',
        'ﾊD',
        'ﾆ',
        'ﾎ',
        'ﾍ',
        'ﾄ',
        'ﾁ',
        'ﾘA',
        'ﾘB',
        'ﾘC',
        'ﾘD',
        'ﾘE',
        'ﾘF',
        'ﾘG',
        'ﾘH',
        'ﾇ'
    ];

    /**
     * 利用者状況等コードの内容を返す
     */
    public function getUserCircumstanceCode()
    {
        return self::USER_CIRCUMSTANCE_CODE;
    }

    /**
     * 利用者の登録情報を取得する
     */
    public function getUserInformation(
        GetFaclityInformationRequest $request,
        BasicAbstractService $basicAbstract
    ) {
        $response = $basicAbstract->getUserInformation($request);
        return $response;
    }

    /**
     * 履歴リストを取得する
     */
    public function getHistories(
        GetFaclityInformationRequest $request,
        BasicAbstractService $basicAbstract
    ) {
        $response = BasicRemarks::
            where('facility_user_id', $request->facility_user_id)
            ->orderBy('start_date', 'DESC')
            ->get()
            ->toArray();

        return $response;
    }

    /**
     * DPCコードから主傷病名を取得する
     */
    public function getMdcGroupNames(GetFaclityInformationRequest $request)
    {
        $dpc = $request->dpc_code;
        $mdcCode = substr($dpc, 0, 2);
        $groupCode = substr($dpc, 2, 4);
        $mdcGroupNameInfo = MdcGroupNames::getMdcGroupData($mdcCode, $groupCode);

        return $mdcGroupNameInfo;
    }

    /**
     * 登録処理
     */
    public function save(
        BasicAbstractRequest $request,
        BasicAbstractService $basicAbstract
    ) {
        try {
            $response = $basicAbstract->save($request);
        } catch (\Exception $e) {
            throw $e;
        }
        if ($response) {
            return $response;
        }

    }
}
