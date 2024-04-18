<?php

namespace App\Http\Controllers\GroupHome\CarePlanInfo;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetFaclityUserInformationRequest;
use App\Http\Requests\GroupHome\CarePlanInfo\ServicePlan1GetHistoryRequest;
use App\Http\Requests\GroupHome\CarePlanInfo\ServicePlan1PdfRequest;
use App\Http\Requests\GroupHome\CarePlanInfo\ServicePlan1RegisterRequest;
use App\Http\Requests\GroupHome\CarePlanInfo\ServicePlanGetCarePlanPeriodRequest;
use App\Http\Requests\GroupHome\CarePlanInfo\ServicePlanGetUserStartDateRequest;
use App\Models\SecondServicePlan;
use App\Models\ServicePlan;
use App\Models\WeeklyPlan;
use App\Service\GroupHome\ServicePlan1Service;
use App\Service\GroupHome\ServicePlanService;
use Carbon\Carbon;
use PDF;

class ServicePlanController extends Controller
{
    /**
     * i_facilities のfirst_plan_input を取得
     */
    public function getFacilityFristPlanInput(GetFaclityUserInformationRequest $request)
    {
        $facilityUserId  = $request->facility_user_id;

        $ServicePlan1Service = new ServicePlan1Service();
        $flg = $ServicePlan1Service->getFacilityFristPlanInput($facilityUserId);

        return response()->json($flg);
    }

    /**
     * サービス情報から要介護度のリストを取得する
     */
    public function getEffectiveService(ServicePlan1Service $ServicePlan1Service, GetFaclityUserInformationRequest $request)
    {
        // リクエストパラメーターを取得する
        $params = [
            'facility_user_id' => $request->facility_user_id
        ];
        $carelevels = $ServicePlan1Service->getFacilityUserServiceInformation($params);
        return $carelevels;
    }

    /**
     * 利用者の現在の介護情報を取得する
     */
    public function getUserInformation(GetFaclityUserInformationRequest $request)
    {
        $facilityUserId = $request->facility_user_id;
        $year = $request->year;
        $month = $request->month;

        $ServicePlan1Service = new ServicePlan1Service();
        $userInformation = $ServicePlan1Service->getUserInformation($facilityUserId, $year, $month);

        return response()->json($userInformation);
    }

    /**
     * 選択した利用者の履歴リスト及び履歴情報を返す
     */
    public function getPlan1History(ServicePlan1GetHistoryRequest $request, ServicePlan1Service $servicePlan1Service)
    {
        if (isset($request->service_plan_id)) {
            // 選択した履歴情報を取得
            $historyInformation = $servicePlan1Service->getPlan1History($request->service_plan_id, $request->facility_user_id);
        } else {
            // 履歴リストを取得
            $historyInformation = $servicePlan1Service->getPlan1HistoryList($request->facility_user_id);
        }

        return response()->json($historyInformation);
    }

    /**
   * 新規登録・更新
   * @param ServicePlan1RegisterRequest
   */
    public function save(ServicePlan1RegisterRequest $request)
    {
        $latestServicePlanId = $request->latest_service_plan_id;

        $servicePlanParams = [
            'facility_user_id' => $request->facility_user_id,
            'plan_start_period' => $request->plan_start_period,
            'plan_end_period' => $request->plan_end_period,
            'status' => $request->status,
            'fixed_date' => $request->fixed_date,
            'delivery_date' => $request->delivery_date,
            'certification_status' => $request->certification_status,
            'recognition_date' => $request->recognition_date,
            'care_period_start' => $request->care_period_start,
            'care_period_end' => $request->care_period_end,
            'care_level_name' => $request->care_level_name,
            'consent' => $request->consent,
            'place' => $request->place,
            'remarks' => $request->remarks,
            'independence_level' => $request->independence_level,
            'dementia_level' => $request->dementia_level,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'care_level_dispflg' => $request->care_level_dispflg,
            'first_plan_start_period' => $request->first_plan_start_period,
        ];

        $firstServicePlanParams = [
            'plan_division' => $request->plan_division,
            'title1' => $request->title1,
            'content1' => $request->content1,
            'title2' => $request->title2,
            'content2' => $request->content2,
            'title3' => $request->title3,
            'content3' => $request->content3,
            'living_alone' => $request->living_alone,
            'handicapped' => $request->handicapped,
            'other' => $request->other,
            'other_reason' => $request->other_reason
        ];

        if (isset($request->service_plan_id)) {
            $servicePlanParams['service_plan_id'] = $request->service_plan_id;
            $firstServicePlanParams['service_plan_id'] = $request->service_plan_id;
        }

        if (isset($request->first_service_plan_id)) {
            $firstServicePlanParams['id'] = $request->first_service_plan_id;
        }

        $ServicePlan1Service = new ServicePlan1Service();
        $insertId = $ServicePlan1Service->save($servicePlanParams, $firstServicePlanParams, $latestServicePlanId);

        if ($request->isReplicate) {
            $latestPlan = $ServicePlan1Service->getLatestIssuedData($request->facility_user_id);
            if (!empty($latestPlan['id'])) {
                WeeklyPlan::replicateWithServicePlan($insertId['service_plan_id'], $latestPlan['id']);
            }
        }

        return $insertId;
    }

