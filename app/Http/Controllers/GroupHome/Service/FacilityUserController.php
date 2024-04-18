<?php

namespace App\Http\Controllers\GroupHome\Service;

use App\Authorization\FacilityUserAccessAuthorization;
use App\Models\UserFacilityInformation;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupHome\Service\FacilityUserGetHeaderRequest;
use App\Http\Requests\GroupHome\Service\FacilityUserGetBillingTargetRequest;
use App\Http\Requests\GroupHome\Service\FacilityUserGetRequest;
use App\Http\Requests\GroupHome\Service\FacilityUserGetDaysRequest;
use App\Http\Requests\GroupHome\Service\FacilityUserFormRequest;
use App\Service\GroupHome\FacilityService;
use App\Service\GroupHome\FacilityUserService;

use Illuminate\Http\Exceptions\HttpResponseException;

class FacilityUserController extends Controller
{
    /**
     * 施設利用者一人のデータを返す
     * @param FacilityUserGetHeaderRequest key: facility_user_id,year,month
     */
    public function getHeader(FacilityUserGetHeaderRequest $request)
    {
        // 必要なパラメーターを取得
        $params = [
            'facility_user_id' => $request->facility_user_id,
            'year' => $request->year,
            'month' => $request->month
        ];

        $facilityUserService = new FacilityUserService();
        return $facilityUserService->getUserHeader($params);
    }

    /**
     * (事業所と対象年月から取得した)施設利用者について請求対象者の情報を返す。
     * @param FacilityUserGetBillingTargetRequest $request
     */
    public function getBillingTarget(FacilityUserGetBillingTargetRequest $request)
    {
        $facilityUserService = new FacilityUserService();
        $data = $facilityUserService->getBillingTarget($request->facility_id, $request->year, $request->month);
        return $data;
    }

    /**
     * 施設利用者について対象年月の外泊日を全て返す。
     * @param FacilityUserGetDaysRequest $request
     */
    public function getStayOutDays(FacilityUserGetDaysRequest $request)
    {
        $facilityUserService = new FacilityUserService();
        $stayOutDays = $facilityUserService->getStayOutDays($request->facility_user_id, $request->year, $request->month);
        return $stayOutDays;
    }

    /**
     * 施設利用者について対象年月の入居日までの日付を返す。
     * @param FacilityUserGetDaysRequest $request
     */
    public function getStartDates(FacilityUserGetDaysRequest $request)
    {
        $facilityUserService = new FacilityUserService();
        $startDates = $facilityUserService->getStartDates($request->facility_user_id, $request->year, $request->month);
        return $startDates;
    }

    /**
     * 施設利用者について対象年月の退居日からの日付を返す。
     * @param FacilityUserGetDaysRequest $request
     */
    public function getEndDates(FacilityUserGetDaysRequest $request)
    {
        $facilityUserService = new FacilityUserService();
        $endDates = $facilityUserService->getEndDates($request->facility_user_id, $request->year, $request->month);
        return $endDates;
    }

    /**
     * @param FacilityUserGetRequest $request key: facility_user_id_list,clm,approval,benefit_rate,care_info
     *     approval: {year:年, month:月}
     *     benefit_rate: {year:年, month:月}
     *     care_info: {clm_list:取得するキーのリスト, year:年, month:月, with:{care_level:取得するキーのリスト}}
     */
    public function getData(FacilityUserGetRequest $request)
    {
        // 必要なパラメーターを取得
        $param = [
            'facility_user_id_list' => $request->facility_user_id_list,
            'clm' => $request->clm,
        ];
        if ($request->approval) {
            $param['approval'] = $request->approval;
        }
        if ($request->benefit_rate) {
            $param['benefit_rate'] = $request->benefit_rate;
        }
        if ($request->care_info) {
            $param['care_info'] = $request->care_info;
        }

        // facility_user_id_listがリクエストに含まれない場合、アクセス可能な全ての施設利用者IDをセットする
        if ($param['facility_user_id_list'] === null) {
            $service = new FacilityService();
            $facilityIdList = array_column($service->getRelatedData(), 'facility_id');
            $faciliyUser = UserFacilityInformation::whereIn('facility_id', $facilityIdList)
                ->select('facility_user_id')
                ->get()
                ->toArray();
            $param['facility_user_id_list'] = array_column($faciliyUser, 'facility_user_id');
        }

        // ユーザーの施設利用者情報へのアクセス権を確認
        $authorization = new FacilityUserAccessAuthorization();
        if ($authorization->can($param['facility_user_id_list'])) {
            $facilityUserService = new FacilityUserService();
            return $facilityUserService->getData($param);
        } else {
            throw new HttpResponseException(response()->json([], 400));
        }
    }

    /**
     * @param FacilityUserFormRequest $request
     */
    public function insertForm(FacilityUserFormRequest $request){
        // パラメーターを作成
        $facilityUserData = $request->only(['insurer_no','insured_no','last_name','first_name','last_name_kana','first_name_kana',
            'gender','birthday','postal_code','location1','location2','phone_number',
            'start_date','end_date','death_date','death_reason','blood_type','rh_type','cell_phone_number','before_in_status_id',
            'after_out_status_id','diagnosis_date','diagnostician','consent_date','consenter','consenter_phone_number','invalid_flag',
            'spacial_address_flag'
        ]);
        $facilityID = $request['facility_id'];
        $contractorNumber = $request['contractor_number'];

        $facilityUserService = new FacilityUserService();
        $facilityUserService->insert($facilityUserData, $facilityID, $contractorNumber);

        return redirect()->route('group_home.user_info');
    }

    /**
     * @param FacilityUserFormRequest $request
     */
    public function updateForm(FacilityUserFormRequest $request){
        // パラメーターを作成
        $facilityID = $request['facility_id'];
        $facilityUserID = $request['facility_user_id'];
        $contractorNumber = $request['contractor_number'];
        $facilityUserData = $request->only(['insurer_no','insured_no','last_name','first_name','last_name_kana','first_name_kana',
            'gender','birthday','postal_code','location1','location2','phone_number',
            'start_date','end_date','death_date','death_reason','blood_type','rh_type','cell_phone_number','before_in_status_id',
            'after_out_status_id','diagnosis_date','diagnostician','consent_date','consenter','consenter_phone_number','invalid_flag',
            'spacial_address_flag'
        ]);

        $facilityUserService = new FacilityUserService();
        $facilityUserService->update($facilityID, $facilityUserID, $contractorNumber, $facilityUserData);

        return redirect()->route('group_home.user_info');
    }
}
