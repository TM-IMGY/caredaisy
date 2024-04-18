<?php

namespace App\Service\GroupHome;

use App\Models\MFacilityUser;
use App\Models\UninsuredManagements;
use App\Models\UserFacilityServiceInformation;
use App\Models\UninsuredRequest;
use App\Models\UninsuredRequestDetail;
use App\Models\UninsuredItem;
use App\Models\UninsuredItemHistory;
use Exception;

class UninsuredService
{
    /*
     * カレンダーの内容及びその行を取得
     */
    public function uninsuredList(array $params)
    {
        $list = [];

        $urList = UninsuredRequest::where("facility_user_id", $params["facility_user_id"])
            ->where("month", $params["month"])
            ->orderBy("sort", "asc")->get();

        foreach ($urList as $ur) {
            $item = [];

            if ($ur->uninsured_item_history_id != null) {
                $editedItem = UninsuredItemHistory::find($ur->uninsured_item_history_id);
                $originalItem = UninsuredItem::find($editedItem->uninsured_item_id);
                $item["uninsured_history_id"] = $ur->uninsured_item_history_id;
                $item["name"] = $editedItem->item;
            } else {
                // 新規品目として作られたレコード
                $item["uninsured_history_id"] = null;
                $item["name"] = $ur->name;
            }
            $dList = UninsuredRequestDetail::where("uninsured_request_id", $ur->id)->orderBy("date_of_use", "asc")->get()->toArray();

            $details = [];
            foreach ($dList as $row) {
                $details[$row["date_of_use"]] = $row;
            }

            $item["unit_cost"] = $ur->unit_cost;
            $item["uninsured_request_id"] = $ur->id;
            $item["sort"] = $ur->sort;

            $tmp = [];
            $tmp["item"] = $item;
            $tmp["details"] = $details;
            $list[] = $tmp;
        }
        return $list;
    }


    /*
     * 保険外請求の品目リストを取得
     */
    public function uninsuredItemList($facilityUserId, $year, $month)
    {
        try {
            // facilityUserIdをキーにservice_idほしい
            // 利用中のサービス取得
            $relation = UserFacilityServiceInformation::listFacilityUserTargetMonth($facilityUserId, $year, $month);

            if (!$relation) {
                return [];
            }

            $targetMonth = $year.'-'.$month.'-01'; // Y-m-d

            // 対象となる月の保険外請求品目リスト絞り込み
            $serviceId = $relation[0]['service_id'];
            $uninsuredItem = UninsuredItem::where("service_id", $serviceId)
                ->where("start_month", "<=", $targetMonth)
                ->whereRaw('((end_month is null) or (end_month > ?))', [$targetMonth])
                ->first();
            if (!$uninsuredItem) {
                return [];
            }

            $uninsuredItemId = $uninsuredItem->id;

            // 保険外請求品目の取得
            $uninsuredItemHistories = UninsuredItemHistory::where("uninsured_item_id", $uninsuredItemId)
                ->orderBy('sort', 'asc')
                ->get()
                ->toArray();

            $list = [];
            foreach ($uninsuredItemHistories as $row) {
                $list[] = $row;
            }
        } catch (Exception $e) {
            report($e);
            return [];
        }
        return $list;
    }

    public function delete (array $params)
    {
        try {
            $uninsuredRequest = UninsuredRequest::where("id", $params["uninsured_request_id"])
                ->where("month", $params["month"])
                ->where("facility_user_id", $params["facility_user_id"])
                ->first();

            if ($uninsuredRequest) {
                $uninsuredRequest->delete();

                $uninsuredRequests = UninsuredRequest::where("facility_user_id", $params["facility_user_id"])
                    ->where("month", $params["month"])
                    ->orderBy("sort", "asc")
                    ->get();

                $index = 1;
                foreach ($uninsuredRequests as $req) {
                    $req->update(["sort" => $index]);
                    $index++;
                }
            }
        } catch (Exception $e) {
            report($e);
            return false;
        }
        return true;
    }

    public function saveCell (array $params)
    {
        // catch等々どうするか未定だが一応用意しておく
        try {
            $cell = UninsuredRequestDetail::where("uninsured_request_id", $params["uninsured_request_id"])
                ->where("date_of_use", $params["date_of_use"])->first();

            $values = [];

            if ($cell) {
                $values["quantity"] = $params["quantity"];
                $cell->update($values);
            } else {
                $values["uninsured_request_id"] = $params["uninsured_request_id"];
                $values["date_of_use"] = $params["date_of_use"];
                $values["quantity"] = $params["quantity"];
                UninsuredRequestDetail::insert($values);
            }
        } catch (Exception $e) {
            report($e);
            return false;
        }
        return true;
    }

    public function saveRow (array $params)
    {
        // catch等々どうするか未定だが一応用意しておく
        try {
            $values = [];
            $values['month'] = $params['month'];
            $values["sort"] = $params['sort'];

            if (isset($params["uninsured_item_history_id"])) {
                $values["uninsured_item_history_id"] = $params["uninsured_item_history_id"] == "null" ? null : $params["uninsured_item_history_id"];
            }
            if (isset($params['unit_cost'])) {
                $values['unit_cost'] = $params['unit_cost'];
            }

            if (isset($params["uninsured_request_id"]) && is_numeric($params["uninsured_request_id"])) {
                $query = UninsuredRequest::where("facility_user_id", $params["facility_user_id"])->where("month", $params["month"]);
                $query = $query->where("id", $params["uninsured_request_id"]);
                $model = $query->first();
                $model->update($values);
            } else {
                $values['facility_user_id'] = $params['facility_user_id'];
                $id = UninsuredRequest::insertGetId($values);
                $model = UninsuredRequest::find($id);
            }
        } catch (Exception $e) {
            report($e);
            return [];
        }
        return $model->toArray();
    }

    public function saveItem (array $params)
    {
        try {
            $id = UninsuredRequest::insertGetId($params);
            $model = UninsuredRequest::find($id);
        } catch (Exception $e) {
            report($e);
            return [];
        }
        return $model->toArray();
    }

    // 利用者の優先度の高い法別番号取得
    public function getUserPublicInfo (array $params)
    {
        $facilityUserId = $params['facility_user_id'];
        $firstDate = $params['first_of_month'];
        $lastDate = $params['last_of_month'];

        $sql = <<< SQL
SELECT DISTINCT
    ps.priority,
    ps.legal_number
FROM
    i_user_public_expense_informations upei
JOIN m_public_spendings ps
ON LEFT(upei.bearer_number,2) = ps.legal_number
WHERE
    upei.facility_user_id = ?
    AND upei.effective_start_date <= ?
    AND upei.expiry_date >= ?
ORDER BY ps.priority
SQL;

        $userPublicInfo = \DB::select($sql, [$facilityUserId, $lastDate, $firstDate]);

        return ['legal_number' => $userPublicInfo ? $userPublicInfo[0]->legal_number : null];
    }
}
