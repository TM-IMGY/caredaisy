<?php

namespace App\Http\Controllers\GroupHome\CarePlanInfo;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\CarePlanInfo\ServicePlan2PdfRequest;
use App\Service\GroupHome\ServicePlanService;
use App\Service\GroupHome\SecondServicePlanService;
use App\Service\GroupHome\FacilityUserService;
use App\Http\Requests\GroupHome\CarePlanInfo\ServicePlan2Request;
use App\Http\Requests\GroupHome\CarePlanInfo\ServicePlan2RegisterRequest;
use App\Models\ServicePlanNeed;
use Illuminate\Http\Exceptions\HttpResponseException;
use PDF;
use Illuminate\Http\Request;

class SecondServicePlanController extends Controller
{
    /**
     * 介護計画書2にレコードを新規挿入する
     * @param Request $request
     * @return Response
     */
    public function insertRecord(ServicePlan2Request $request)
    {
        // 必要なパラメーターを受け取る
        $param = [
            'service_plan_id' => $request->service_plan_id
        ];

        $sspService = new SecondServicePlanService();
        $sspService->insertRecord($param['service_plan_id']);
        $secondServicePlan = $sspService->get($param['service_plan_id']);
        if ($secondServicePlan === null) {
            return ['is_success' => false];
        } else {
            return $this->getResponseData($secondServicePlan);
        }
    }

    /**
     * 介護計画書2のデータを取得する
     * @param Request $request
     * @return Response
     */
    public function get(ServicePlan2Request $request)
    {
        // 必要なパラメーターを受け取る
        $param = [
            'service_plan_id' => $request->service_plan_id
        ];

        $sspService = new SecondServicePlanService();
        $secondServicePlan = $sspService->get($param['service_plan_id']);
        if ($secondServicePlan === null) {
            $res = response()->json([
                'errors' => 'データがありません',
            ], 200);
            throw new HttpResponseException($res);
        } else {
            return $this->getResponseData($secondServicePlan);
        }
    }

