<?php

namespace App\Http\Requests;

use App\Models\CareReward;
use App\Models\CareRewardHistory;
use App\Models\CorporationAccount;
use App\Models\Facility;
use App\Models\FirstServicePlan;
use App\Models\Institution;
use App\Models\Invoice;
use App\Models\ReturnDocument;
use App\Models\SecondServicePlan;
use App\Models\Service;
use App\Models\ServicePlan;
use App\Models\StayOutManagement;
use App\Models\UninsuredItem;
use App\Models\UninsuredItemHistory;
use App\Models\UninsuredRequest;
use App\Models\UserFacilityInformation;
use App\Models\UserFacilityServiceInformation;
use App\Models\UserIndependenceInformation;
use Illuminate\Foundation\Http\FormRequest;

class CareDaisyBaseFormRequest extends FormRequest
{
    /**
     * ログインユーザーが、リクエストした介護報酬履歴IDへのアクセス権を返す
     * @return bool
     */
    protected function authorizeCareRewardHistoryId($careRewardHistoryId, $facilityId)
    {
        // 介護報酬履歴情報が事業所情報とリレーションしていないため、まずサービス情報を取得する
        $services = Service::where('facility_id', (int)$facilityId)
            ->select('id')
            ->get()
            ->toArray();
        if (count($services) == 0) {
            return false;
        }

        // サービスから介護報酬IDを全て取得する
        $careRewards = CareReward::whereIn('service_id', array_column($services, 'id'))
            ->select('id')
            ->get()
            ->toArray();
        if (count($careRewards) == 0) {
            return false;
        }

        // 介護報酬履歴IDから介護報酬履歴を全て取得する
        $careRewardHistories = CareRewardHistory::whereIn('care_reward_id', array_column($careRewards, 'id'))
            ->get()
            ->toArray();
        $careRewardHistoryIds = array_column($careRewardHistories, 'id');

        if (!in_array($careRewardHistoryId, $careRewardHistoryIds)) {
            return false;
        }
        $careRewardId = CareRewardHistory::select('care_reward_id')->find($careRewardHistoryId);
        $serviceId = CareReward::select('service_id')->find($careRewardId->care_reward_id);
        $selectFacilityId = Service::select('facility_id')->find($serviceId->service_id);

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $selectFacilityId);
    }

    /**
     * ログインユーザーが、リクエストしたサービス計画IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeServicePlanId($servicePlanId, $facilityUserId = null)
    {
        // 介護計画書を取得する
        $servicePlan = ServicePlan::where('id', $servicePlanId)->select('facility_user_id')->first();

        // 介護計画書が存在しない場合は施設利用者IDをパラメーターから取得する(新規作成など)
        $selectFacilityUserId = $servicePlan ? $servicePlan->facility_user_id : $facilityUserId;
        if ($selectFacilityUserId == null) {
            return false;
        }

        $facilityId = UserFacilityInformation::where('facility_user_id', $selectFacilityUserId)->select('facility_id')->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストしたサービス計画2IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeServicePlan2Id($servicePlan2, $servicePlanId)
    {
        // リクエストパラメータから受け取った$servicePlanIdからデータを取得する。
        $servicePlan = ServicePlan::with([
            'secondServicePlan' => function ($query) {
                $query->with([
                    'servicePlanNeeds' => function ($query) {
                        $query->with([
                            'serviceLongPlans' => function ($query) {
                                $query->with([
                                    'serviceShortPlans' => function ($query) {
                                        $query->with([
                                            'servicePlanSupports'
                                        ]);
                                    }
                                ]);
                            }
                        ]);
                    }
                ]);
            }
        ])->find($servicePlanId);

        // $servicePlan2のリクエスト情報のキー存在するかチェック
        if (!array_key_exists('second_service_plan_id', $servicePlan2)) {
            return false;
        }

        // データ取得した$servicePlanの情報にリクエストで送られたsecond_service_plan_idが存在するかチェック
        $checkSecondServicePlan = $servicePlan->secondServicePlan
            ->firstWhere('id', $servicePlan2['second_service_plan_id']);
        if ($checkSecondServicePlan == null) {
            return false;
        }
        // 既存データのユニークキー重複チェック用変数
        $needUnique = array();
        $longPlanUnique = array();
        $shortPlanUnique = array();
        $suppotUnique = array();

        // 各階層ごとの権限チェック
        foreach ($servicePlan2['need_list'] as $need) { //サービス計画書ニーズ
            $needNewRecordCheck = false;
            // $needのリクエスト情報のキー存在チェック
            if (
                array_key_exists('second_service_plan_id', $need) &&
                array_key_exists('service_plan_need_id', $need)
            ) {
                // $checkSecondServicePlanの情報にリクエストで送られたservice_plan_need_idが存在するかチェック
                $checkNeed = $checkSecondServicePlan->servicePlanNeeds
                    ->firstWhere('id', $need['service_plan_need_id']);
                if ($checkNeed == null) {
                    return false;
                }
                // ユニークキーの値を配列へ格納
                array_push($needUnique, $need['service_plan_need_id']);
            } else { // サービス計画ニーズから新規登録だった場合の処理
                // $needのリクエスト情報のキー存在チェック
                if (!array_key_exists('second_service_plan_id', $need)) {
                    return false;
                }
                $needNewRecordCheck = true;
            }
            // 共通処理
            // リクエストの親階層のパラメータと一致しているかチェック
            if ($servicePlan2['second_service_plan_id'] !== $need['second_service_plan_id']) {
                return false;
            }
            foreach ($need['long_plan_list'] as $longPlan) { // サービス計画長期
                $longPlanNewRecordCheck = false;
                // 親階層が新規レコードかのチェック
                if (!$needNewRecordCheck) {
                    // $longPlanのリクエスト情報のキー存在チェック
                    if (
                        array_key_exists('service_long_plan_id', $longPlan) &&
                        array_key_exists('service_plan_need_id', $longPlan)
                    ) {
                        // $checkNeedの情報にリクエストで送られたservice_long_plan_idが存在するかチェック
                        $checklongPlan = $checkNeed->serviceLongPlans
                            ->firstWhere('id', $longPlan['service_long_plan_id']);
                        if ($checklongPlan == null) {
                            return false;
                        }
                        // リクエストの親階層のパラメータと一致しているかチェック
                        if ($need['service_plan_need_id'] !== $longPlan['service_plan_need_id']) {
                            return false;
                        }
                        // ユニークキーの値を配列へ格納
                        array_push($longPlanUnique, $longPlan['service_long_plan_id']);
                    } else { // サービス計画長期から新規登録だった場合の処理
                        if (
                            array_key_exists('service_long_plan_id', $longPlan) ||
                            array_key_exists('service_plan_need_id', $longPlan)
                        ) {
                            return false;
                        }
                        $longPlanNewRecordCheck = true;
                    }
                } else { // 親階層から新規登録だった場合の処理
                    // $longPlanのリクエスト情報のキー存在チェック
                    if (
                        array_key_exists('service_long_plan_id', $longPlan) ||
                        array_key_exists('service_plan_need_id', $longPlan)
                    ) {
                        return false;
                    }
                    $longPlanNewRecordCheck = true;
                }
                foreach ($longPlan['short_plan_list'] as $shortPlan) { // サービス計画短期
                    $shortPlanNewRecordCheck = false;
                    // 親階層が新規かどうかのチェック
                    if (!($needNewRecordCheck || $longPlanNewRecordCheck)) {
                        // $shortPlanのリクエスト情報のキー存在チェック
                        if (
                            array_key_exists('service_short_plan_id', $shortPlan) &&
                            array_key_exists('service_long_plan_id', $shortPlan)
                        ) {
                            // checklongPlanの情報にリクエストで送られたservice_short_plan_idが存在するかチェック
                            $checkShortPlan = $checklongPlan->serviceShortPlans
                                ->firstWhere('id', $shortPlan['service_short_plan_id']);
                            if ($checkShortPlan == null) {
                                return false;
                            }
                            // リクエストの親階層のパラメータと一致しているかチェック
                            if ($longPlan['service_long_plan_id'] !== $shortPlan['service_long_plan_id']) {
                                return false;
                            }
                            // ユニークキーの値を配列へ格納
                            array_push($shortPlanUnique, $shortPlan['service_short_plan_id']);
                        } else { //サービス計画短期の階層から新規登録だった場合の処理
                            // $shortPlanのリクエスト情報のキー存在チェック
                            if (
                                array_key_exists('service_short_plan_id', $shortPlan) ||
                                array_key_exists('service_long_plan_id', $shortPlan)
                            ) {
                                return false;
                            }
                            $shortPlanNewRecordCheck = true;
                        }
                    } else { // 親階層から新規登録だった場合の処理
                        // $shortPlanのリクエスト情報のキー存在チェック
                        if (
                            array_key_exists('service_short_plan_id', $shortPlan) ||
                            array_key_exists('service_long_plan_id', $shortPlan)
                        ) {
                            return false;
                        }
                        $shortPlanNewRecordCheck = true;
                    }
                    foreach ($shortPlan['support_list'] as $support) { //サービス計画サポート
                        // 親階層が新規かどうかのチェック
                        if (!($needNewRecordCheck || $longPlanNewRecordCheck || $shortPlanNewRecordCheck)) {
                            // $supportのリクエスト情報のキー存在チェック
                            if (
                                array_key_exists('service_plan_support_id', $support) &&
                                array_key_exists('service_short_plan_id', $support)
                            ) {
                                // checklongPlanの情報にリクエストで送られたservice_plan_support_idが存在するかチェック
                                $checkSupport = $checkShortPlan->servicePlanSupports
                                    ->firstWhere('id', $support['service_plan_support_id']);
                                if ($checkSupport == null) {
                                    return false;
                                }
                                // リクエストの親階層のパラメータと一致しているかチェック
                                if ($shortPlan['service_short_plan_id'] !== $support['service_short_plan_id']) {
                                    return false;
                                }
                                // ユニークキーの値を配列へ格納
                                array_push($suppotUnique, $support['service_plan_support_id']);
                            } else { // サービス計画サポートの階層から新規登録だった場合の処理
                                if (
                                    array_key_exists('service_plan_support_id', $support) ||
                                    array_key_exists('service_short_plan_id', $support)
                                ) {
                                    return false;
                                }
                            }
                        } else { // 親階層から新規登録だった場合の処理
                            // $supportのリクエスト情報のキー存在チェック
                            if (
                                array_key_exists('service_plan_support_id', $support) ||
                                array_key_exists('service_short_plan_id', $support)
                            ) {
                                return false;
                            }
                        }
                    }
                }
            }
        }
        // 既存データとして権限チェックされたユニークキーが重複していないかをチェック
        if (
            max(array_count_values($needUnique)) > 1 ||
            max(array_count_values($longPlanUnique)) > 1 ||
            max(array_count_values($shortPlanUnique)) > 1 ||
            max(array_count_values($suppotUnique)) > 1
        ) {
            return false;
        }

        // 上記が通った場合に$servicePlan2['second_service_plan_id']に紐づく権限チェックを行う
        $selectServiceId = SecondServicePlan::select('service_plan_id')
            ->find($servicePlan2['second_service_plan_id']);
        $facilityUserId = ServicePlan::select('facility_user_id')
            ->find($selectServiceId->service_plan_id);
        $facilityId = UserFacilityInformation::where('facility_user_id', $facilityUserId->facility_user_id)
            ->select('facility_id')->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストしたサービス計画1IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeServicePlan1Id($firstServicePlanId)
    {

        $firstServicePlan = FirstServicePlan::where('id', $firstServicePlanId)
            ->select('service_plan_id')->first();
        // 介護計画書1がまだ存在しない場合はアクセス権を承認する
        if ($firstServicePlan === null) {
            return true;
        }
        $servicePlanId = $firstServicePlan['service_plan_id'];

        $facilityUser = ServicePlan::where('id', $servicePlanId)
            ->select('facility_user_id')->first();
        $facilityId = UserFacilityInformation::where('facility_user_id', $facilityUser->facility_user_id)
            ->select('facility_id')->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした保険外請求IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeUninsuredRequests($uninsuredRequestId)
    {
        $facilityUserId = UninsuredRequest::where('id', $uninsuredRequestId)
            ->select('facility_user_id')->first();
        $facilityId = UserFacilityInformation::where('facility_user_id', $facilityUserId->facility_user_id)
            ->select('facility_id')->first();
        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした保険外品目IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeUninsuredItemId($uninsuredItemId)
    {
        $serviceId = UninsuredItem::select('service_id')->find($uninsuredItemId);
        $facilityId = Service::where('id', $serviceId->service_id)
            ->select('facility_id')->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした施設利用者IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeFacilityUserId($facilityUserId)
    {
        $facilityId = UserFacilityInformation::where('facility_user_id', $facilityUserId)
            ->select('facility_id')->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした施設利用者IDリストに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeFacilityUserIds($facilityUserIds): bool
    {
        $uniqueFacilityUserIds = array_unique($facilityUserIds);
        // 重複チェック
        if (count($uniqueFacilityUserIds) != count($facilityUserIds)) {
            return false;
        }
        // リクエストした施設利用者IDリストに紐づく事業所IDリストを取得する
        $userFacilityInformations = UserFacilityInformation::whereIn('facility_user_id', $facilityUserIds)
            ->select('facility_id')
            ->get()
            ->toArray();
        $targetFacilityIds = array_column($userFacilityInformations, 'facility_id');

        if (count($targetFacilityIds) === 0) {
            return false;
        }

        // ログインユーザーに紐づく事業所IDリストを取得する
        $AccessibleFacilityIds = $this->affiliationFacilities();

        // 検証対象の全ての施設利用者IDリストが、アクセス可能な施設利用者IDリストに含まれない時、偽を返す
        // TODO: 施設利用者が複数の事業所に所属する仕様になった場合、内容によってはfalseになる。
        for ($i = 0, $cnt = count($targetFacilityIds); $i < $cnt; $i++) {
            if (!in_array($targetFacilityIds[$i], $AccessibleFacilityIds, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * ログインユーザーが、リクエストした事業所IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeFacilityId($facilityId)
    {
        $facilityIds = $this->affiliationFacilities();

        if (in_array((int)$facilityId, $facilityIds, true) === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ログインユーザーが、リクエストしたサービス種別IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeServiceId($serviceId)
    {
        $facilityId = Service::where('id', $serviceId)
            ->select('facility_id')->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした利用者IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeFacilityIdFUserId($facilityUserId, $requestFacilityId)
    {
        $facilityId = UserFacilityInformation::where('facility_user_id', $facilityUserId)
            ->select('facility_id')->first();

        if ((int)$requestFacilityId != $facilityId->facility_id) {
            return false;
        }

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした保険外品目履歴IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeUninsuredItemHistoryId($id)
    {
        $uninsuredItemId = UninsuredItemHistory::select('uninsured_item_id')
            ->find($id);
        $serviceId = UninsuredItem::select('service_id')
            ->find($uninsuredItemId->uninsured_item_id);
        $facilityId = Service::select('facility_id')
            ->find($serviceId->service_id);

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした利用者毎の自立度情報IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeUserIndependenceInformationId($userIndependenceInformationsId)
    {
        $facilityUserId = UserIndependenceInformation::select('facility_user_id')
            ->find($userIndependenceInformationsId);
        $facilityId = UserFacilityInformation::where('facility_user_id', $facilityUserId->facility_user_id)
            ->select('facility_id')->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした利用者毎の利用事業所情報IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeUserFacilityServiceInformationId($userFacilityServiceInformationId)
    {
        $facilityId = UserFacilityServiceInformation::select('facility_id')
            ->find($userFacilityServiceInformationId);

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした外泊管理IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeStayOutManagementId($id)
    {
        $facilityUserId = StayOutManagement::select('facility_user_id')
            ->find($id);
        $facilityId = UserFacilityInformation::where('facility_user_id', $facilityUserId->facility_user_id)
            ->select('facility_id')->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした請求情報IDに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeInvoiceId($id)
    {
        $facilityNumber = Invoice::select('facility_number')
            ->find($id);
        $facilityId = Facility::where('facility_number', $facilityNumber->facility_number)
            ->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした請求情報のfile_pathに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeInvoiceFilePath($filePath)
    {

        $facilityNumber = Invoice::select('facility_number')->where('csv', $filePath)
            ->first();
        // 請求情報が取得できたかチェック
        if (!$facilityNumber) {
            return false;
        }
        $facilityId = Facility::where('facility_number', $facilityNumber->facility_number)
            ->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }


    /**
     * ログインユーザーが、リクエストした連絡文書情報IDに紐づく事業所にアクセス権があるかを返す(通知文書)
     * @return bool
     */
    protected function authorizeReturnDocumentIdForNotificationDocument($id)
    {
        $facilityNumber = ReturnDocument::select('facility_number', 'document_type')->find($id);
        // 連絡文書情報が取得できたかのチェック
        if (!$facilityNumber) {
            return false;
        }
        // 取得した連絡文書情報の文書タイプが通知文書かチェック
        if ($facilityNumber->document_type != ReturnDocument::DOCUMENT_TYPE_NOTIFICATION_DOCUMENT) {
            return false;
        }

        $facilityId = Facility::where('facility_number', $facilityNumber->facility_number)
            ->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした連絡文書情報IDに紐づく事業所にアクセス権があるかを返す(お知らせ)
     * @return bool
     */
    protected function authorizeReturnDocumentIdForNews($id)
    {
        $facilityNumber = ReturnDocument::select('facility_number', 'document_type')->find($id);
        // 連絡文書情報が取得できたかのチェック
        if (!$facilityNumber) {
            return false;
        }
        // 取得した連絡文書情報の文書タイプがお知らせかチェック
        if ($facilityNumber->document_type != ReturnDocument::DOCUMENT_TYPE_NEWS) {
            return false;
        }

        // 取得した事業所に*が含まれていた場合のチェック
        // ＊8桁以外来ることはないと思うが、断定できないため、冗長に書いておく
        if (strpos($facilityNumber->facility_number, '*') !== false) {
            // 取得した事業所番号に*が（連続8桁）含まれていた場合のチェック
            if (strpos($facilityNumber->facility_number, '********') !== false) {
                // ログインユーザーに紐づく事業所番号を取得する。
                $facilityNumbers = $this->affiliatedFacilitiyNumbers();
                if (count($facilityNumbers) !== 0) {
                    for ($i = 0; $i < count($facilityNumbers); $i++) {
                        // 一致している情報が存在するかチェック
                        if ($facilityNumber->facility_number === substr($facilityNumbers[$i], 0, 2) . '********') {
                            return true;
                        }
                    }
                    return false;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        $facilityId = Facility::where('facility_number', $facilityNumber->facility_number)
            ->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    /**
     * ログインユーザーが、リクエストした添付ファイル情報のIndexに紐づく事業所にアクセス権があるかを返す
     * @return bool
     */
    protected function authorizeReturnAttachmentIndex($index, $id)
    {
        $facilityNumber = ReturnDocument::select('facility_number', 'document_type', 'document_code')
            ->with([
                'returnAttachment' => function ($query) use ($index) {
                    $query->where('index', $index);
                }
            ])
            ->find($id);
        // リレーションで取得した添付ファイル情報が1件しか取れていないかチェック
        if (count($facilityNumber->returnAttachment) != 1) {
            return false;
        }

        // 取得した事業所番号に*が含まれていた場合のチェック
        // ＊8桁以外来ることはないと思うが、断定できないため、冗長に書いておく
        if (strpos($facilityNumber->facility_number, '*') !== false) {
            // 取得した事業所番号に*が（連続8桁）含まれていた場合のチェック
            if (strpos($facilityNumber->facility_number, '********') !== false) {
                // ログインユーザーに紐づく事業所番号を取得する。
                $facilityNumbers = $this->affiliatedFacilitiyNumbers();
                if (count($facilityNumbers) !== 0) {
                    for ($i = 0; $i < count($facilityNumbers); $i++) {
                        // 一致している情報が存在するかチェック
                        if ($facilityNumber->facility_number === substr($facilityNumbers[$i], 0, 2) . '********') {
                            return true;
                        }
                    }
                    return false;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        $facilityId = Facility::where('facility_number', $facilityNumber->facility_number)
            ->first();

        $facilityIds = $this->affiliationFacilities();

        return $this->checkAffiliation($facilityIds, $facilityId);
    }

    protected function checkAffiliation($facilityIds, $facilityId)
    {
        // 処理対象に紐づく事業所が、ログインユーザーに紐づいている事業所に含まれていればtrue
        if (in_array($facilityId->facility_id, $facilityIds, true) === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ログインユーザーに紐づいている事業所を取得
     * @return array 事業所IDリスト
     */
    protected function affiliationFacilities()
    {
        $corporationId = CorporationAccount::where('account_id', \Auth::id())
            ->select('corporation_id')
            ->first();

        $institutionIdList = Institution::where('corporation_id', $corporationId->corporation_id)
            ->select('id')
            ->get()->map(function ($item) {
                return $item->id;
            });
        $facilityIdList = Facility::whereIn('institution_id', $institutionIdList)
            ->select('facility_id')->get()->toArray();

        $facilityIds = array_column($facilityIdList, 'facility_id');
        return $facilityIds;
    }

    /**
     * ログインユーザーに紐づいている事業所番号を取得
     * @return array 事業所番号のリスト
     */
    protected function affiliatedFacilitiyNumbers()
    {
        $corporationId = CorporationAccount::where('account_id', \Auth::id())
            ->select('corporation_id')
            ->first();

        $institutionIdList = Institution::where('corporation_id', $corporationId->corporation_id)
            ->select('id')
            ->get()->map(function ($item) {
                return $item->id;
            });
        $facilityNumberList = Facility::whereIn('institution_id', $institutionIdList)
            ->select('facility_number')->get()->toArray();
        $facilityNumbers = array_column($facilityNumberList, 'facility_number');
        return $facilityNumbers;
    }
}
