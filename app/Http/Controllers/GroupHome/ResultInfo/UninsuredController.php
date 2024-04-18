<?php

namespace App\Http\Controllers\GroupHome\ResultInfo;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Http\Requests\GetFaclityUserInformationRequest;
use App\Http\Requests\GroupHome\ResultInfo\UninsuredAgreementRequest;
use App\Http\Requests\GroupHome\ResultInfo\UninsuredManagementRequest;
use App\Service\GroupHome\UninsuredService;
use App\Service\GroupHome\StayOutService;
use App\Service\GroupHome\FacilityUserService;
use App\Service\GroupHome\UserPublicExpenseInformationService;

use App\Models\Approval;

use Carbon\Carbon;
use Illuminate\Http\Request;

class UninsuredController extends Controller
{
    public function saveItem(UninsuredManagementRequest $request)
    {
        $params = [];
        $params["facility_user_id"] = $request->facility_user_id;
        $params["name"] = $request->item_name;
        $params["uninsured_item_history_id"] = null;
        $params["month"] = $request->year. "-". $request->month. "-01";
        $params["unit_cost"] = 0;
        $params["sort"] = 99; // すぐ差し替わるので暫定として

        $uninsuredService = new UninsuredService();
        $itemArray = $uninsuredService->saveItem($params);
        return $itemArray;
    }

    public function checkAgreement(UninsuredAgreementRequest $request)
    {
        $facilityId = $request->facility_id;
        $facilityUserId = $request->facility_user_id;
        $month = $request->year. "-". $request->month. "-01";
        $approvalType = Approval::UNINSURED_APPROVAL_TYPE;

        $approvalFlag = Approval::getApproval(
            $facilityId,
            $facilityUserId,
            $month,
            $approvalType
        );

        return ["approval_flag" => $approvalFlag];
    }

    public function agreement(UninsuredAgreementRequest $request)
    {
        $facilityId = $request->facility_id;
        $facilityUserId = $request->facility_user_id;
        $month = $request->year. "-". $request->month. "-01";
        $approvalType = Approval::UNINSURED_APPROVAL_TYPE;
        $approvalFlag = ($request->flag == 'true') ? 1 : 0;

        $result = Approval::setApproval(
            $facilityId,
            $facilityUserId,
            $month,
            $approvalType,
            $approvalFlag
        );

        if ($result) {
            return ["approval_flag" => $approvalFlag];
        }
        abort(500);
    }


    public function list(GetFaclityUserInformationRequest $request)
    {
        $carbon = new Carbon($request->year. "-". $request->month. "-01");
        $params = [
            'facility_user_id' => $request->facility_user_id,
            'month' => $carbon->format("Y-m-d")
        ];

        $uninsuredService = new UninsuredService();
        return $uninsuredService->uninsuredList($params);
    }

    public function userInfo(GetFaclityUserInformationRequest $request)
    {
        /**
         * todo:
         * コントローラの記述が長くなってきているため
         * サービスファイルなどにリファクタリングした方が良いように感じる。
         */
        Carbon::setLocale('ja');
        $carbon = new Carbon($request->year. "-". $request->month. "-01");
        $lastDay = $carbon->lastOfMonth()->format("d");
        $lastDate = $carbon->lastOfMonth()->format("Y-m-d 23:59:59");
        $carbon->firstOfMonth();

        $params = [
            'facility_user_id' => $request->facility_user_id,
            'first_of_month' => $carbon->format("Y-m-d H:i:s"),
            'last_of_month' => $lastDate,
        ];

        $stayoutService = new StayOutService();
        $outDates = $stayoutService->getStayoutDays($params);

        $facilityUserId = $params['facility_user_id'];
        // 対象年月が$params変数に記載されているため、それぞれ抜き出す。
        $year = substr($params["first_of_month"], 0, 4);
        $month = substr($params["first_of_month"], 5, 2);
        // 入居日までと退去日からの日付が対象の年月にあった場合に日付の数値を配列を格納する。
        $facilityUserService = new FacilityUserService();
        $outDates = array_merge($outDates, $facilityUserService->getStartDates($facilityUserId, $year, $month));
        $outDates = array_merge($outDates, $facilityUserService->getEndDates($facilityUserId, $year, $month));
        // 配列の重複を削除する。
        $outDates = array_unique($outDates);
        // 昇順にソートする。
        sort($outDates, SORT_ASC);

        $calendar = [];
        for ($i = 1; $i <= $lastDay; $i++) {
            $tmp = [];
            $tmp["day"] = $i;
            $tmp["DOW"] = $carbon->isoFormat("ddd");

            $tmp["is_out_dates"] = false;
            if (in_array($i, $outDates)) {
                $tmp["is_out_dates"] = true;
            }

            $calendar[] = $tmp;
            $carbon->addDay(1);
        }

        return json_encode($calendar);
    }