    /**
     * レスポンス用のデータを取得する
     * @param array $secondServicePlan
     * @return Response
     */
    public function getResponseData($secondServicePlan)
    {
        // secondServicePlanを切り分ける
        $need = $secondServicePlan['service_plan_need'];
        $long = $secondServicePlan['service_long_plan'];
        $short = $secondServicePlan['service_short_plan'];
        $support = $secondServicePlan['service_plan_support'];

        $data = ['second_service_plan_id' => $secondServicePlan['second_service_plan_id'],'need_list' => []];

        $data['need_list'] = $need;

        // ニーズを追加
        for ($needIndex = 0,$needCnt = count($need); $needIndex < $needCnt; $needIndex++) {
            // index初期化用
            $lIndex = 0;
            $SIndex = 0;
            $SpIndex = 0;
            $oldNIndex = 0;
            $oldLIndex = 0;
            $oldSIndex = 0;

            $data['need_list'][$needIndex] = [
                'second_service_plan_id' => $data['second_service_plan_id'], // 外部キー
                'service_plan_need_id' => $need[$needIndex]['id'],
                'needs' => $need[$needIndex]['needs'],
                'task_start' => $need[$needIndex]['task_start'],
                'task_end' => $need[$needIndex]['task_end'],
                'sort' => $need[$needIndex]['sort'],
                'long_plan_list' => []
            ];
            $sort_need[$needIndex] = $data['need_list'][$needIndex]['sort'];

            if ($needIndex == 0) {
                $oldNIndex = 0;
            }
            $nowNIndex = $needIndex;
            // 長期を追加
            for ($longIndex = 0,$longCnt = count($long); $longIndex < $longCnt; $longIndex++) {
                if ($longIndex == 0) {
                    $oldLIndex = 0;
                } //新たなニーズのループに入った際の初期化
                if ($nowNIndex > $oldNIndex) {
                    $lIndex = 0;
                    $oldNIndex = $nowNIndex;
                }
                if ($need[$needIndex]['id'] != $long[$longIndex]['service_plan_need_id']) {
                    continue;
                }
                $data['need_list'][$needIndex]['long_plan_list'][$lIndex] = [
                    'service_plan_need_id' => $need[$needIndex]['id'], // 外部キー
                    'service_long_plan_id' => $long[$longIndex]['id'],
                    'goal' => $long[$longIndex]['goal'],
                    'task_start' => $long[$longIndex]['task_start'],
                    'task_end' => $long[$longIndex]['task_end'],
                    'sort' => $long[$longIndex]['sort'],
                    'short_plan_list' => []
                ];
                $sort_long[$longIndex] = $data['need_list'][$needIndex]['long_plan_list'][$lIndex]['sort'];

                $nowLIndex = $longIndex;
                // 短期を追加
                for ($shortIndex = 0,$shortCnt = count($short); $shortIndex < $shortCnt; $shortIndex++) {
                    if ($shortIndex == 0) {
                        $oldSIndex = 0;
                    }//新たなループに入った際の初期化
                    if ($nowLIndex > $oldLIndex) {
                        $SIndex = 0;
                        $oldLIndex = $nowLIndex;
                    }
                    if ($long[$longIndex]['id'] != $short[$shortIndex]['service_long_plan_id']) {
                        continue;
                    }
                    $data['need_list'][$needIndex]['long_plan_list'][$lIndex]['short_plan_list'][$SIndex] = [
                        'service_long_plan_id' => $long[$longIndex]['id'], // 外部キー
                        'service_short_plan_id' => $short[$shortIndex]['id'],
                        'goal' => $short[$shortIndex]['goal'],
                        'task_start' => $short[$shortIndex]['task_start'],
                        'task_end' => $short[$shortIndex]['task_end'],
                        'sort' => $short[$shortIndex]['sort'],
                        'support_list' => []
                    ];
                    $sort_short[$shortIndex] = $data['need_list'][$needIndex]['long_plan_list'][$lIndex]['short_plan_list'][$SIndex]['sort'];
                    $nowSIndex = $shortIndex;
                    // 援助内容
                    for ($supportIndex = 0,$supportCnt = count($support); $supportIndex < $supportCnt; $supportIndex++) {
                        if ($nowSIndex > $oldSIndex) {
                            $SpIndex = 0;
                            $oldSIndex = $nowSIndex;
                        }
                        if ($short[$shortIndex]['id'] != $support[$supportIndex]['service_short_plan_id']) {
                            continue;
                        }
                        $data['need_list'][$needIndex]['long_plan_list'][$lIndex]['short_plan_list'][$SIndex]['support_list'][$SpIndex] = [
                            'service_short_plan_id' => $short[$shortIndex]['id'], // 外部キー
                            'service_plan_support_id' => $support[$supportIndex]['id'],
                            'task_start' => $support[$supportIndex]['task_start'],
                            'task_end' => $support[$supportIndex]['task_end'],
                            'service' => $support[$supportIndex]['service'],
                            'staff' => $support[$supportIndex]['staff'],
                            'frequency' => $support[$supportIndex]['frequency'],
                            'sort' => $support[$supportIndex]['sort']
                        ];
                        $sort_service[$supportIndex] = $data['need_list'][$needIndex]['long_plan_list'][$lIndex]['short_plan_list'][$SIndex]['support_list'][$SpIndex]['sort'];
                        $SpIndex++;
                    }
                    if (is_array($sort_service)) {
                        array_multisort($sort_service, SORT_ASC, $data['need_list'][$needIndex]['long_plan_list'][$lIndex]['short_plan_list'][$SIndex]['support_list']);
                    }
                    $sort_service = [];
                    $SIndex++;
                }
                if (is_array($sort_short)) {
                    array_multisort($sort_short, SORT_ASC, $data['need_list'][$needIndex]['long_plan_list'][$lIndex]['short_plan_list']);
                }
                $sort_short = [];
                $lIndex++;
            }
            if (is_array($sort_long)) {
                array_multisort($sort_long, SORT_ASC, $data['need_list'][$needIndex]['long_plan_list']);
            }
            $sort_long = [];
        }
        if (is_array($sort_long)) {
            array_multisort($sort_need, SORT_ASC, $data['need_list']);
        }
        return $data;
    }

