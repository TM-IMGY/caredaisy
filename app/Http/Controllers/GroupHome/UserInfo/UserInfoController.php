<?php

namespace App\Http\Controllers\GroupHome\UserInfo;

use App\Http\Requests\GetFaclityInformationRequest;
use App\Http\Requests\GetFaclityUserInformationRequest;
use App\Http\Requests\GroupHome\UserInfo\ApprovalRequest;
use App\Http\Requests\GroupHome\UserInfo\BenefitInputRequest;
use App\Http\Requests\GroupHome\UserInfo\IndependenceRequest;
use App\Http\Requests\GroupHome\UserInfo\PublicExpenditureRequest;
use App\Http\Requests\GroupHome\UserInfo\ServiceRequest;
use App\Http\Requests\GroupHome\UserInfo\GetHistoryServiceInfomationRequest;
use App\Http\Requests\GroupHome\UserInfo\PopupUpdataIndependenceRequest;
use App\Models\Facility;
use App\Models\FacilityUser;
use App\Models\CareLevel;
use App\Models\ServiceType;
use App\Models\UserCareInformation;
use App\Models\Service;
use App\Models\UserFacilityServiceInformation;
use App\Models\UserIndependenceInformation;
use App\Service\GroupHome\FacilityService;
use App\Http\Controllers\Controller;

use App\Service\GroupHome\FacilityUserService;
use App\Service\GroupHome\UserBenefitInformationService;
use App\Service\GroupHome\UserPublicExpenseInformationService;
use App\Service\GroupHome\UserApprovalInformationService;