    public function pdf(ServicePlan1PdfRequest $request)
    {
        // 連票フラグ
        $flg = 0;
        $facilityUserId = $request->facility_user_id;
        $servicePlanId = $request->service_plan_id;
        $ServicePlan1Service = new ServicePlan1Service();
        $facilityUserInformations[0] = $ServicePlan1Service->pdf($facilityUserId, $servicePlanId);
        $username = $facilityUserInformations[0]['user_info']['last_name'].
                    $facilityUserInformations[0]['user_info']['first_name'];

        // return view('components.group_home.care_plan_info.service_plan1_pdf',compact('facilityUserInformations','flg'));

        return PDF::loadView('components/group_home/care_plan_info/service_plan1_pdf', compact('facilityUserInformations', 'flg'))
            ->setOption('encoding', 'utf-8')
            ->setOption('user-style-sheet', public_path(). '/css/group_home/care_plan_info/service_plan1_pdf.css')
            ->setPaper('A4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 8)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('footer-font-size', 12)
            ->setOption('footer-center', "[page]/[topage]ページ")
            ->inline("介護計画書_{$username}.pdf");
    }

    public function SecondServicePlanExistence(ServicePlan1PdfRequest $request)
    {
        $servicePlanId = $request->service_plan_id;

        $secondServicePlan = SecondServicePlan::where('service_plan_id', $servicePlanId)
            ->get()
            ->count();

        return response()->json($secondServicePlan);
    }

    public function consecutivePdf(ServicePlan1PdfRequest $request)
    {
        // リクエストデータを取得する
        $params = [
        'service_plan_id' => $request->service_plan_id,
        ];

        // 介護計画書のPDFデータを取得する
        $servicePlan1Service = new ServicePlan1Service();
        $pdf = $servicePlan1Service->consecutivePdf($params);

        // PDFプレビューの保存しに名前を入れる
        $facilityUserId = $request->facility_user_id;
        $servicePlanId = $request->service_plan_id;
        $facilityUserInformations[0] = $servicePlan1Service->pdf($facilityUserId, $servicePlanId);
        $username = $facilityUserInformations[0]['user_info']['last_name'].
                    $facilityUserInformations[0]['user_info']['first_name'];

        return $pdf->inline("介護計画書_{$username}.pdf");
    }

    /**
     * 介護計画書のケアプラン期間を返す
     * @param ServicePlanGetCarePlanPeriodRequest $request key: facility_user_id
     */
    public function getCarePlanPeriod(ServicePlanGetCarePlanPeriodRequest $request)
    {
        // リクエストパラメーターを取得する
        $params = [
            'facility_user_id' => $request->facility_user_id
        ];

        // ユースケースにリクエストパラメーターを渡し、レスポンスを取得する
        $servicePlanService = new ServicePlanService();
        $data = $servicePlanService->getCarePlanPeriod($params);
        $servicePlanService->compare3Dates($params);
        return $data;
    }

    /**
     * 最新の交付済みプラン情報を取得する
     */
    public function getIssuedData(GetFaclityUserInformationRequest $request, ServicePlan1Service $servicePlan1Service)
    {
        $deliveyData = $servicePlan1Service->getLatestIssuedData($request->facility_user_id);
        return response()->json($deliveyData);
    }

    /**
     * ケアプラン期間内の有効なサービス数を抽出する
     */
    public function checkEffectiveService(ServicePlan1PdfRequest $request)
    {
        $count = ServicePlan::checkEffectiveService($request->service_plan_id);
        return response()->json($count);
    }

    /**
     * 入居日を取得
     */
    public function getUserStartDate(ServicePlanGetUserStartDateRequest $request)
    {
        // リクエストパラメーターを取得する
        $params = [
            'facility_user_id' => $request->facility_user_id
        ];

        $servicePlanService = new ServicePlanService();
        $startDate = $servicePlanService->getFacilityUserStartDate($params)->format('Y-m-d');
        return response()->json($startDate);
    }
}