    /**
     * 介護計画書2の関連レコードを更新する
     * @param Request $request
     * @return Response
     */
    public function update(ServicePlan2RegisterRequest $request)
    {
        // 必要なパラメーターを受け取る
        $sspService = new SecondServicePlanService();
        $param = [
            'service_plan_id' => $request['service_plan_id'],
            'need_list' => $request['service_plan2']['need_list'],
            'second_service_plan_id' => $request['service_plan2']['second_service_plan_id'],
        ];
        // 新規挿入/更新レコードのデータを作成する
        $insertData = ['need' => [], 'long' => [] , 'short' => [] ,'support' => []];
        // ニーズ
        $needList = $param['need_list'];
        try {
            \DB::beginTransaction();

            // ここでNeed以下一旦全削除
            ServicePlanNeed::where("second_service_plan_id", $param["second_service_plan_id"])->delete();

            for ($needIndex = 0,$needCnt = count($needList); $needIndex < $needCnt; $needIndex++) {
                $insertData = [];
                $insertData['need'] = [
                    'second_service_plan_id' => $param['second_service_plan_id'],
                    'needs' => $needList[$needIndex]['needs'],
                    'task_start' => $needList[$needIndex]['task_start'],
                    'task_end' => $needList[$needIndex]['task_end'],
                    'sort' => $needList[$needIndex]['sort'],
                ];
                $need_id = $sspService->insertNNeed($insertData['need']);
                // 長期
                $longList = $needList[$needIndex]['long_plan_list'];
                for ($longIndex = 0,$longCnt = count($longList); $longIndex < $longCnt; $longIndex++) {
                    $service_plan_need_id = $need_id;
                    $insertData['long'] = [
                        'service_plan_need_id' => $service_plan_need_id, // 外部キー
                        'goal' => $longList[$longIndex]['goal'],
                        'task_start' => $longList[$longIndex]['task_start'],
                        'task_end' => $longList[$longIndex]['task_end'],
                        'sort' => $longList[$longIndex]['sort'],
                    ];
                    $long_id = $sspService->insertNLong($insertData['long']);
                    // 短期
                    $shortList = $longList[$longIndex]['short_plan_list'];
                    for ($shortIndex = 0,$shortCnt = count($shortList); $shortIndex < $shortCnt; $shortIndex++) {
                        $service_long_plan_id = $long_id;
                        $insertData['short'] = [
                            'service_long_plan_id' => $service_long_plan_id, // 外部キー
                            'goal' => $shortList[$shortIndex]['goal'],
                            'task_start' => $shortList[$shortIndex]['task_start'],
                            'task_end' => $shortList[$shortIndex]['task_end'],
                            'sort' => $shortList[$shortIndex]['sort'],
                        ];
                        $short_id = $sspService->insertNShort($insertData['short']);
                        // 援助内容
                        $supportList = $shortList[$shortIndex]['support_list'];
                        for ($supportIndex = 0,$supportCnt = count($supportList); $supportIndex < $supportCnt; $supportIndex++) {
                            $service_short_plan_id = $short_id;
                            $insertData['support'] = [
                                'service_short_plan_id' => $service_short_plan_id, // 外部キー
                                'task_start' => $supportList[$supportIndex]['task_start'],
                                'task_end' => $supportList[$supportIndex]['task_end'],
                                'service' => $supportList[$supportIndex]['service'],
                                'staff' => $supportList[$supportIndex]['staff'],
                                'frequency' => $supportList[$supportIndex]['frequency'],
                                'sort' => $supportList[$supportIndex]['sort']
                            ];
                            $sspService->insertNSupport($insertData['support']);
                        }
                    }
                }
            }
            \DB::commit();

            return ['is_success' => true];
        } catch (\Exception $e) {
            report($e);
            \DB::rollBack();
            return ['is_success' => false];
        }
    }
    /**
     * Validationは後回し
     * */
    public function outputServicePlan2Pdf(ServicePlan2PdfRequest $request)
    {
        $servicePlanId = $request->plan_id;

        $servicePlanService = new ServicePlanService();
        $servicePlan = $servicePlanService->get(["id" => $servicePlanId, "clm" => ["facility_user_id", "plan_end_period", "plan_start_period"]]);

        $facilityUserId = $servicePlan[0]["facility_user_id"];
        $editor = $servicePlan[0]["plan_end_period"];
        $createdAt = $servicePlan[0]["plan_start_period"];

        // 利用者情報を作成
        $fus = new FacilityUserService();
        $fust = $fus->getFacilityUserServiceType($facilityUserId, $servicePlanId);
        $facilityUser = $fust["FacilityUser"];
        $facilityUserServiceTypeCode = $fust["serviceType"];

        $sspService = new SecondServicePlanService();
        $secondServicePlan = $sspService->get($servicePlanId);

        // データあると信じて処理する
        $secondServiceDatas = [];

        $tmpSupports = [];
        foreach ($secondServicePlan["service_plan_support"] as $support) {
            $tmpSupports[$support["service_short_plan_id"]][] = $support;
        }

        $tmpShortPlans = [];
        foreach ($secondServicePlan["service_short_plan"] as $shortPlan) {
            $shortPlan["servicePlanSupports"] = $tmpSupports[$shortPlan["id"]];
            $shortPlan["childRowCount"] = count($tmpSupports[$shortPlan["id"]]);
            $tmpShortPlans[$shortPlan["service_long_plan_id"]][] = $shortPlan;
        }

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

        foreach ($secondServicePlan["service_plan_need"] as $need) {
            $need["serviceLongPlans"] = $tmpLongPlans[$need["id"]];
            $rowCount = 0;
            foreach ($tmpLongPlans[$need["id"]] as $long) {
                $rowCount += $long["childRowCount"];
            }
            $need["childRowCount"] = $rowCount;
            $secondServiceDatas["servicePlanNeeds"][] = $need;
        }

        $title = $servicePlanService->selectPdfTitle($facilityUserServiceTypeCode, 2);
        $username = $facilityUser['last_name'].$facilityUser['first_name'];

        //var_dump($secondServiceDatas); exit;
            //    return view('components/group_home.care_plan_info/service_plan2_pdf',compact("secondServiceDatas", "editor", "createdAt", "facilityUser", "facilityUserServiceTypeCode", "title"));
        return PDF::loadView('components/group_home.care_plan_info/service_plan2_pdf', compact("secondServiceDatas", "editor", "createdAt", "facilityUser", "facilityUserServiceTypeCode", "title"))
            ->setOption('encoding', 'utf-8')
            ->setOption('user-style-sheet', public_path(). '/css/group_home/care_plan_info/service_plan2_pdf.css')
            ->setPaper('A4', "landscape")
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 5)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('footer-font-size', 12)
            ->setOption('footer-center', "[page]/[topage]ページ")
            ->inline("介護計画書２_{$username}.pdf");
    }
}
