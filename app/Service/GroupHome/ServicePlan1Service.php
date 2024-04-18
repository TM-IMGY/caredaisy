<?php

namespace App\Service\GroupHome;

use App\Models\Facility;
use App\Models\FacilityUser;
use App\Models\FirstServicePlan;
use App\Models\SecondServicePlan;
use App\Models\Service;
use App\Models\ServicePlan;
use App\Models\ServiceType;
use App\Models\CareLevel;
use App\Models\UserCareInformation;
use App\Models\UserFacilityInformation;
use App\Models\UserFacilityServiceInformation;
use App\Service\GroupHome\SecondServicePlanService;
use App\Service\GroupHome\FacilityUserService;
use App\Service\GroupHome\ServicePlanService;
use \App\Utility\S3;
use Carbon\Carbon;

class ServicePlan1Service
{
    /**
     * 介護計画書の情報を返す
     * @param array $param key: id
     * @return array
     */
    public function get($param) : array
    {
        return ServicePlan::where('id', $param['id'])->select($param['clm'])->get()->toArray();
    }

    /**
     * 入居日またはケアプラン開始日から利用者のサービス情報を取得する
     */
    public function getFacilityUserServiceInformation($params)
    {
        // ユースケースにリクエストパラメーターを渡し、レスポンスを取得する
        $servicePlanService = new ServicePlanService();
        $data = $servicePlanService->getCarePlanPeriod($params);
        $startDate = $data['start_date'];
        if ($data['is_init_plan']) {
            $startDate = $servicePlanService->getFacilityUserStartDate($params)->format('Y-m-d');
        }

        $careLevels = self::getCareLevelList($params['facility_user_id'], $startDate);
        return $careLevels;
    }

    public function getCareLevelList($facilityUserId, $startDate)
    {
        $serviceId = UserFacilityServiceInformation::facilityUserEffectiveService($facilityUserId, $startDate);

        // 施設利用者が提供されているサービス種別IDを取得する。
        $serviceTypeCodeId = Service::
            where('id', $serviceId)
            ->pluck('service_type_code_id')
            ->toArray();

        // 施設利用者が提供されているサービス種別コード情報を取得する。
        $serviceType = ServiceType::
            where('service_type_code_id', $serviceTypeCodeId)
            ->select('service_type_code_id')
            ->first();

        if (is_null($serviceType)) {
            return [];
        }

        // 種類に紐づく要介護度を取得する
        $careLevels = ServiceType::relationCareLevels[$serviceType['service_type_code_id']];
        return $careLevels;
    }

    public function getFacilityFristPlanInput($facilityUserId)
    {
        $serviceId = UserFacilityServiceInformation::where('facility_user_id', $facilityUserId)
            ->select('service_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return $item->service_id;
            });

        if ($serviceId->isEmpty()) {
            return $flg = ['first_plan_input' => 0];
        }

        $flg = Service::where('id', $serviceId[0])
            ->select('first_plan_input')
            ->get()
            ->toArray();