use Illuminate\Http\Request;//Ajax通信の為に追加
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserInfoController extends Controller
{
  public function index(){
    return view('group_home.user_info.user_info');
  }

  public function getFacilityUseService(GetFaclityInformationRequest $request)
  {
    $service = Service::where("facility_id", $request->facility_id)
        ->select('service_type_code_id')
        ->get()
        ->toArray();

    $services = array_column($service, 'service_type_code_id');
    return $services;
  }

  /**
   * サービス
   * @author ttakenaka
   */
    public function service_ajax(GetFaclityUserInformationRequest $request){
    // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
    // 権限チェックのために"$request->postData"を'$request->facility_user_id'に改修
    $facilityUserId = $request->facility_user_id;

    // すべての利用履歴
    $get_facility_informations = UserFacilityServiceInformation::where('facility_user_id',$facilityUserId)
        ->select('user_facility_service_information_id','facility_id','service_id','usage_situation','use_start','use_end')
        ->orderBy('use_start', 'desc')
        ->get()
        ->toArray();

    $arrayList = array();//連想配列準備

    // サービスID(service_id)からサービス種別コード(service_type_code_id)に変換

    $arrayList = [];//連想配列初期化
    for($i = 0; $i < count($get_facility_informations); $i++){
        $data = Service::where('id',$get_facility_informations[$i]['service_id'])
        ->select('service_type_code_id')
        ->get();
        if(!is_null($data)){
            array_push($arrayList,$data->toArray());
        }
    }
    $get_service = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    // サービス種類名　MServiceType：service_type_name　取得
    $arrayList = [];//連想配列初期化
    for($i = 0; $i < count($get_service); $i++){
        $data = ServiceType::where('service_type_code_id',$get_service[$i]['service_type_code_id'])
        ->select('service_type_name')
        ->get();
        if(!is_null($data)){
            array_push($arrayList,$data->toArray());
        }
    }
    $get_service_type_name = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    // 事業所名　Facility：facility_name_kanji　取得
    $arrayList = [];//連想配列初期化
    for($i = 0; $i < count($get_facility_informations); $i++){
        $data = Facility::where('facility_id',$get_facility_informations[$i]['facility_id'])
        ->select('facility_name_kanji')
        ->get();
        if(!is_null($data)){
            array_push($arrayList,$data->toArray());
        }
    }
    $get_facility_data = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    // 事業所番号　Facility：facility_number　取得
    $arrayList = [];//連想配列初期化
    for($i = 0; $i < count($get_facility_informations); $i++){
        $data = Facility::where('facility_id',$get_facility_informations[$i]['facility_id'])
        ->select('facility_number')
        ->get();
        if(!is_null($data)){
            array_push($arrayList,$data->toArray());
        }
    }
    $get_facility_number = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    $maximumItems = count($get_facility_informations);


    // JSON形式に変換
    $json = [
      "facility_infomations" => $get_facility_informations,
      "facility_name_kanji" => $get_facility_data,
      "service_type_name" => $get_service_type_name,
      "facility_number" => $get_facility_number,
      "maximum_items" => $maximumItems
    ];
    return response()->json($json);
}

    public function popup_service_ajax(GetFaclityUserInformationRequest $request){
    // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
    // 権限チェックのために"$request->postData"を'$request->facility_user_id'に改修
    $facilityUserId = $request->facility_user_id;

    // アカウントに紐づいた事業所名　MFacilitiesRelations：account_id　取得
    $service = new FacilityService();
    $get_relation_facility_id = $service->getRelatedData();
    $facilityIdList = $get_relation_facility_id[0]['facility_id'];
    // 事業所IDのリストから事業所の情報を取得
    $get_facility_data = Facility::where('facility_id',$facilityIdList)
    ->select('facility_id','facility_name_kanji')
    ->get()
    ->toArray();
    $get_facility_data = array_unique($get_facility_data,SORT_REGULAR);//事業所名が重複する物が不要の場合、下記のコードを適応する

    $maximumItems = count($get_facility_data);

    // JSON形式に変換
    $json = [
        "facility_name_kanji" => $get_facility_data,
        "maximum_items" => $maximumItems
    ];
    return response()->json($json);
}


public function popup_updata_service_ajax(GetFaclityUserInformationRequest $request){
    // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
    // 権限チェックのために"$request->postData1"を'$request->facility_user_id'に改修
    $facilityUserId = $request->facility_user_id;
    $user_facility_service_information_id = $request->postData2;
    // 各テーブルから必要なカラムを取得
    // 利用者毎の利用事業所情報（UserFacilityServiceInformation）と　左サイドで選択した利用者IDが合致した事業所IDを取得
    $facilityId = UserFacilityServiceInformation::where('facility_user_id',$facilityUserId)
    ->select('facility_id')
    ->orderBy('use_start', 'desc')
    ->get()
    ->toArray();
    $arrayList = array();//連想配列準備

    // アカウントに紐づいた事業所名　MFacilitiesRelations：account_id　取得
    $service = new FacilityService();
    $get_relation_facility_id = $service->getRelatedData();
    $facilityIdList = $get_relation_facility_id[0]['facility_id'];
    // 事業所IDのリストから事業所の情報を取得
    $get_facility_data = Facility::where('facility_id',$facilityIdList)
    ->select('facility_id','facility_name_kanji')
    ->get()
    ->toArray();
    $get_facility_data = array_unique($get_facility_data,SORT_REGULAR);//事業所名が重複する物が不要の場合、下記のコードを適応する
    // ↓↓名前昇順に変更（必要があれば）
    // asort($get_facility_data);
    // $get_facility_data = array_merge($get_facility_data);

    if($user_facility_service_information_id != 0){
        // 登録してある m_user_facility_infomations 取得
        $get_facility_informations = UserFacilityServiceInformation::where('user_facility_service_information_id',$user_facility_service_information_id)
        ->select('facility_id','service_id','usage_situation','use_start','use_end')
        ->orderBy('use_start', 'desc')
        ->get()
        ->toArray();

        $serviceTypeCodeId = Service::where('id',$get_facility_informations[0]['service_id'])
            ->select('service_type_code_id')
            ->first();

    }else{
        // 登録してない場合
        $get_facility_informations = 0;
        $serviceTypeCodeId = 0;
    }
    $arrayList = [];//連想配列準備
    // サービス種別　Service：service_type_code　取得
    for($i = 0; $i < count($get_facility_data); $i++){
        $data = Service::where('facility_id',$get_facility_data[$i]['facility_id'])
            ->select('service_type_code_id')
            ->get();
            if(!is_null($data)){
                array_push($arrayList,$data->toArray());
            }
        }
    $get_service_type_code_id = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    $get_service_type_code_id = array_merge(array_unique($get_service_type_code_id,SORT_REGULAR));
    asort($get_service_type_code_id);
    $get_service_type_code_id = array_merge($get_service_type_code_id);

    $arrayList = [];//連想配列準備
    // サービス種類名　MFacilitiesServiceType：service_type_code　取得
    for($i = 0; $i < count($get_service_type_code_id); $i++){
        $data = ServiceType::where('service_type_code_id',$get_service_type_code_id[$i]['service_type_code_id'])
            ->select('service_type_code_id','service_type_code','service_type_name')
            ->get();
            if(!is_null($data)){
                array_push($arrayList,$data->toArray());
            }
        }
        $get_service_type_name = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    $maximum_service_type_code_id = count($get_service_type_code_id);

    $maximum_facility_data = count($get_facility_data);

    // JSON形式に変換
    $json = [
        "facility_infomations" => $get_facility_informations,
        "facility_data" => $get_facility_data,
        "service_type_code" => $get_service_type_code_id,
        "service_type_name" => $get_service_type_name,
        "maximum_service_type_code" => $maximum_service_type_code_id,
        "maximum_facility_data" => $maximum_facility_data,
        'service_type_code_id' => $serviceTypeCodeId,
    ];
    return response()->json($json);
}

    public function getHistoryServiceInfo(GetHistoryServiceInfomationRequest $request)
    {
        $userFacilityServiceInformationId = $request->user_facility_service_information_id;
        $serviceInfo = UserFacilityServiceInformation::where('user_facility_service_information_id',$userFacilityServiceInformationId)
            ->get()
            ->toArray()[0];

        $serviceTypeCodeId = Service::where('id',$serviceInfo['service_id'])
            ->select('service_type_code_id')
            ->get()
            ->toArray()[0];

        $userInfo = array_merge($serviceInfo,$serviceTypeCodeId);
        return response()->json($userInfo);
    }

public function popup_facility_service_ajax(GetFaclityUserInformationRequest $request){
    // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
    // 権限チェックのために"$request->postData"を'$request->facility_id'に改修
    $facility_id = $request->facility_id;

    // サービス種類名　MFacilitiesServiceType：service_type_code　取得
    $get_service_type_code = Service::where('facility_id',$facility_id)
    ->select('service_type_code_id')
    ->get()
    ->toArray();
    $get_service_type_code = array_merge(array_unique($get_service_type_code,SORT_REGULAR));
    asort($get_service_type_code);
    $get_service_type_code = array_merge($get_service_type_code);
    $arrayList = array();//連想配列準備
    // サービス種類名　MFacilitiesServiceType：service_type_code　取得
    for($i = 0; $i < count($get_service_type_code); $i++){
        $data = ServiceType::where('service_type_code_id',$get_service_type_code[$i]['service_type_code_id'])
            ->select('service_type_code_id','service_type_code','service_type_name')
            ->get();
            if(!is_null($data)){
                array_push($arrayList,$data->toArray());
            }
        }
        $get_service_type_name = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    $maximumItems = count($get_service_type_code);

    // JSON形式に変換
    $json = [
        "service_type_code" => $get_service_type_code,
        "service_type_name" => $get_service_type_name,
        "maximum_items" => $maximumItems
    ];
    return response()->json($json);
}

public function service_store(ServiceRequest $request){
    // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
    // 権限チェックのために"$request->facilityId"を'$request->facility_id'に、"$request->facilityUserId"を'$request->facility_user_id'に改修
    $facilityId = $request->facility_id;
    $get_service_type_code_id = $request->serviceTypeCodeId;
    $get_usage_situation = $request->usageSituation;
    $get_use_start = $request->useStart;
    $get_use_end = $request->useEnd;
    $get_facility_user_id = $request->facility_user_id;
    $get_id_service = $request->saveGetIdService;

    // サービス種別コード(service_type_code_id)からサービスID(service_id)に変換
    $service_id = Service::where('service_type_code_id',$get_service_type_code_id)
    ->where('facility_id', $facilityId)
    ->select('id')
    ->get()
    ->toArray();
    $service_id = call_user_func_array("array_merge",$service_id);//連想配列を配列に変換
    $get_service_id = $service_id['id'];

    if($get_id_service == 0){
        // DB新規追加処理
        //DB保存準備
        $m_user_facility_infomations = new UserFacilityServiceInformation;

        //DB保存
        $m_user_facility_infomations->facility_id = $facilityId;
        $m_user_facility_infomations->service_id = $get_service_id;
        $m_user_facility_infomations->usage_situation = $get_usage_situation;
        $m_user_facility_infomations->use_start = $get_use_start;
        $m_user_facility_infomations->use_end = $get_use_end;
        $m_user_facility_infomations->facility_user_id = $get_facility_user_id;
        $m_user_facility_infomations->save();
    }else{
        // DB編集処理
        //DB保存準備
        $m_user_facility_infomations = UserFacilityServiceInformation::findOrFail($get_id_service);

        //DB保存
        $m_user_facility_infomations->facility_id = $facilityId;
        $m_user_facility_infomations->service_id = $get_service_id;
        $m_user_facility_infomations->usage_situation = $get_usage_situation;
        $m_user_facility_infomations->use_start = $get_use_start;
        $m_user_facility_infomations->use_end = $get_use_end;
        $m_user_facility_infomations->save();
    }
    // 利用者毎の利用事業所情報ID　UserFacilityServiceInformation：user_facility_service_information_id　取得
    // すべての利用履歴
    $get_facility_informations = UserFacilityServiceInformation::where('facility_user_id',$get_facility_user_id)
        ->select('user_facility_service_information_id','facility_id','service_id','usage_situation','use_start','use_end')
        ->orderBy('use_start', 'desc')
        ->get()
        ->toArray();

    $serviceTypeCodeId = Service::where('id',$get_facility_informations[0]['service_id'])
        ->select('service_type_code_id')
        ->first();

    $arrayList = array();//連想配列準備
    // サービスID(service_id)からサービス種別コード(service_type_code_id)に変換
    $arrayList = [];//連想配列初期化
    for($i = 0; $i < count($get_facility_informations); $i++){
        $data = Service::where('id',$get_facility_informations[$i]['service_id'])
        ->select('service_type_code_id')
        ->get();
        if(!is_null($data)){
            array_push($arrayList,$data->toArray());
        }
    }
    $get_service = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    // サービス種類名　MServiceType：service_type_name　取得
    $arrayList = [];//連想配列初期化
    for($i = 0; $i < count($get_facility_informations); $i++){
        $data = ServiceType::where('service_type_code_id',$get_service[$i]['service_type_code_id'])
        ->select('service_type_code','service_type_name')
        ->get();
        if(!is_null($data)){
            array_push($arrayList,$data->toArray());
        }
    }
    $get_service_type_name = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    // 事業所名　Facility：facility_name_kanji　取得
    $arrayList = [];//連想配列初期化
    for($i = 0; $i < count($get_facility_informations); $i++){
        $data = Facility::where('facility_id',$get_facility_informations[$i]['facility_id'])
        ->select('facility_name_kanji')
        ->get();
        if(!is_null($data)){
            array_push($arrayList,$data->toArray());
        }
    }
    $get_facility_data = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    // 事業所番号　Facility：facility_number　取得
    $arrayList = [];//連想配列初期化
    for($i = 0; $i < count($get_facility_informations); $i++){
        $data = Facility::where('facility_id',$get_facility_informations[$i]['facility_id'])
        ->select('facility_number')
        ->get();
        if(!is_null($data)){
            array_push($arrayList,$data->toArray());
        }
    }
    $get_facility_number = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    $maximumItems = count($get_facility_informations);


    // JSON形式に変換
    $json = [
      "facility_infomations" => $get_facility_informations,
      "facility_name_kanji" => $get_facility_data,
      "service_type_name" => $get_service_type_name,
      "facility_number" => $get_facility_number,
      "service_type_code_id" => $serviceTypeCodeId,
      "maximum_items" => $maximumItems
    ];
    return response()->json($json);
}

public function startDate(GetFaclityUserInformationRequest $request)
{
    $facilityUserId = $request->facility_user_id;
    $startDate = FacilityUser::find($facilityUserId)->toArray()['start_date'];
    return response()->json($startDate);
}


  /**
   * 認定情報
   * @author ttakenaka
   */
    public function approval_ajax(GetFaclityUserInformationRequest $request)
    {
    // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
    // 権限チェックのために"$request->postData"を'$request->facility_user_id'に改修
        $facilityUserId = $request->facility_user_id;

        $arrayList = array();//連想配列準備

        // 要介護度：MUserCareInfomations：care_level
        // 認定状況：MUserCareInfomations：certification_status
        // 認定年月日：MUserCareInfomations：recognition_date　取得
        $arrayList = [];//連想配列初期化
        $userCareData = UserCareInformation::where('facility_user_id', $facilityUserId)
            ->orderBy('certification_status', 'asc')
            ->orderBy('care_period_start', 'desc')
            ->select(
                'user_care_info_id',
                'care_level_id',
                'certification_status',
                'recognition_date',
                'care_period_start',
                'care_period_end',
                'date_confirmation_insurance_card',
                'date_qualification'
            )
            ->get()
            ->toArray();
        // 認定状況：MCareLevels：care_level_name　取得
        $arrayList = [];//連想配列初期化
        for ($i = 0; $i < count($userCareData); $i++) {
            $data = CareLevel::where('care_level_id', $userCareData[$i]['care_level_id'])
            ->select('care_level_name')
            ->get();
            if (!is_null($data)) {
                array_push($arrayList, $data->toArray());
            }
        }
        $careLevelName = call_user_func_array("array_merge", $arrayList);//連想配列を配列に変換

        $maximumItems = count($userCareData);

        // JSON形式に変換
        $json = [
            "user_care_data" => $userCareData,
            "care_level_name" => $careLevelName,
            "maximum_items" => $maximumItems
        ];

        return response()->json($json);
    }

public function popup_updata_approval_ajax(GetFaclityUserInformationRequest $request){
    // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
    $userCareInfoId = $request->user_care_info_id;

    // 登録してある m_user_care_infomations 取得
    $mUserCareInfomations = UserCareInformation::where('user_care_info_id',$userCareInfoId)
    ->orderBy('care_period_start', 'desc')
    ->select('user_care_info_id', 'care_level_id','certification_status','recognition_date',
            'care_period_start','care_period_end','date_confirmation_insurance_card','date_qualification')
    ->get()
    ->toArray();

    // 要介護度取得：MCareLevels：care_level　取得
    $arrayList = array();//連想配列準備
    for($i = 0; $i < count($mUserCareInfomations); $i++){
        $data = CareLevel::where('care_level_id',$mUserCareInfomations[$i]['care_level_id'])
        ->select('care_level')
        ->get();
        if(!is_null($data)){
            array_push($arrayList,$data->toArray());
        }
    }
    $careLevel = call_user_func_array("array_merge",$arrayList);//連想配列を配列に変換

    // JSON形式に変換
    $json = [
        "m_user_care_infomations" => $mUserCareInfomations,
        "care_level" => $careLevel
    ];
    return response()->json($json);
}

    public function getApprovalValuesCheckResult(Request $request)
    {
        $params = [
            'care_level' => $request->care_level,
            'certification_status' => $request->certification_status,
            'recognition_date' => $request->recognition_date,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'facility_user_id' => $request->facility_user_id,
            'save_id_approval' => $request->save_id_approval,
        ];

        $userApprovalInformation = new UserApprovalInformationService;
        // 認定期間のチェックを行う
        return $userApprovalInformation->getApprovalValuesCheckResult($params);
    }

    public function certificationStatusCheck($facilityId, $userCareInfoId)
    {
        // 認定状況の確認
        $checkCertificationStatus = UserCareInformation::where('facility_user_id', $facilityId)
        ->orderBy('care_period_start', 'desc')
        ->select(
            'user_care_info_id',
            'certification_status'
        )
        ->get()
        ->toArray();

        foreach ($checkCertificationStatus as $value) {
            // 更新対象自身以外に申請中が1件でもある場合、1を返す
            if ($value['certification_status'] === 1 && $value['user_care_info_id'] != $userCareInfoId) {
                return 1;
            }
        }
    }

    public function approval_store(ApprovalRequest $request)
    {
        // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
        // 権限チェックのために"$request->facilityId"だけ'$request->facility_user_id'に改修
        $careLevel = $request->careLevel;
        $certificationStatus = $request->certificationStatus;
        $recognitionDate = $request->recognitionDate;
        $carePeriodStart = $request->startDate;
        $carePeriodEnd = $request->endDate;
        $dateConfirmationInsuranceCard = $request->date_confirmation_insurance_card;
        $dateQualification = $request->date_qualification;
        $facilityId = $request->facility_user_id;
        $idApproval = $request->saveGetIdApproval;

        if ($certificationStatus == 1) {
            // 申請中のステータスのチェック
            $isCertificationStatus = $this->certificationStatusCheck($facilityId, $idApproval);

            if ($isCertificationStatus == 1) {
                // 申請中のデータがあった場合falseを返す
                return response()->json(false);
            }
        }

        //要介護度care_levelをcare_level_idに変換
        $careLevelId = CareLevel::where('care_level', $careLevel)
            ->select('care_level_id')
            ->get()
            ->toArray();
        $careLevelId = call_user_func_array("array_merge", $careLevelId);//連想配列を配列に変換
        $careLevelParam = $careLevelId['care_level_id'];

        if ($idApproval == 0) {
            // DB新規追加処理
            // DB保存準備
            $mUserCareInfomations = new UserCareInformation;

            //DB保存
            $mUserCareInfomations->care_level_id = $careLevelParam;
            $mUserCareInfomations->certification_status = $certificationStatus;
            $mUserCareInfomations->recognition_date = $recognitionDate;
            $mUserCareInfomations->care_period_start = $carePeriodStart;
            $mUserCareInfomations->care_period_end = $carePeriodEnd;
            $mUserCareInfomations->date_confirmation_insurance_card = $dateConfirmationInsuranceCard;
            $mUserCareInfomations->date_qualification = $dateQualification;
            $mUserCareInfomations->facility_user_id = $facilityId;
            $mUserCareInfomations->save();
        } else {
            // DB編集処理
            // DB保存準備
            $mUserCareInfomations = UserCareInformation::findOrFail($idApproval);

            //DB保存
            $mUserCareInfomations->care_level_id = $careLevelParam;
            $mUserCareInfomations->certification_status = $certificationStatus;
            $mUserCareInfomations->recognition_date = $recognitionDate;
            $mUserCareInfomations->care_period_start = $carePeriodStart;
            $mUserCareInfomations->care_period_end = $carePeriodEnd;
            $mUserCareInfomations->date_confirmation_insurance_card = $dateConfirmationInsuranceCard;
            $mUserCareInfomations->date_qualification = $dateQualification;
            $mUserCareInfomations->save();
        }

        $arrayList = array();//連想配列準備

        // 要介護度：MUserCareInfomations：care_level_id
        // 認定状況：MUserCareInfomations：certification_status
        // 認定年月日：MUserCareInfomations：recognition_date　取得
        $arrayList = [];//連想配列初期化
        $userCareData = UserCareInformation::where('facility_user_id', $facilityId)
            ->orderBy('care_period_start', 'desc')
            ->select(
                'user_care_info_id',
                'care_level_id',
                'certification_status',
                'recognition_date',
                'care_period_start',
                'care_period_end',
                'date_confirmation_insurance_card',
                'date_qualification'
            )
            ->get()
            ->toArray();

        // 認定状況：MCareLevels：care_level_name　取得
        $arrayList = [];//連想配列初期化
        for ($i = 0; $i < count($userCareData); $i++) {
            $data = CareLevel::where('care_level_id', $userCareData[$i]['care_level_id'])
            ->select('care_level', 'care_level_name')
            ->get();
            if (!is_null($data)) {
                array_push($arrayList, $data->toArray());
            }
        }
        $careLevelName = call_user_func_array("array_merge", $arrayList);//連想配列を配列に変換

        $maximumItems = count($userCareData);

        // JSON形式に変換
        $json = [
        "user_care_data" => $userCareData,
        "care_level_name" => $careLevelName,
        "maximum_items" => $maximumItems
        ];

        return response()->json($json);
    }
  /**
   * 自立度
   * @author ttakenaka
   */
    public function independence_ajax(GetFaclityUserInformationRequest $request){
        // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
        // 権限チェックのために"$request->postData"を'$request->facility_user_id'に改修
        $facilityUserId = $request->facility_user_id;
        // 障害高齢者自立度：UserIndependenceInformation　各種取得
            $get_independence_infomations = UserIndependenceInformation::where('facility_user_id',$facilityUserId)
            ->select('user_independence_informations_id','independence_level','dementia_level','judgment_date','judger',)
            ->orderBy('judgment_date', 'desc')
            ->get()
            ->toArray();
        $maximumItems = count($get_independence_infomations);

        // JSON形式に変換
        $json = [
        "independence_infomations" => $get_independence_infomations,
        "maximum_items" => $maximumItems
        ];

    return response()->json($json);
}


    public function popup_updata_independence_ajax(PopupUpdataIndependenceRequest $request){
    // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
    $user_independence_informations_id = $request->user_independence_informations_id;

    // 登録してある m_user_care_infomations 取得
    $get_user_independence_infomations = UserIndependenceInformation::where('user_independence_informations_id',$user_independence_informations_id)
    ->select('independence_level','dementia_level','judgment_date','judger')
    ->orderBy('judgment_date', 'desc')
    ->get()
    ->toArray();

    // JSON形式に変換
    $json = [
        "user_independence_infomations" => $get_user_independence_infomations
    ];
    return response()->json($json);
}
    public function independence_store(IndependenceRequest $request){
    // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
    // 権限チェックのために"$request->facilityId"だけ'$request->facility_user_id'に改修
    $get_independence_level = $request->independentIndependence;
    $get_dementia_level = $request->dementiaIndependence;
    $get_judgment_date = $request->judgmentDateIndependence;
    $get_judger = $request->judgeIndependence;
    $facilityId = $request->facility_user_id;
    $get_id_independence = $request->saveGetIdIndependence;

    if($get_id_independence == 0){
        // DB新規追加処理
        //DB保存準備
        $db_i_user_independence_infomations = new UserIndependenceInformation;

        //DB保存
        $db_i_user_independence_infomations->independence_level = $get_independence_level;
        $db_i_user_independence_infomations->dementia_level = $get_dementia_level;
        $db_i_user_independence_infomations->judgment_date = $get_judgment_date;
        $db_i_user_independence_infomations->judger = $get_judger;
        $db_i_user_independence_infomations->facility_user_id = $facilityId;
        $db_i_user_independence_infomations->save();
    }else{
        // DB編集処理
        //DB保存準備
        $db_i_user_independence_infomations = UserIndependenceInformation::findOrFail($get_id_independence);

        //DB保存
        $db_i_user_independence_infomations->independence_level = $get_independence_level;
        $db_i_user_independence_infomations->dementia_level = $get_dementia_level;
        $db_i_user_independence_infomations->judgment_date = $get_judgment_date;
        $db_i_user_independence_infomations->judger = $get_judger;
        $db_i_user_independence_infomations->save();
    }

    $arrayList = array();//連想配列準備
        // 障害高齢者自立度：UserIndependenceInformation　各種取得
        $get_independence_infomations = UserIndependenceInformation::where('facility_user_id',$facilityId)
        ->select('user_independence_informations_id','independence_level','dementia_level','judgment_date','judger',)
        ->orderBy('judgment_date', 'desc')
        ->get()
        ->toArray();
    $maximumItems = count($get_independence_infomations);

    // JSON形式に変換
    $json = [
    "independence_infomations" => $get_independence_infomations,
    "maximum_items" => $maximumItems
    ];

    return response()->json($json);
    }

    /**
     * 公費情報
     * @author hyamada
     */

    //公費マスタから法別番号・法別名称取得
    public function getPublicSpending()
    {
        $userPublicExpenseInformationService = new UserPublicExpenseInformationService();
        return $userPublicExpenseInformationService->getPublicSpending();
    }

    public function getPublicExpenditureHistory(GetFaclityUserInformationRequest $request){
        $userPublicExpenseInformationService = new UserPublicExpenseInformationService();
        $public_expenditure_history = $userPublicExpenseInformationService->getPublicExpenditureHistory($request->facility_user_id);

        return response()->json($public_expenditure_history);
    }

    //公費略称のチェック用データ
    public function getPublicSpendingCheckedData(GetFaclityUserInformationRequest $request){
        $userPublicExpenseInformationService = new UserPublicExpenseInformationService();
        $result = $userPublicExpenseInformationService->getPublicSpendingCheckedData(
            $request->facility_user_id,
            $request->facility_id
        );

        return $result;
    }

    // 新規登録・更新内容のエラーの有無等をチェック
    public function getPublicExpenditureValuesCheckResult(Request $request)
    {
        $params = [
            'facility_user_id' => $request->facility_user_id,
            'bearer_number' => $request->bearer_number,
            'recipient_number' => $request->recipient_number,
            'confirmation_medical_insurance_date'=> $request->confirmation_medical_insurance_date,
            'food_expenses_burden_limit' => $request->food_expenses_burden_limit,
            'living_expenses_burden_limit' => $request->living_expenses_burden_limit,
            'outpatient_contribution' => $request->outpatient_contribution,
            'hospitalization_burden' => $request->hospitalization_burden,
            'application_classification' => $request->application_classification,
            'special_classification' => $request->special_classification,
            'effective_start_date' => $request->effective_start_date,
            'expiry_date' => $request->expiry_date,
            'update_type' => $request->update_type
        ];

        if(isset($request->public_expense_information_id)){
            $params['public_expense_information_id'] = $request->public_expense_information_id;
        }

        $userPublicExpenseInformationService = new UserPublicExpenseInformationService();
        return $userPublicExpenseInformationService->getPublicExpenditureValuesCheckResult($params);
    }

    // 公費情報の新規登録・更新
    public function publicExpenditureSave(PublicExpenditureRequest $request){

        //権限チェックのために$request->'facilityUserID'だけ改修
        $param = [
            'facility_user_id' => $request->facility_user_id,
            'bearer_number' => $request->bearer_number,
            'recipient_number' => $request->recipient_number,
            'confirmation_medical_insurance_date' => $request->confirmation_medical_insurance_date,
            'food_expenses_burden_limit' => $request->food_expenses_burden_limit,
            'living_expenses_burden_limit' => $request->living_expenses_burden_limit,
            'outpatient_contribution' => $request->outpatient_contribution,
            'hospitalization_burden' => $request->hospitalization_burden,
            'application_classification' => $request->application_classification,
            'special_classification' => $request->special_classification,
            'effective_start_date' => $request->effective_start_date,
            'expiry_date' => $request->expiry_date,
            'amount_borne_person' => $request->amount_borne_person,
        ];

        if(isset($request->public_expense_information_id)){
            $param['public_expense_information_id'] = $request->public_expense_information_id;
        }

        $userPublicExpenseInformationService = new UserPublicExpenseInformationService();
        $result = $userPublicExpenseInformationService->publicExpenditureSave($param);

        return response()->json($result);
    }


   /**
   * 給付率
   * @author hyamada
   */

    // 該当利用者の給付率の履歴情報を取得
    public function getBenefitHistory(GetFaclityUserInformationRequest $request)
    {
        $facilityUserId = $request->facility_user_id;

        $userBenefitInformation = new UserBenefitInformationService;
        $benefit_history = $userBenefitInformation->getBenefitHistory($facilityUserId);

        return response()->json($benefit_history);
    }

    // 給付率の履歴から情報を取得
    public function getBenefitData(GetFaclityUserInformationRequest $request){
        $targetId = $request->benefit_information_id;

        $userBenefitInformation = new UserBenefitInformationService;
        $benefitData = $userBenefitInformation->getBenefitData($targetId);

        return response()->json($benefitData);
    }

    // 新規登録・更新内容のエラーの有無等をチェック
    public function getBenefitValuesCheckResult(Request $request)
    {
        $params = [
            'benefit_type' => $request->benefit_type,
            'benefit_rate' => $request->benefit_rate,
            'effective_start_date' => $request->effective_start_date,
            'expiry_date' => $request->expiry_date,
            'facility_user_id' => $request->facility_user_id,
            'post_type' => $request->post_type
        ];

        if(isset($request->benefit_information_id)){
            $params['benefit_information_id'] = $request->benefit_information_id;
        }

        $userBenefitInformation = new UserBenefitInformationService;
        return $userBenefitInformation->getBenefitValuesCheckResult($params);
    }

    // 給付情報の新規登録・更新
    public function benefitSave(BenefitInputRequest $request){

        //権限チェックのために$request->'facilityUserID'だけ改修
        $param = [
            'facility_user_id'=> $request->facility_user_id,
            'benefit_type'=> $request->benefit_type,
            'benefit_rate'=> $request->benefit_rate,
            'effective_start_date'=> $request->effective_start_date,
            'expiry_date'=> $request->expiry_date,
        ];

        if(isset($request->benefit_information_id)){
            $param['benefit_information_id'] = $request->benefit_information_id;
        }

        $userBenefitInformation = new UserBenefitInformationService;
        $res = $userBenefitInformation->benefitSave($param);

        return response()->json($res);
    }

    // 該当利用者の被保険者番号を取得
    public function getInsuredNo(GetFaclityUserInformationRequest $request)
    {
        $facilityUserId = $request->facility_user_id;
        $InsuredNo = FacilityUserService::getInsuredNo($facilityUserId);

        return response()->json($InsuredNo);
    }
}
