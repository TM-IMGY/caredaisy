<?php

namespace App\Service\GroupHome;

use App\Lib\Common\Consts;
use App\Models\Approval;
use App\Models\UserFacilityServiceInformation;
use App\Models\UninsuredItemHistory;
use App\Models\UninsuredRequest;
use App\Models\UninsuredRequestDetail;

use Carbon\Carbon;

/**
 * 夜間バッチで実行される自動請求処理のテーブル操作を行うクラス
 */
class AutoBillingTable
{
    /**
     * 施設利用者の保険外請求を実行する
     * @param array $params key: facility_user_id, year, month
     * @return void
     */
    public function executeUninsuredBilling(array $params): void
    {
        $facilityUserId = $params['facility_user_id'];
        $year = $params['year'];
        $month = $params['month'];

        $targetDate = new Carbon("${year}-${month}-1");
        $targetYm = $targetDate->format('Y-m-d');
        $targetYmDaysInMonth = $targetDate->daysInMonth;

        // 施設利用者の対象月の保険外請求レコードを取得する(sort昇順にする)
        $uninsuredRequests = UninsuredRequest::where('facility_user_id', $facilityUserId)
            ->where('month', $targetYm)
            ->select('id', 'sort', 'uninsured_item_history_id', 'unit_cost')
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();

        // 保険外品目履歴からidの取得する。
        $uninsuredItemHistoryIds = array_column($params['uninsured_item_histories'], 'id');
        // 保険外請求から保険外品目履歴のIDのリストを取得する
        $registerdHistoryIds = array_column($uninsuredRequests, 'uninsured_item_history_id');
        // 保険外品目履歴を元に保険外請求との比較をし、保険外請求にないuninsured_item_history_idを取得する
        $diffHistoryIds = array_diff($uninsuredItemHistoryIds, $registerdHistoryIds);
        // 保険外請求にないuninsured_item_history_idが取得できたかチェック
        if (count($diffHistoryIds) !== 0) {
            // 配列のキーを振り直し
            $diffHistoryIds = array_values($diffHistoryIds);
        }
        // 保険外請求のIDを取得する(array_columnの第3引数のパラメータにnull値が複数あった場合、idがずれていたため修正)
        $requestIds = array_column($uninsuredRequests, 'id');
        // 保険外請求の更新による表示順
        $updateSortIndex = count($diffHistoryIds) + 1;
        // 保険外請求の新規による表示順
        $insertSortIndex = 1;

        // 保険外請求の情報を更新する。
        for ($i = 0, $cnt = count($registerdHistoryIds); $i < $cnt; $i++) {
            // 値が入っているか確認
            if (!is_null($registerdHistoryIds[$i])) { // 保険外請求の「品目リストから選ぶ」から登録したデータ
                //$indexの確認
                $targetIndex = array_search($registerdHistoryIds[$i],  $uninsuredItemHistoryIds);
                // 取得したIndexから対象の保険外品目履歴情報を取得する。
                $itemHistory = $params['uninsured_item_histories'][$targetIndex];
                $unitCost = $itemHistory['unit_cost'];
                // 新規で登録するデータがないかチェック
                if (count($diffHistoryIds) !== 0) {
                    // 保険外請求の表示順を変更する
                    UninsuredRequest::where('id', $requestIds[$i])
                        ->update([
                            'sort' => $updateSortIndex,
                        ]);
                }
            } else { // 保険外請求の「品目追加する」から登録したデータ
                // 新規で登録するデータがないかチェック
                if (count($diffHistoryIds) !== 0) {
                    // 保険外請求の表示順を変更する。
                    UninsuredRequest::where('id', $requestIds[$i])
                        ->update([
                            'sort' => $updateSortIndex,
                        ]);
                }
            }
            $updateSortIndex++;
        }
        // 登録されていない保険外品目を保険外請求へ新規登録する。
        for ($j = 0, $cnt2 = count($diffHistoryIds); $j < $cnt2; $j++) {
            // $indexの確認
            $targetIndex = array_search($diffHistoryIds[$j],  $uninsuredItemHistoryIds);
            // 取得したIndexから対象の保険外品目履歴情報を取得する。
            $itemHistory = $params['uninsured_item_histories'][$targetIndex];
            $unitCost = $itemHistory['unit_cost'];

            $requestId = UninsuredRequest::insertGetId([
                'facility_user_id' => $facilityUserId,
                'month' => $targetYm,
                'name' => null,
                'sort' => $insertSortIndex,
                'uninsured_item_history_id' => $diffHistoryIds[$j],
                'unit_cost' => $unitCost === null ? 0 : $unitCost,
            ]);

            // 保険外請求の詳細
            // 全ての実績を立てるフラグ
            $isResultAll = $itemHistory['set_one'] == Consts::VALID;
            // 月初に実績を立てるフラグ
            $isResultMonthStart = $itemHistory['unit'] == UninsuredItemHistory::UNIT_MONTH;
            for ($day = 1; $day <= $targetYmDaysInMonth; $day++) {
                $dateOfUse = new Carbon("${year}-${month}-${day}");
                $quantity = '0';
                if ($isResultAll || ($isResultMonthStart && $day === 1)) {
                    $quantity = '1';
                }
                UninsuredRequestDetail::insert([
                    'date_of_use' => $dateOfUse,
                    'quantity' => $quantity,
                    'uninsured_request_id' => $requestId,
                ]);
            }
            $insertSortIndex++;
        }
    }