    /**
     * 保険外品目プルダウン
     */
    public function itemList(GetFaclityUserInformationRequest $request)
    {
        $facilityUserId = $request->facility_user_id;
        $year = $request->target_year;
        $month = $request->target_month;

        $uninsuredService = new UninsuredService();
        
        return $uninsuredService->uninsuredItemList($facilityUserId, $year, $month);
    }

    public function saveSort(UninsuredManagementRequest $request)
    {
        $sort = 1;
        foreach (explode(",", $request->uninsured_request_id_list) as $uninsured_request_id) {
            $params = [
                "facility_user_id" => $request->facility_user_id,
                "uninsured_request_id" => $uninsured_request_id,
                'month' => $request->month,
                "sort" => $sort,
            ];

            $uninsuredService = new UninsuredService();
            $res = $uninsuredService->saveRow($params);
            if (!$res) {
                abort(500);
            }
            $sort++;
        }
        return [];
    }

    public function saveRow(UninsuredManagementRequest $request)
    {
        $params = [
            "facility_user_id" => $request->facility_user_id,
            'unit_cost' => $request->unit_cost,
            'month' => $request->month,
            'sort' => $request->sort,
        ];

        if ($request->uninsured_item_history_id) {
            $params['uninsured_item_history_id'] = $request->uninsured_item_history_id;
        }
        if ($request->uninsured_request_id) {
            $params['uninsured_request_id'] = $request->uninsured_request_id;
        }

        $uninsuredService = new UninsuredService();
        $res = $uninsuredService->saveRow($params);

        if ($res) {
            return $res;
        }
        abort(500);
    }

    public function delete(GetFaclityUserInformationRequest $request)
    {
        $params = [
            "facility_user_id" => $request->facility_user_id,
            'month' => $request->month,
            "uninsured_request_id" => $request->id,
        ];

        $uninsuredService = new UninsuredService();
        $res = $uninsuredService->delete($params);

        if ($res) {
            return [];
        }
        abort(500);
    }

    public function saveCell(UninsuredManagementRequest $request)
     {
         $params = [];
         $params["uninsured_request_id"] = $request->id;
         $params["quantity"] = $request->quantity ?? 0;
         $params["date_of_use"] = $request->date;

        $uninsuredService = new UninsuredService();
        $res = $uninsuredService->saveCell($params);

        if ($res) {
            return [];
        }
        abort(500);
    }

    public function getUserPublicInfo(GetFaclityUserInformationRequest $request){
        $carbon = new Carbon($request->year. "-". $request->month. "-01");
        $lastDate = $carbon->lastOfMonth()->format("Y-m-d");
        $carbon->firstOfMonth();

        $params = [
            'facility_user_id' => $request->facility_user_id,
            'first_of_month' => $carbon->format("Y-m-d"),
            'last_of_month' => $lastDate
        ];

        $uninsuredService = new UninsuredService();
        return $uninsuredService->getUserPublicInfo($params);
    }
}
