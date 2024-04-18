<?php

namespace App\Http\Controllers\GroupHome\FacilityInfo;

use App\Models\Corporation;
use App\Models\Facility;
use App\Models\Institution;
use App\Models\ServiceType;
use App\Models\Service;

use App\Service\GroupHome\ServiceTypeService;
use App\Service\GroupHome\UninsuredCostService;
use App\Http\Requests\GetFaclityInformationRequest;
use App\Http\Requests\GroupHome\FacilityInfo\CorporationRequest;
use App\Http\Requests\GroupHome\FacilityInfo\DeleteServiceItemRequest;
use App\Http\Requests\GroupHome\FacilityInfo\FacilityRequest;
use App\Http\Requests\GroupHome\FacilityInfo\FaclityServiceRequest;
use App\Http\Requests\GroupHome\FacilityInfo\GetUninsuredItemHistories;
use App\Http\Requests\GroupHome\FacilityInfo\InstitutionRequest;
use App\Http\Requests\GroupHome\FacilityInfo\iUninsuredItemRequest;
use App\Http\Requests\GroupHome\FacilityInfo\iUninsuredItemHistoryRequest;
use App\Http\Requests\GroupHome\FacilityInfo\ServiceTypeRequest;
use App\Http\Requests\GroupHome\FacilityInfo\iUninsuredItemSortRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; //Ajax通信の為に追加
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class FacilityInfoController extends Controller
{
    public function index()
    {
        return view('group_home.facility_info.facility_info');
    }

    /**
     * 法人
     * @author eikeda
     */
    public function corporation(CorporationRequest $request)
    {
        $corporations = Corporation::findOrFail($request->corporation_id);

        $json = [
            "corporations" => $corporations,
        ];

        return response()->json($json);
    }

    public function corporation_update(CorporationRequest $request)
    {
        //法人更新処理
        $db_corporations = Corporation::findOrFail($request->corporation_id);

        $db_corporations->name = $request->corporation_name;
        $db_corporations->abbreviation = $request->corporation_name_abbr;
        $db_corporations->representative = $request->corporation_rep;
        $db_corporations->phone_number = $request->corporation_phone;
        $db_corporations->fax_number = $request->corporation_fax;
        $db_corporations->postal_code = $request->corporation_postalcode;
        $db_corporations->location = $request->corporation_address;
        $db_corporations->remarks = $request->corporation_remarks;
        $db_corporations->save();
    }


    /**
     * 施設
     * @author eikeda
     */
    public function institution(InstitutionRequest $request)
    {
        $institution = Institution::findOrFail($request->institution_id);

        $json = [
            "institution" => $institution,
        ];

        return response()->json($json);
    }

    public function institution_update(InstitutionRequest $request)
    {
        //施設更新処理
        $db_institutions = Institution::findOrFail($request->institution_id);

        $db_institutions->name = $request->institution_name;
        $db_institutions->abbreviation = $request->institution_abbr;
        $db_institutions->representative = $request->institution_rep;
        $db_institutions->phone_number = $request->institution_phone;
        $db_institutions->fax_number = $request->institution_fax;
        $db_institutions->postal_code = $request->institution_postalcode;
        $db_institutions->location = $request->institution_address;
        $db_institutions->remarks = $request->institution_remarks;
        $db_institutions->save();
    }

    /**
     * 事業所
     * @author eikeda
     */
    public function office(FacilityRequest $request)
    {
        $facility = Facility::findOrFail($request->facility_id);

        $json = [
            "facility" => $facility,
        ];

        return response()->json($json);
    }

    public function office_update(FacilityRequest $request)
    {
        //事業所更新処理
        $db_facility = Facility::findOrFail($request->facility_id);

        $db_facility->facility_number = $request->office_number;
        $db_facility->facility_name_kanji = $request->office_name_kanji;
        $db_facility->facility_name_kana = $request->office_name_kana;
        $db_facility->abbreviation = $request->office_name_abbr;
        $db_facility->facility_manager = $request->office_manager;
        $db_facility->insurer_no = $request->office_insurer_no;
        $db_facility->postal_code = $request->office_postal_code;
        $db_facility->location = $request->office_location;
        $db_facility->phone_number = $request->office_phone_number;
        $db_facility->fax_number = $request->office_fax_number;
        $db_facility->remarks = $request->office_remarks;
        $db_facility->job_title = $request->office_job_title;
        $db_facility->save();
    }


    /**
     * サービス種別
     * @author ttakenaka
　   */
    public function service_type_ajax(GetFaclityInformationRequest $request)
    {
        // サービス種類利用履歴取得
        // 権限チェックのために"$request->postData1"を'$request->facility_id'に改修
        $get_facility_id = $request->facility_id;
        $get_service_type = $request->postData2;
        if ($get_service_type == 0) {
            // 事業所選択をした場合全取得
            $get_service_type = Service::where('facility_id', $get_facility_id)
                ->select('id', 'service_type_code_id', 'area', 'change_date')
                ->orderBy('change_date', 'desc')
                ->get()
                ->toArray();
        } else {
            // サービスタイプを選択した場合
            $get_service_type = Service::where('facility_id', $get_facility_id)
                ->where('service_type_code_id', $get_service_type)
                ->select('id', 'service_type_code_id', 'area', 'change_date', 'first_plan_input')
                ->orderBy('change_date', 'desc')
                ->get()
                ->toArray();
        }

        $serviceType = new ServiceTypeService();
        $facilityServiceAll = $serviceType->get($get_facility_id);

        $maximum_items = count($get_service_type);

        $array_list = array(); //連想配列準備
        // サービス種類名　MServiceType：service_type_name　取得
        $array_list = []; //連想配列初期化
        for ($i = 0; $i < count($get_service_type); $i++) {
            $get_data = ServiceType::where('service_type_code_id', $get_service_type[$i]['service_type_code_id'])
                ->select(
                    'service_type_code',
                    'service_type_name',
                    'area_unit_price_1',
                    'area_unit_price_2',
                    'area_unit_price_3',
                    'area_unit_price_4',
                    'area_unit_price_5',
                    'area_unit_price_6',
                    'area_unit_price_7',
                    'area_unit_price_8',
                    'area_unit_price_9',
                    'area_unit_price_10'
                )
                ->get();
            if (!($get_data == null)) {
                array_push($array_list, $get_data->toArray());
            }
        }
        $get_service_type2 = call_user_func_array("array_merge", $array_list); //連想配列を配列に変換

        // JSON形式に変換
        $json = [
            "get_service_type" => $get_service_type,
            "get_service_type2" => $get_service_type2,
            "maximum_items" => $maximum_items,
            'service_type_all' => $facilityServiceAll,
        ];
        return response()->json($json);
    }

    public function service_type_update_from(FaclityServiceRequest $request)
    {
        // サービス種類利用履歴取得
        $serviceTypeUpdateId = $request->service_id;
        $get_service_type = Service::where('id', $serviceTypeUpdateId)
            ->select('service_type_code_id', 'area', 'change_date')
            ->orderBy('change_date', 'desc')
            ->get()
            ->toArray();

        $maximum_items = count($get_service_type);

        $array_list = array(); //連想配列準備
        // サービス種類名　MServiceType：service_type_name　取得
        $array_list = []; //連想配列初期化
        for ($i = 0; $i < count($get_service_type); $i++) {
            $get_data = ServiceType::where('service_type_code_id', $get_service_type[$i]['service_type_code_id'])
                ->select(
                    'service_type_name',
                    'service_type_code',
                    'area_unit_price_1',
                    'area_unit_price_2',
                    'area_unit_price_3',
                    'area_unit_price_4',
                    'area_unit_price_5',
                    'area_unit_price_6',
                    'area_unit_price_7',
                    'area_unit_price_8',
                    'area_unit_price_9',
                    'area_unit_price_10'
                )
                ->get();
            if (!($get_data == null)) {
                array_push($array_list, $get_data->toArray());
            }
        }
        $get_service_type2 = call_user_func_array("array_merge", $array_list); //連想配列を配列に変換

        // JSON形式に変換
        $json = [
            "get_service_type" => $get_service_type,
            "get_service_type2" => $get_service_type2,
            "maximum_items" => $maximum_items
        ];
        return response()->json($json);
    }

    public function service_type_list()
    {
        // プルダウンメニュー：サービス種類名取得
        $get_service_type = ServiceType::select('service_type_code', 'service_type_name')
            ->get()
            ->toArray();

        $maximum_items = count($get_service_type);

        // JSON形式に変換
        $json = [
            "get_service_type" => $get_service_type,
            "maximum_items" => $maximum_items
        ];
        return response()->json($json);
    }

    public function service_type_store(ServiceTypeRequest $request)
    {
        // 左サイドで選択した利用者ID（MFacilityUser：facility_user_id）を取得
        $get_facility_id = $request->facility_id;
        $get_service_type_code = $request->service_type_code;
        $get_area = $request->area;
        $get_change_date = $request->change_date;
        $get_id_service = $request->saveGetIdServiceType;
        $get_service_type_num = $request->saveLeftGetIdServiceType;
        $first_plan_input = $request->first_plan_input;

        // サービス種別コード(service_type_code)からサービス種別ID(service_type_code_id)に変換
        $service_type_code_id = ServiceType::where('service_type_code', $get_service_type_code)
            ->select('service_type_code_id')
            ->get()
            ->toArray();
        $service_type_code_id = call_user_func_array("array_merge", $service_type_code_id); //連想配列を配列に変換
        $get_service_type_code_id = $service_type_code_id['service_type_code_id'];

        if ($get_id_service == 0) {
            // DB新規追加処理
            //DB保存準備
            $i_service = new Service;

            //DB保存
            $i_service->facility_id = $get_facility_id;
            $i_service->service_type_code_id = $get_service_type_code_id;
            $i_service->area = $get_area;
            $i_service->change_date = $get_change_date;
            $i_service->first_plan_input = $first_plan_input;
            $i_service->save();
        } else {
            // DB編集処理
            //DB保存準備
            $i_service = Service::findOrFail($get_id_service);
            //DB保存
            $i_service->facility_id = $get_facility_id;
            $i_service->service_type_code_id = $get_service_type_code_id;
            $i_service->area = $get_area;
            $i_service->change_date = $get_change_date;
            $i_service->first_plan_input = $first_plan_input;
            $i_service->save();
        }
        // サービス種類利用履歴取得

        if ($get_service_type_num == 0) {
            // 事業所選択をした場合全取得
            $get_service_type = Service::where('facility_id', $get_facility_id)
                ->select('id', 'service_type_code_id', 'area', 'change_date')
                ->orderBy('change_date', 'desc')
                ->get()
                ->toArray();
        } else {
            // サービスタイプを選択した場合
            $get_service_type = Service::where('facility_id', $get_facility_id)
                ->where('service_type_code_id', $get_service_type_num)
                ->select('id', 'service_type_code_id', 'area', 'change_date')
                ->orderBy('change_date', 'desc')
                ->get()
                ->toArray();
        }

        $maximum_items = count($get_service_type);

        $array_list = array(); //連想配列準備
        // サービス種類名　MServiceType：service_type_name　取得
        $array_list = []; //連想配列初期化
        for ($i = 0; $i < count($get_service_type); $i++) {
            $get_data = ServiceType::where('service_type_code_id', $get_service_type[$i]['service_type_code_id'])
                ->select(
                    'service_type_code',
                    'service_type_name',
                    'area_unit_price_1',
                    'area_unit_price_2',
                    'area_unit_price_3',
                    'area_unit_price_4',
                    'area_unit_price_5',
                    'area_unit_price_6',
                    'area_unit_price_7',
                    'area_unit_price_8',
                    'area_unit_price_9',
                    'area_unit_price_10'
                )
                ->get();
            if (!($get_data == null)) {
                array_push($array_list, $get_data->toArray());
            }
        }
        $get_service_type2 = call_user_func_array("array_merge", $array_list); //連想配列を配列に変換

        // JSON形式に変換
        $json = [
            "get_service_type" => $get_service_type,
            "get_service_type2" => $get_service_type2,
            "maximum_items" => $maximum_items
        ];
        return response()->json($json);
    }

    /**
     * サービス種別選択時処理
     */
    public function getUninsuredServiceHistory(GetFaclityInformationRequest $request)
    {
        $uninsuredCostSurvice = new UninsuredCostService();
        $result = $uninsuredCostSurvice->getUninsuredServiceHistory($request->service_id);

        return $result;
    }

    /**
     * 履歴選択時処理
     */
    public function getUninsuredItemHistories(GetUninsuredItemHistories $request)
    {
        $uninsuredCostSurvice = new UninsuredCostService();
        $result = $uninsuredCostSurvice->getUninsuredItemHistories($request->id);

        return response()->json($result);
    }

    /**
     * サービスの初回登録
     */
    public function firstServiceRegister(iUninsuredItemRequest $request)
    {
        // どうせ押されたタイミングのDateが送られてくるだけなのでRequestの値は見る必要ない
        $thisMonth = (new Carbon())->startOfMonth();

        $param = [
            'service_id' => $request->service_id,
            'start_month' => $thisMonth->format("Y-m-d"),
        ];

        $uninsuredCostSurvice = new UninsuredCostService();
        $result = $uninsuredCostSurvice->firstServiceRegister($param);

        if ($result) {
            return response()->json(true);
        }
        abort(500);
    }

    /**
     * 新しい月のサービス登録
     */
    public function newMonthService(iUninsuredItemRequest $request)
    {
        // どうせ押されたタイミングのDateが送られてくるだけなのでRequestの値は見る必要ない

        // 先月末を取りたいが内部計算どうなってるか怖いので月初に一回セットしてからマイナス1日で計算する
        $beforeMonth = (new Carbon())->startOfMonth()->subDay();

        $thisMonth = new Carbon();
        $thisMonth->startOfMonth();


        $updateParam = [
            'id' => $request->close_uninsured_items_id,
            'end_month' => $beforeMonth->format("Y-m-d"),
        ];

        $newParam = [
            'service_id' => $request->service_id,
            'start_month' => $thisMonth->format("Y-m-d"),
        ];

        $newItemList = $request->latest_item_list;

        $uninsuredCostSurvice = new UninsuredCostService();
        $result = $uninsuredCostSurvice->newMonthService($updateParam, $newParam, $newItemList);

        if ($result) {
            return response()->json($result);
        }
        abort(500);
    }

    /**
     * サービス内容の新規登録・更新
     */
    public function saveUninsuredItem(iUninsuredItemHistoryRequest $request)
    {
        //権限チェックのために$request->uninsured_item_idだけ改修
        $param = [
            'uninsured_item_id' => $request->uninsured_item_id,
            'item' => $request->item,
            'unit_cost' => $request->unit_cost,
            'unit' => $request->unit,
            'set_one' => $request->set_one,
            'fixed_cost' => $request->fixed_cost,
            'variable_cost' => $request->variable_cost,
            'welfare_equipment' => $request->welfare_equipment,
            'meal' => $request->meal,
            'daily_necessary' => $request->daily_necessary,
            'hobby' => $request->hobby,
            'escort' => $request->escort,
            'billing_reflect_flg' => $request->billing_reflect_flg,
        ];

        if (isset($request->id)) {
            $param['id'] = $request->id;
        } elseif (isset($request->sort)) {
            $param['sort'] = $request->sort;
        }

        $uninsuredCostSurvice = new UninsuredCostService();
        $result = $uninsuredCostSurvice->saveUninsuredItem($param);

        return response()->json($result);
    }

    /**
     * 品目の削除
     */
    public function deleteServiceItem(DeleteServiceItemRequest $request)
    {
        $uninsuredCostSurvice = new UninsuredCostService();
        $result = $uninsuredCostSurvice->deleteServiceItem($request->id);

        return response()->json($result);
    }

    /**
     * 品目ソートの変更保存
     */
    public function saveSort(iUninsuredItemSortRequest $request)
    {
        $uninsuredItemHistoryIdList = $request->uninsured_item_history_id_list;
        for ($i=0; $i<count($uninsuredItemHistoryIdList); $i++) {
            $params = [
                'id' => $uninsuredItemHistoryIdList[$i],
                'sort' => $i + 1
            ];
            $uninsuredCostService = new UninsuredCostService();
            $result = $uninsuredCostService->saveSort($params);
            if (!$result) {
                abort(500);
            }
        }

        // 暫定
        // 今後、何らかの理由で登録できなかった内容を配列で返す可能性も
        return [];
    }
}