    /**
     * 国保連請求が未請求の施設利用者の情報を配列で返す
     * 施設利用者の内
     * 入居日が存在し、かつ対象月には施設に入っている日が一日でもある
     * 退去日がNULLまたは、対象月には施設に入っている日が一日でもある
     * 退去日がNULLまたは、対象月には生存している日が一日でもある
     * また対象月のレコードがi_service_resultsにない(請求処理未実行判定)
     * 上記の条件に合致する者を返す
     * @param array $params key: year, month
     * @return array
     */
    public function getUnbilledFacilityUsers(array $params): array
    {
        $year = $params['year'];
        $month = $params['month'];
        $targetDate = "${year}-${month}-01";
        $lastDate = date('Y-m-d', strtotime('last day of ' . "${year}-${month}"));

        $sql = <<<SQL
        SELECT
            facility_user_id
        FROM
            i_service_results
        where
            target_date = ?
        group by
            facility_user_id
        SQL;

        $serviceResultsUser = array_column(\DB::select($sql, [$targetDate]), 'facility_user_id');
        $inClause = '';
        if (count($serviceResultsUser) > 0) {
            $inClause = 'AND fu.facility_user_id NOT IN (' . substr(str_repeat(', ?', count($serviceResultsUser)), 1) . ')';
        }

        $dbName = config('database.connections.confidential.database');

        $sql = <<<SQL
        SELECT
            ufi.facility_id,
            fu.facility_user_id
        FROM
            {$dbName}.i_facility_users fu
        JOIN
            i_user_facility_informations ufi
            ON fu.facility_user_id = ufi.facility_user_id
        where
            fu.start_date <= ?
            AND (fu.end_date is NULL OR fu.end_date >= ?)
            AND (fu.death_date is NULL OR fu.death_date >= ?)
            {$inClause}
        SQL;

        $values = array_merge([$lastDate], [$targetDate], [$targetDate], $serviceResultsUser);
        $bases = \DB::select($sql, $values);

        $unbilledUserList = array();
        for ($i = 0; $i < count($bases); $i++) {
            $unbilledUserList[$i]['facility_id'] = $bases[$i]->facility_id;
            $unbilledUserList[$i]['facility_user_id'] = $bases[$i]->facility_user_id;
        }

        return $unbilledUserList;
    }