        return $flg;
    }

    /**
     * 介護計画書連票のPDFデータを返す
     * @param array $params key: service_plan_id
     */
    public function consecutivePdf($params)
    {
        $servicePlanService = new ServicePlanService();
        $servicePlanId = $params['service_plan_id'];

        // 介護計画書のリポジトリからデータを取得する
        $servicePlan = $this->get([
            "id" => $servicePlanId,
            "clm" => ["facility_user_id", "plan_end_period", "plan_start_period"]
        ]);
        $facilityUserId = $servicePlan[0]["facility_user_id"];
        $editor = $servicePlan[0]["plan_end_period"];
        $createdAt = $servicePlan[0]["plan_start_period"];

        // 施設利用者のリポジトリからデータを取得する
        $facilityUserService = new FacilityUserService();
        $facilityUser = $facilityUserService->getFacilityUser(["facility_user_id" => $facilityUserId]);

        // 介護計画書2のリポジトリからデータを取得する
        $sspService = new SecondServicePlanService();
        $secondServicePlan = $sspService->get($servicePlanId);

        // レスポンスに渡すデータの介護計画書2部分のプレースホルダー
        $secondServiceDatas = [];

        // レスポンスに渡すサポートデータを作成する
        $tmpSupports = [];
        foreach ($secondServicePlan["service_plan_support"] as $support) {
            $tmpSupports[$support["service_short_plan_id"]][] = $support;
        }

        // レスポンスに渡す短期データを作成する
        $tmpShortPlans = [];
        foreach ($secondServicePlan["service_short_plan"] as $shortPlan) {
            $shortPlan["servicePlanSupports"] = $tmpSupports[$shortPlan["id"]];
            $shortPlan["childRowCount"] = count($tmpSupports[$shortPlan["id"]]);
            $tmpShortPlans[$shortPlan["service_long_plan_id"]][] = $shortPlan;
        }

        // レスポンスに渡す長期データを作成する
        $tmpLongPlans = [];
        foreach ($secondServicePlan["service_long_plan"] as $longPlan) {
            $longPlan["serviceShortPlans"] = $tmpShortPlans[$longPlan["id"]];
            $rowCount = 0;
            foreach ($tmpShortPlans[$longPlan["id"]] as $short) {
                $rowCount += $short["childRowCount"];
            }
            $longPlan["childRowCount"] = $rowCount;
            $tmpLongPlans[$longPlan["service_plan_need_id"]][] = $longPlan;
        }

        // レスポンスに渡すニーズデータを作成する
        foreach ($secondServicePlan["service_plan_need"] as $need) {
            $need["serviceLongPlans"] = $tmpLongPlans[$need["id"]];
            $rowCount = 0;
            foreach ($tmpLongPlans[$need["id"]] as $long) {
                $rowCount += $long["childRowCount"];
            }
            $need["childRowCount"] = $rowCount;
            $secondServiceDatas["servicePlanNeeds"][] = $need;
        }

        // 連票フラグ
        $flg = 1;

        $facilityUserInformations[0] = $this->pdf($facilityUserId, $servicePlanId);

        $title = $servicePlanService->selectPdfTitle($facilityUserInformations[0]['facilityUserServiceTypeCode'],2);

        // PDFを生成して返す
        return
            \PDF::loadView(
                'components/group_home/care_plan_info/service_plan1_pdf',
                compact('facilityUserInformations', 'flg', 'secondServiceDatas', "editor", "createdAt", "facilityUser", "title")
            )
            ->setOption('encoding', 'utf-8')
            ->setOption('user-style-sheet', public_path() . '/css/group_home/care_plan_info/service_plan1_pdf.css')
            ->setOption('user-style-sheet', public_path() . '/css/group_home/care_plan_info/service_plan2_pdf.css')
            ->setPaper('A4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 5)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('footer-font-size', 12)
            ->setOption('footer-center', "[page]/[topage]ページ");
    }

    public function getUserInformation($facilityUserId, $year, $month)
    {
        $userCareInformation = UserCareInformation::where('facility_user_id', $facilityUserId)
            ->select(
                'certification_status',
                'care_period_start',
                'care_level_id',
                'recognition_date',
                'care_period_start',
                'care_period_end',
            )
            ->orderby('care_period_start', 'DESC')
            ->date($year, $month)
            ->first();

        // 認定済の認定情報が見つからなかったら申請中の認定情報を取得する
        if ($userCareInformation == []) {
            $userCareInformation = UserCareInformation::
                where('facility_user_id', $facilityUserId)
                ->where('certification_status', UserCareInformation::CERTIFICATION_STATUS_APPLYING)
                ->select(
                    'certification_status',
                    'care_level_id',
                )
                ->first();
        }

        // 認定済・申請中どちらも取得できなかったら空配列を返す
        if ($userCareInformation == []) {
            return [];
        }
        $userCareInformationArr = $userCareInformation->attributesToArray();

        $careLevel = $userCareInformationArr['care_level_id'];
        $careLevelName = CareLevel::where('care_level_id', $careLevel)
            ->select('care_level_name')
            ->first()
            ->attributesToArray();

        $responce = array_merge($userCareInformationArr, $careLevelName);

        return $responce;
    }

    public function getPlan1HistoryList($facilityUserId)
    {
        $servicePlan1History = ServicePlan::where('facility_user_id', $facilityUserId)
            ->select(
                'id',
                'plan_start_period',
                'plan_end_period',
                'status',
                'start_date',
                'end_date',
                'fixed_date',
                'care_level_name',
                'delivery_date',
                'created_at',
            )
            ->orderBy('plan_start_period', 'desc')
            ->get()
            ->toArray();

        return $servicePlan1History;
    }

    public function getPlan1History($servicePlanId, $facilityUserId)
    {
        $servicePlanInfo = ServicePlan::where('id', $servicePlanId)
            ->select(
                'plan_start_period',
                'plan_end_period',
                'status',
                'fixed_date',
                'delivery_date',
                'care_level_name',
                'recognition_date',
                'certification_status',
                'care_period_start',
                'care_period_end',
                'consent',
                'place',
                'remarks',
                'independence_level',
                'dementia_level',
                'updated_at',
                'start_date',
                'end_date',
                'care_level_dispflg',
                'first_plan_start_period'
            )
            ->get()
            ->toArray();

        $firstServicePlanInfo = FirstServicePlan::where('service_plan_id', $servicePlanId)
            ->select(
                'id',
                'service_plan_id',
                'plan_division',
                'title1',
                'content1',
                'title2',
                'content2',
                'title3',
                'content3',
                'title4',
                'content4',
                'living_alone',
                'handicapped',
                'other',
                'other_reason'
            )
            ->get()
            ->toArray();

        $information = array_merge($servicePlanInfo[0], $firstServicePlanInfo[0]);

        $careLevels['care_levels'] = self::getCareLevelList($facilityUserId, $information['start_date']);
        $requestData = array_merge($information, $careLevels);

        return $requestData;
    }

    /**
     * 介護計画書1の保存のユースケース
     * @param array $servicePlanParams
     * @param array $firstServicePlanParams
     * @param int $latestServicePlanId
     * @return array
     */
    public function save($servicePlanParams, $firstServicePlanParams, $latestServicePlanId) : array
    {
        // 保存したレコード情報を返すためのプレースホルダー
        $res = [];

        // 介護計画書のIDと介護計画書1のIDがない場合 => 介護計画書の新規登録の場合
        if (empty($servicePlanParams['service_plan_id']) && empty($firstServicePlanParams['id'])) {
            $res = $this->insert($servicePlanParams, $firstServicePlanParams, $latestServicePlanId);
        } else {
            $res = $this->update($servicePlanParams, $firstServicePlanParams);
        }

        // 介護計画書が交付済みとなった時、PDF化してストレージに保存する
        if (isset($res['service_plan_id']) && $servicePlanParams['status'] == ServicePlan::STATUS_ISSUED) {
            // 介護計画書のPDFデータを取得する
            $pdf = $this->consecutivePdf([
                'service_plan_id' => $res['service_plan_id']
            ]);
            $pdfData = $pdf->output();

            // 介護計画書連票をストレージに保存する
            if (!empty($servicePlanParams['service_plan_id'])) {
                S3::saveServicePlanPdf($servicePlanParams['service_plan_id'], $pdfData);
            }
        }

        return $res;
    }

    /**
     * 介護計画書の保存処理
     * @param array $servicePlanParams
     * @param array $firstServicePlanParams
     * @param int $latestServicePlanId
     * @return array
     */
    public function insert($servicePlanParams, $firstServicePlanParams, $latestServicePlanId) : array
    {
        $res = \DB::transaction(function () use ($servicePlanParams, $firstServicePlanParams, $latestServicePlanId) {
            // 介護計画書を保存する
            $serviceSaveResult = ServicePlan::create([
                'facility_user_id' => $servicePlanParams['facility_user_id'],
                'plan_start_period' => $servicePlanParams['plan_start_period'],
                'plan_end_period' => $servicePlanParams['plan_end_period'],
                'status' => $servicePlanParams['status'],
                'fixed_date' => $servicePlanParams['fixed_date'],
                'delivery_date' => $servicePlanParams['delivery_date'],
                'certification_status' => $servicePlanParams['certification_status'],
                'recognition_date' => $servicePlanParams['recognition_date'],
                'care_period_start' => $servicePlanParams['care_period_start'],
                'care_period_end' => $servicePlanParams['care_period_end'],
                'care_level_name' => $servicePlanParams['care_level_name'],
                'consent' => $servicePlanParams['consent'],
                'place' => $servicePlanParams['place'],
                'remarks' => $servicePlanParams['remarks'],
                'independence_level' => $servicePlanParams['independence_level'],
                'dementia_level' => $servicePlanParams['dementia_level'],
                'start_date' => $servicePlanParams['start_date'],
                'end_date' => $servicePlanParams['end_date'],
                'care_level_dispflg' => $servicePlanParams['care_level_dispflg'],
                'first_plan_start_period' => $servicePlanParams['first_plan_start_period']
            ]);

            // 介護計画書1を保存する
            if (!empty($serviceSaveResult)) {
                $firstServiceSaveResult = FirstServicePlan::create([
                    'service_plan_id' => $serviceSaveResult->id,
                    'plan_division' => $firstServicePlanParams['plan_division'],
                    'title1' => $firstServicePlanParams['title1'],
                    'content1' => $firstServicePlanParams['content1'],
                    'title2' => $firstServicePlanParams['title2'],
                    'content2' => $firstServicePlanParams['content2'],
                    'title3' => $firstServicePlanParams['title3'],
                    'content3' => $firstServicePlanParams['content3'],
                    'living_alone' => $firstServicePlanParams['living_alone'],
                    'handicapped' => $firstServicePlanParams['handicapped'],
                    'other' => $firstServicePlanParams['other'],
                    'other_reason' => $firstServicePlanParams['other_reason'],
                ]);
            }
            if ($serviceSaveResult && $firstServiceSaveResult) {
                $servicePlanId = ['service_plan_id' => $serviceSaveResult->id];
            }

            // 最新の交付済みプランをコピーして作成したら計画書2もコピー・保存する
            if (!is_null($latestServicePlanId)) {
                $this->saveServicePlan2Data($servicePlanId, $latestServicePlanId);
            } else {
                // 交付済みプランがないなら空の計画書2を作成
                $sspService = new SecondServicePlanService();
                $sspService->insertRecord($serviceSaveResult->id);
            }
            return $servicePlanId;
        });

        return $res;
    }

    /**
     * 介護計画書の更新処理
     * @param array $servicePlanParams
     * @param array $firstServicePlanParams
     * @return array
     */
    public function update($servicePlanParams, $firstServicePlanParams) : array
    {
        $res = \DB::transaction(function () use ($servicePlanParams, $firstServicePlanParams) {
            // 介護計画書を更新する
            $serviceSaveResult = ServicePlan::where('id', '=', $servicePlanParams['service_plan_id'])
                ->update([
                    'plan_start_period' => $servicePlanParams['plan_start_period'],
                    'plan_end_period' => $servicePlanParams['plan_end_period'],
                    'status' => $servicePlanParams['status'],
                    'fixed_date' => $servicePlanParams['fixed_date'],
                    'delivery_date' => $servicePlanParams['delivery_date'],
                    'consent' => $servicePlanParams['consent'],
                    'place' => $servicePlanParams['place'],
                    'remarks' => $servicePlanParams['remarks'],
                    'start_date' => $servicePlanParams['start_date'],
                    'end_date' => $servicePlanParams['end_date'],
                    'certification_status' => $servicePlanParams['certification_status'],
                    'recognition_date' => $servicePlanParams['recognition_date'],
                    'care_period_start' => $servicePlanParams['care_period_start'],
                    'care_period_end' => $servicePlanParams['care_period_end'],
                    'care_level_name' => $servicePlanParams['care_level_name'],
                    'care_level_dispflg' => $servicePlanParams['care_level_dispflg'],
                    'first_plan_start_period' => $servicePlanParams['first_plan_start_period']
                ]);

            // 介護計画書1を更新する
            $firstServiceSaveResult = FirstServicePlan::where('id', '=', $firstServicePlanParams['id'])
                ->where('service_plan_id', '=', $firstServicePlanParams['service_plan_id'])
                ->update([
                    'plan_division' => $firstServicePlanParams['plan_division'],
                    'title1' => $firstServicePlanParams['title1'],
                    'content1' => $firstServicePlanParams['content1'],
                    'title2' => $firstServicePlanParams['title2'],
                    'content2' => $firstServicePlanParams['content2'],
                    'title3' => $firstServicePlanParams['title3'],
                    'content3' => $firstServicePlanParams['content3'],
                    'living_alone' => $firstServicePlanParams['living_alone'],
                    'handicapped' => $firstServicePlanParams['handicapped'],
                    'other' => $firstServicePlanParams['other'],
                    'other_reason' => $firstServicePlanParams['other_reason'],
                ]);

            if ($serviceSaveResult && $firstServiceSaveResult) {
                return ['service_plan_id' => $servicePlanParams['service_plan_id']];
            }
        });

        return $res;
    }

    /**
     * PDFに表示する要介護状態区分のリストを作成する
     * @param integer $serviceType
     */
    public function createLevelDivision($serviceType)
    {
        // 要介護状態区分の分類
        // 複数種別に対応する区分がdivision1のみなら他は不要かも
        $division1 = [32,33,36,55];
        $division2 = [35];
        $division3 = [37];

        if (in_array($serviceType, $division1)) {
            $list = ['要介護1','・','要介護2','・','要介護3','・','要介護4','・','要介護5'];
        } elseif (in_array($serviceType, $division2)) {
            $list = ['非該当','・','要支援1','・','要支援2'];
        } elseif (in_array($serviceType, $division3)) {
            $list = ['要支援2'];
        }
        return $list;
    }

    /**
     * pdfに表示するデータを取得
     *
     */
    public function pdf($facilityUserId, $servicePlanId)
    {
        $servicePlanService = new ServicePlanService();

        // 利用者情報を作成
        $fus = new FacilityUserService();
        $fust = $fus->getFacilityUserServiceType($facilityUserId, $servicePlanId);
        $facilityUser = $fust["FacilityUser"];
        $facilityUserServiceTypeCode = $fust["serviceType"];

        $userInfo = $facilityUser->toArray();

        $birthday = $facilityUser->birthday;
        $birth = collect($this->divideDate('birth', $birthday));
        $res = collect($userInfo)->merge($birth);

        $userInformations[0] = $res;

        // 計画書情報を取得
        $servicePlanInformations = $this->getPlan1History($servicePlanId, $facilityUserId);
        // 認定度のステータスが「申請中」なら認定度関係はnullに
        if ($servicePlanInformations['certification_status'] == UserCareInformation::CERTIFICATION_STATUS_APPLYING) {
            $servicePlanInformations['recognition_date'] = null;
            $servicePlanInformations['care_period_start'] = null;
            $servicePlanInformations['care_period_end'] = null;
        }

        $planStartDate = $this->divideDate('plan_start_period', $servicePlanInformations['plan_start_period']);
        $recognitionDate = $this->divideDate('recognition', $servicePlanInformations['recognition_date']);
        $carePeriodStart = $this->divideDate('care_period_start', $servicePlanInformations['care_period_start']);
        $carePeriodEnd = $this->divideDate('care_period_end', $servicePlanInformations['care_period_end']);
        $deliveryDate = $this->divideDate('delivery_date', $servicePlanInformations['delivery_date']);
        $firstPlanStartDate = $this->divideDate('first_plan_start_period', $servicePlanInformations['first_plan_start_period']);
        $servicePlanInformations[0] = collect(array_merge(
            $servicePlanInformations,
            $planStartDate,
            $recognitionDate,
            $carePeriodStart,
            $carePeriodEnd,
            $deliveryDate,
            $firstPlanStartDate
        ));


        // 利用者の所属事業所情報を取得
        $facilityInfo = $this->getFacilityInfo($facilityUserId);
        $facilityInfo = collect($facilityInfo[0]);
        $facilityInformations[0] = $facilityInfo;

        // 計画書1の情報を作成
        $firstServicePlan = FirstServicePlan::where('service_plan_id', $servicePlanId)
                ->get();

        // 各内容の行数を計算する
        $count['content_count'] = $firstServicePlan->map(function ($item) {
            for ($i = 1; $i < 5; $i++) {
                $res = [];
                $con = str_replace(array("\r\n", "\r", "\n"), "\n", $item['content'.$i]);
                $arr = explode("\n", $con);
                foreach ($arr as $val) {
                    $wrap = $this->mb_wordwrap($val);
                    $res[] = explode("\n", $wrap);
                }
                $result['content'.$i] = intval(count(array_reduce($res, 'array_merge', array())));
            }

            return $result;
        })->toArray()[0];

        $count = collect($count);
        $firstServicePlanInformations[0] = collect($firstServicePlan[0])->merge($count);

        return $res = [
            'facility_info' => $facilityInformations[0],
            'user_info' => $userInformations[0],
            'service_plan_info' => $servicePlanInformations[0],
            'first_service_plan_info' => $firstServicePlanInformations[0],
            'facilityUserServiceTypeCode' => $facilityUserServiceTypeCode,
            'title' => $servicePlanService->selectPdfTitle($facilityUserServiceTypeCode,1),
            'care_level_division' => $this->createLevelDivision($facilityUserServiceTypeCode),
        ];
    }

    /**
     * 長文を改行
     */
    public function mb_wordwrap($str, $width = 65, $break = PHP_EOL, $encode = "UTF-8")
    {
        $c = mb_strlen($str, $encode);
        $arr = [];
        for ($i = 0; $i <= $c; $i += $width) {
            $arr[] = mb_substr($str, $i, $width, $encode);
        }
        return implode($break, $arr);
    }

    /**
     * 年月日を分解
     */
    public function divideDate($column, $targetDate)
    {
        if ($targetDate == null) {
            $date[$column]['year'] = null;
            $date[$column]['month'] = null;
            $date[$column]['day'] = null;
        } else {
            $date[$column]['year'] = date('Y', strtotime($targetDate));
            $date[$column]['month'] = date('m', strtotime($targetDate));
            $date[$column]['day'] = date('d', strtotime($targetDate));
        }

        return $date;
    }

    public function getFacilityInfo($userId)
    {
        $facilityId = UserFacilityInformation::where('facility_user_id', $userId)
            ->select('facility_id')
            ->get()
            ->toArray();

        $facilityInfo = Facility::where('facility_id', $facilityId[0]['facility_id'])
            ->select('location', 'facility_name_kanji')
            ->get();

        return $facilityInfo;
    }

    /**
     * 最新の交付済みプランを取得する
     * @param int $facilityUserId
     * @return array
     */
    public function getLatestIssuedData($facilityUserId)
    {
        $clm = ['id', 'delivery_date', 'plan_end_period', 'end_date', 'first_plan_start_period'];
        $servicePlanData = ServicePlan::selectIssuedPlanInfo($clm, 'facility_user_id', $facilityUserId)
            ->orderBy('delivery_date', 'DESC')
            ->first();

        if ($servicePlanData == null || $servicePlanData['delivery_date'] == null) {
            return null;
        }

        // 前回プランのケアプラン終了日の次の日を次回ケアプラン開始日とする
        $startDate['start_date'] = (new Carbon($servicePlanData['end_date']))->addDay()->toDateString();

        $firstServicePlanData = FirstServicePlan::where('service_plan_id', $servicePlanData['id'])
            ->select('content1', 'content2', 'content3', 'content4')
            ->first()
            ->toArray();

        $today['plan_start_period'] = (new Carbon())->toDateString();
        $servicePlanDataArr = $servicePlanData->toArray();

        return $data = array_merge($servicePlanDataArr, $startDate, $firstServicePlanData, $today);
    }

    /**
     * 最新の交付済みプランの計画書2の内容を新規登録したケアプランに紐づけて登録する
     * @param array $id
     * @param int $latestServicePlanId
     */
    public function saveServicePlan2Data($id, $latestServicePlanId)
    {
        $secondServicePlanId = SecondServicePlan::insertGetId(['service_plan_id' => $id['service_plan_id']]);
        SecondServicePlan::CopyOfRelatedData($secondServicePlanId, $latestServicePlanId);
    }
}
