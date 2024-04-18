<?php

namespace App\Service\GroupHome;

use App\Models\MFacilityUser;
use App\Models\StayOutManagement;
use App\Service\GroupHome\FacilityUserService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class StayOutService
{
    public function stayOutList(array $params)
    {
        $now = new \Datetime();
        $now_date = $now -> format('Y-m-d H:i:s');

        $stayOutList = StayOutManagement::where("facility_user_id", $params["facility_user_id"])
            ->orderBy('start_date', 'desc')
            ->get();

        if (!$stayOutList) {
            return null;
        }

        $list = [];
        foreach ($stayOutList as $row) {
            $startDatetime = $row->start_date;
            $date = new \Datetime($startDatetime);
            $row->start_date = $date->format("Y/m/d H:i");
            $startTimestamp = $date->getTimestamp();
            $endDatetime = $row->end_date;
            if (!is_null($endDatetime)) {
                $date = new \Datetime($endDatetime);
                $row->end_date = $date->format("Y/m/d H:i");
            } else {
                $row->end_date = "";
            }
            $endTimestamp = $date->getTimestamp();

            $array = $row->toArray();
            $array["stayout_now"] = false;
            if ($startTimestamp <= $now->getTimestamp() && $now->getTimestamp() < $endTimestamp) {
                $array["stayout_now"] = true;
            }
            $list[] = $array;
        }
        return $list;
    }

    public function stayOutDetail(array $params)
    {
        $stayOutData = StayOutManagement::find($params["id"]);
        if (!$stayOutData) {
            return null;
        }

        $startDatetime = $stayOutData->start_date;
        $date = new \Datetime($startDatetime);
        $stayOutData->start_date = $date->format("Y-m-d H:i");
        $endDatetime = $stayOutData->end_date;

        if ($endDatetime) {
            $date = new \Datetime($endDatetime);
            $stayOutData->end_date = $date->format("Y-m-d H:i");
        } else {
            $stayOutData->end_date = "";
        }

        return $stayOutData->toArray();
    }

    public function delete(int $id)
    {
        return StayOutManagement::destroy($id);
    }

    public function save(array $params)
    {
        $params["start_date"] = $params["start_date"]. " ". $params["start_time"];
        $params["remarks_reason_for_stay_Out"] = "";
        unset($params["start_time"]);

        if (!empty($params["end_date"]) && !empty($params['end_time'])) {
            $params["end_date"] = $params["end_date"]. " ". $params["end_time"];
        }
        unset($params["end_time"]);

        if (empty($params["id"])) {
            $res = StayOutManagement::insert($params);
        } else {
            $row = StayOutManagement::find($params["id"]);
            $res = $row->update($params);
        }
        return $res;
    }

    /**
     * 外泊日のリストを返す
     * @param array $params
     * @param string $params['facility_user_id']
     * @param string $params['first_of_month'] Y-m-d 00:00:00
     * @param string $params['last_of_month'] Y-m-d 23:59:59
     * @return array
     */
    public function getStayoutDays($params): array
    {
        $dateList = [];

        $query = <<< SQL
SELECT
  `start_date`,
  `end_date`
FROM
  `i_stay_out_managements`
WHERE
  `facility_user_id` = ?
  AND `start_date` <= ?
  AND (`end_date` >= ? OR `end_date` is null )
SQL;

        // 施設利用者の入院データを取得する
        $targetStartDate = new CarbonImmutable($params['first_of_month']);
        $targetEndDate = $targetStartDate->endOfMonth();
        $stayouts = \DB::select($query, [
            $params['facility_user_id'],
            $targetEndDate->format('Y-m-d H:i:s'),
            $targetStartDate->format('Y-m-d H:i:s'),
        ]);

        // 利用者の退居日を取得する
        $param = [
            'clm' => ['end_date','facility_user_id'],
            'facility_user_id_list' => [$params['facility_user_id']]
        ];
        $fuService = new FacilityUserService();
        $fuData = $fuService->getData($param)[0];

        $sMonth = new CarbonImmutable($params["first_of_month"]);
        $eMonth = new CarbonImmutable($params["last_of_month"]);
        foreach ($stayouts as $stayout) {
            $startDate = new Carbon($stayout->start_date);

            // 退去日を取得する。
            $moveOut = null;
            if ($fuData['end_date'] !== null) {
                // 退居日は時間の入力がないため0とする。
                $moveOut = (new Carbon($fuData['end_date']))->endOfDay()->seconds(0);
            }

            // 外泊終了日を取得する
            // 外泊終了日がnullの場合は対象年月の翌月初日を仮で入れる
            // 外泊終了日がnullで退居日が登録されていたら退居日の23:59:00を設定する
            // 外泊終了日が退居日より後の場合は終了日を退居日の23:59:00として設定する
            // 外泊登録画面での終了日登録の秒は設定できないので「seconds(0)」で統一する
            $endDate = null;
            if ($stayout->end_date !== null) {
                $endDate = new Carbon($stayout->end_date);
                if ($moveOut !== null && $moveOut->lt($endDate)) {
                    $endDate = $moveOut;
                }
            } else {
                $endDate = (new Carbon($params['last_of_month']))->endOfMonth()->seconds(0)->addDay();
                if ($moveOut!== null) {
                    $endDate = $moveOut;
                }
            }

            // 1秒でも施設にいれば開始日をカウントしない
            $startDateStartOfDay = $startDate->copy()->startOfDay();
            if ($startDateStartOfDay->lt($startDate)) {
                $startDate->addDay()->startOfDay();
            }

            // 1秒でも施設にいれば終了日をカウントしない
            $endDateEndOfDay = $endDate->copy()->endOfDay()->seconds(0);
            if ($endDate->lt($endDateEndOfDay)) {
                $endDate->subDay()->endOfDay()->seconds(0);
            }

            $current = $startDate->copy();
            while ($current->timestamp < $eMonth->timestamp) {
                $inStayoutRange = ($startDate->timestamp <= $current->timestamp) && ($current->timestamp <= $endDate->timestamp);
                $afterStartOfMonth = $sMonth->timestamp <= $current->timestamp;
                if ($afterStartOfMonth && $inStayoutRange) {
                    $dateList[] = $current->day;
                }
                $current->addDay();
            }
        }
        return $dateList;
    }

    /**
     * 外泊期間を返す
     *
     * @return array
     */
    public function getStayoutPeriod($params): array
    {
        $targetDate = new CarbonImmutable($params['target_ym'].'01');

        $query = <<< SQL
SELECT
  `start_date`,
  `end_date`
FROM
  `i_stay_out_managements`
WHERE
  `facility_user_id` = ?
  AND `start_date` <= ?
  AND (`end_date` >= ? OR `end_date` is null )
SQL;

        $stayoutPeriods = \DB::select($query, [
            $params['facility_user_id'],
            $targetDate->endOfMonth()->format('Y-m-d H:i:s'),
            $targetDate->format('Y-m-d H:i:s'),
        ]);

        foreach ($stayoutPeriods as $key => $value) {
            $sort_keys[$key] = $value->start_date;
        }

        array_multisort($sort_keys, SORT_ASC, $stayoutPeriods);

        return $stayoutPeriods;
    }
}
