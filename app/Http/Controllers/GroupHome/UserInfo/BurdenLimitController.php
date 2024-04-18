<?php

namespace App\Http\Controllers\GroupHome\UserInfo;

use App\Service\GroupHome\BurdenLimitService;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\UserInfo\BurdenLimitRequest;
use App\Http\Requests\GetFaclityUserInformationRequest;

/**
 * 負担限度額画面コントローラー
 * 2022年11月14日現在種類55でのみ使用
 */
class BurdenLimitController extends Controller
{
    private $burdenLimit;

    public function __construct(BurdenLimitService $burdenLimit)
    {
        $this->burdenLimit = $burdenLimit;
    }

    /**
     * 履歴リストを取得する
     */
    public function getHistories(GetFaclityUserInformationRequest $request)
    {
        return $this->burdenLimit->getHistories($request->facility_user_id);
    }

    /**
     * 利用者情報を取得する
     */
    public function getUserInformation(GetFaclityUserInformationRequest $request)
    {
        return $this->burdenLimit->createUserInformation($request->facility_user_id);
    }

    public function save(BurdenLimitRequest $request)
    {
        $params = [
            'facility_user_id' => $request->facility_user_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'food_expenses_burden_limit' => $request->food_expenses_burden_limit,
            'living_expenses_burden_limit' => $request->living_expenses_burden_limit,
        ];
        if ($request->has('id')) {
            $params['id'] = $request->id;
        }
        return $this->burdenLimit->save($request);
    }
}