    /**
     * 保険外請求が未請求の施設利用者の情報を配列で返す
     * 施設利用者の内
     * 入居日が存在し、かつ対象月には施設に入っている日が一日でもある
     * 退去日がNULLまたは、対象月には施設に入っている日が一日でもある
     * 退去日がNULLまたは、対象月には生存している日が一日でもある
     * 上記の条件に合致する者を返す
     * @param array $params key: year, month
     * @return array
     */
    public function getUnbilledUninsuredFacilityUsers(array $params): array
    {
        $year = $params['year'];
        $month = $params['month'];
        $targetDate = "${year}-${month}-01";
        $lastDate = date('Y-m-d', strtotime('last day of ' . "${year}-${month}"));
        $dbName = config('database.connections.confidential.database');

        $sql = <<<SQL
        SELECT
            ufi.facility_id,
            fu.facility_user_id
        FROM
            {$dbName}.i_facility_users fu
        JOIN
            i_user_facility_informations ufi
            ON fu.facility_user_id = ufi.facility_user_id
        where
            fu.start_date <= ?
            AND (fu.end_date is NULL OR fu.end_date >= ?)
            AND (fu.death_date is NULL OR fu.death_date >= ?)
        SQL;

        $values = array_merge([$lastDate], [$targetDate], [$targetDate]);
        $bases = \DB::select($sql, $values);

        $unbilledUserList = array();
        for ($i = 0; $i < count($bases); $i++) {
            // 保険外請求が未承認の利用者のみを対象とする
            $approval = Approval::getApproval($bases[$i]->facility_id, $bases[$i]->facility_user_id, $targetDate, Approval::UNINSURED_APPROVAL_TYPE);
            if (!$approval) {
                $unbilledUserList[$i]['facility_id'] = $bases[$i]->facility_id;
                $unbilledUserList[$i]['facility_user_id'] = $bases[$i]->facility_user_id;
            }
        }

        return array_values($unbilledUserList);
    }

    /**
     * 施設利用者が事業所より提供されているサービスの情報を取得する
     * @param array $params key: facility_id, facility_user_id, year, month
     * @return array
     */
    public function getFacilityUserService(array $params): array
    {
        $userFacilityServiceInformations = UserFacilityServiceInformation::yearMonth($params['year'], $params['month'])
            ->where('facility_id', $params['facility_id'])
            ->where('facility_user_id', $params['facility_user_id'])
            ->where('usage_situation', 1)
            ->select('service_id')
            ->get()
            ->toArray();

        // グループホームでは施設利用者が提供を受ける事業所と、そのサービスの種別は一つのみを想定する
        if (count($userFacilityServiceInformations) !== 1) {
            throw new \Exception('the registered user facility service information is incorrect');
        }
        return $userFacilityServiceInformations[0];
    }

    /**
     * サービス情報から保険外品目の履歴情報を返す
     * 他条件は下記の通り
     * 対象月が利用開始月から利用終了月の中にある、または対象月が利用開始月以降で利用終了月がnull
     * @param array $params key: service_id, year, month
     * @return array
     */
    public function getUninsuredItemHistory($params): array
    {
        $serviceId = $params['service_id'];
        $year = $params['year'];
        $month = $params['month'];
        $targetDate = "${year}-${month}-01";
        $lastDate = date('Y-m-d', strtotime('last day of ' . "${year}-${month}"));

        $sql = <<<SQL
        SELECT
            id
        FROM
            i_uninsured_items
        WHERE
            service_id = ?
            AND start_month <= ?
            AND (end_month is NULL OR end_month >= ?)
        SQL;

        $uninsuredItem = \DB::select($sql, [$serviceId, $targetDate, $lastDate]);

        // 保険外品目では期間を指定して取得できるレコードは一つのみを想定する
        if (count($uninsuredItem) !== 1) {
            throw new \Exception('the registered uninsured item is incorrect');
        }

        // 取得した保険外品目のIDから履歴を取得する
        // sortが初期値1でやれていることを考慮し、idの昇順（asc）も追加
        $uninsuredItemHistories = UninsuredItemHistory::where('uninsured_item_id', $uninsuredItem[0]->id)
            ->where('billing_reflect_flg', 0)
            ->select('id', 'set_one', 'unit', 'unit_cost')
            ->orderByRaw('sort ASC, id ASC')
            ->get()
            ->toArray();

        return $uninsuredItemHistories;
    }
}
