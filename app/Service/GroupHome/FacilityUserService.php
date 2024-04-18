<?php

namespace App\Service\GroupHome;

use App\Lib\FacilityUserStayOut;
use App\Models\FacilityUser;
use App\Models\CareLevel;
use App\Models\UserBenefitInformation;
use App\Models\UserCareInformation;
use App\Models\UserFacilityInformation;
use App\Models\ServicePlan;
use App\Models\ServiceResult;
use App\Models\StayOutManagement;
use App\Service\GroupHome\ActualDaysService;
use App\Service\GroupHome\StayOutService;
use App\Service\GroupHome\UserBenefitInformationService;
use App\Service\GroupHome\UserCareInformationService;
use App\Service\GroupHome\UserIndependenceInformationService;

use Carbon\CarbonImmutable;
use Carbon\Carbon;

class FacilityUserService
{
    /**
     * 利用者情報ヘッダのデータを返す
     * @param array $params key: facility_user_id,year,month
     * @return array
     */
    public function getUserHeader(array $params) : array
    {
        $year = $params['year'];
        $month = $params['month'];
        $targetDate = new CarbonImmutable("${year}-${month}-1");
        $targetYM = $targetDate->format('Ym');

        $facilityUsers = FacilityUser::where('facility_user_id', $params['facility_user_id'])
            ->select(
                'birthday',
                'death_date',
                'end_date',
                'first_name',
                'first_name_kana',
                'facility_user_id',
                'gender',
                'insured_no',
                'insurer_no',
                'last_name',
                'last_name_kana',
                'start_date',
                'end_date'
            )
            ->get()
            ->toArray();

        // 介護度
        $facilityUsers = $this->getJoinCareInformation(
            ['care_level_id','care_period_end','care_period_start','certification_status','facility_user_id','recognition_date'],
            $facilityUsers,
            [$params['facility_user_id']],
            'm_care_levels:care_level_id,care_level_name',
            $params['year'],
            $params['month']
        );

        // 外泊日数
        $actualDaysService = new ActualDaysService();
        $actualDays = $actualDaysService->get([
            'death_date' => $facilityUsers[0]['death_date'],
            'end_date' => $facilityUsers[0]['end_date'],
            'facility_user_id' => $params['facility_user_id'],
            'start_date' => $facilityUsers[0]['start_date'],
            'target_ym' => $targetYM
        ]);
        $facilityUsers[0]['stay_out'] = count($actualDays['stay_out_days']);

        $facilityUsers[0]['stay_out_periods'] = [];
        $stayoutService = new StayOutService();
        if ($facilityUsers[0]['stay_out'] > 0) {
            $facilityUsers[0]['stay_out_periods'] = $stayoutService->getStayoutPeriod([
                'facility_user_id' => $params['facility_user_id'],
                'target_ym' => $targetYM
            ]);
        }

        // 承認
        $facilityUsers = $this->getJoinApproval(
            $facilityUsers,
            $params['year'],
            $params['month']
        );

        // 自立度
        $facilityUsers = $this->getJoinIndependenceInformation(
            ['facility_user_id','dementia_level','independence_level'],
            $facilityUsers,
            [$params['facility_user_id']],
            $params['year'],
            $params['month']
        );

        // 入居実日数
        $facilityUsers[0]['actualDays'] = $actualDays['actual_day_cnt'];

        return $facilityUsers[0];
    }

    /**
     * 施設利用者の内、請求対象者の情報を返す。
     * @param int $facilityId
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getBillingTarget(int $facilityId, int $year, int $month): array
    {
        // 事業所に紐づく施設利用者全てのIDを取得する。
        $faciliyUsers = UserFacilityInformation::where('facility_id', $facilityId)
            ->get()
            ->toArray();
        $facilityUserIds = array_column($faciliyUsers, 'facility_user_id');

        // 請求対象の施設利用者を全て取得する。
        // TODO: 請求対象かの知識はモデルが持つべきではない。
        $facilityUsers = FacilityUser::listBillingTarget($facilityUserIds, $year, $month);

        // 施設利用者全員の承認フラグを取得する。
        $serviceResults = ServiceResult::listFacilityUserTargetYmTotal($facilityUserIds, $year, $month);
        $serviceResults = array_column($serviceResults, null, 'facility_user_id');

        // 施設利用者全員の対象年月の介護情報を取得する。
        $userCareInformations = UserCareInformation::listFacilityUserTargetMonthLatest($facilityUserIds, $year, $month);
        $userCareInformations = array_column($userCareInformations, null, 'facility_user_id');

        // レスポンスデータを作成する。
        $response = [];
        for ($i = 0, $cnt = count($facilityUsers); $i < $cnt; $i++) {
            $facilityUser = $facilityUsers[$i];
            $facilityUserId = $facilityUser['facility_user_id'];

            $approval = null;
            if(array_key_exists($facilityUserId, $serviceResults)){
                $approval = $serviceResults[$facilityUserId]['approval'];
            }

            $careLevelname = null;
            if(array_key_exists($facilityUserId, $userCareInformations)){
                $careLevelname = $userCareInformations[$facilityUserId]['m_care_levels']['care_level_name'];
            }

            $response[$i] = [
                'approval' => $approval,
                'care_level_name' => $careLevelname,
                'facility_user_id' => $facilityUserId,
                'first_name' => $facilityUser['first_name'],
                'first_name_kana' => $facilityUser['first_name_kana'],
                'last_name' => $facilityUser['last_name'],
                'last_name_kana' => $facilityUser['last_name_kana']
            ];
        }

        return $response;
    }

    /**
     * 施設利用者について対象年月の外泊日を全て返す。
     * @param int $facilityUserId
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getStayOutDays(int $facilityUserId, int $year, int $month): array
    {
        // 施設利用者の対象年月の外泊情報を取得する。
        $stayOuts = StayOutManagement::getTargetYm($facilityUserId, $year, $month);

        // 施設利用者の退去日を取得する。
        $facilityUser = FacilityUser::where('facility_user_id', $facilityUserId)->first();

        // 施設利用者の対象年月の外泊日を全て取得する。
        $stayOutDays = FacilityUserStayOut::listTargetYmDays(
            $stayOuts,
            $facilityUser->end_date,
            $year,
            $month
        );

        return $stayOutDays;
    }

    /**
     * 施設利用者について対象年月の入居日までの日付を全て返す。
     * 取得した入居日を文字列で扱うため、carbonインスタンスは未使用。
     * @param int $facilityUserId
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getStartDates(int $facilityUserId, int $year, int $month): array
    {
        $facilityUser = FacilityUser::where('facility_user_id', $facilityUserId)->first();
        $startDate = $facilityUser->start_date;
        $startYear = substr($startDate, 0, 4);
        $startMonth = substr($startDate, 5, 2);

        $startDaysConsecutive = [];
        if ($startYear == $year && $startMonth == $month){
            $startDay = substr($startDate, 8, 2);
            // 文字列の入居日で十の位が0の場合、0を削除し数値へ変換する。
            if (strpos(substr($startDay, 0, 1), '0') !== false){
                (int)$startDay = substr($startDay, 1);
            }
            // 入居日まで(入居日を含まない)の日付を生成する。
            for($count = 1; $count < $startDay; $count++){
                $startDaysConsecutive[] += $count;
            }
        }
        return $startDaysConsecutive;
    }

    /**
     * 施設利用者について対象年月の退居日からの日付を全て返す。
     * 取得した退居日を文字列で扱うため、carbonインスタンスは未使用。
     * @param int $facilityUserId
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getEndDates(int $facilityUserId, int $year, int $month): array
    {
        $facilityUser = FacilityUser::where('facility_user_id', $facilityUserId)->first();
        $endDate = $facilityUser->end_date;
        $endYear = substr($endDate, 0, 4);
        $endMonth = substr($endDate, 5, 2);

        $endDaysConsecutive = [];
        if ($endYear == $year && $endMonth == $month){
            $endDay = substr($endDate, 8, 2);
            // 文字列の退居日で十の位が0の場合、0を削除し数値へ変換する。
            if (strpos(substr($endDay, 0, 1), '0') !== false){
                (int)$endDay = substr($endDay, 1);
            }
            // 退居日から(退居日を含まない)の日付を生成する。
            for($count = $endDay + 1; $count <= 31; $count++){
                $endDaysConsecutive[] += $count;
            }
        }
        return $endDaysConsecutive;
    }

    /**
     * 利用者情報をテーブルに新規挿入する
     * @return facility_user_id (int or null)
     */
    public function insert($facilityUserData, $facilityID, $contractorNumber): ? int
    {
        $facilityUserId = null;
        $facilityUserData = FacilityUser::decryptFacilityUserInfo($facilityUserData);
        $facilityUserId = \DB::connection('confidential')->transaction(function() use ($facilityUserData, $facilityID, $contractorNumber){
            return \DB::connection('mysql')->transaction(function() use ($facilityUserData, $facilityID, $contractorNumber){
                $user = new FacilityUser;
                $user->fill($facilityUserData)
                    ->setConnection(null)
                    ->setTable(config('database.connections.confidential.database').'.i_facility_users')
                    ->save();

                $ufInfo = new UserFacilityInformation;
                $ufInfo->fill(['facility_id' => $facilityID,'facility_user_id' => $user->facility_user_id, 'contractor_number' => $contractorNumber])
                    ->setConnection(null)
                    ->setTable(config('database.connections.mysql.database').'.i_user_facility_informations')
                    ->save();
                // LaravelTransactionの無名関数内でリターンするとTransactionの戻り値として扱われる
                return $user->facility_user_id;
            });
        });
        return $facilityUserId;
    }

    /**
     * 利用者情報を返す
     * @param array $param
     * @param array $param['approval'] key: year,month
     * @param array $param['benefit_rate'] key: year,month
     * @param array $param['care_info'] key: year,month
     * @param array $param['clm']
     * @param array $param['facility_user_id_list']
     * @param array $param['independence_information'] key: clm,month,year
     */
    public function getData(array $param) : array
    {
        $clm = $param['clm'];

        $facilityUser = FacilityUser::whereIn('facility_user_id', $param['facility_user_id_list'])
            ->select($clm)
            ->get()
            ->toArray();

        //user_facility_infomationからcontructor_numberをマージ
        $userFacilityInformation = UserFacilityInformation::whereIn('facility_user_id', $param['facility_user_id_list'])
            ->select('facility_user_id', 'contractor_number')
            ->get()
            ->toArray();
        $userFacilityInformation = array_column($userFacilityInformation, null, 'facility_user_id');
        for ($i = 0,$cnt = count($facilityUser); $i < $cnt; $i++) {
            $facilityUserID = $facilityUser[$i]['facility_user_id'];
            $facilityUser[$i]['contractor_number'] = array_key_exists($facilityUserID, $userFacilityInformation) ? $userFacilityInformation[$facilityUserID]['contractor_number'] : null;
        }

        if (array_key_exists('approval', $param)) {
            $approvalYear = $param['approval']['year'];
            $approvalMonth = $param['approval']['month'];
            // 利用者情報に対象年月の承認フラグをマージする
            $serviceResult = ServiceResult::whereIn('facility_user_id', $param['facility_user_id_list'])
                ->date($approvalYear, $approvalMonth)
                ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
                ->select('facility_user_id', 'approval')
                ->get()
                ->toArray();
            $serviceResult = array_column($serviceResult, null, 'facility_user_id');
            for ($i = 0,$cnt = count($facilityUser); $i < $cnt; $i++) {
                $facilityUserID = $facilityUser[$i]['facility_user_id'];
                $facilityUser[$i]['approval'] = array_key_exists($facilityUserID, $serviceResult) ? $serviceResult[$facilityUserID]['approval'] : null; // nullは未承認
            }
        }

        if (array_key_exists('benefit_rate', $param)) {
            $benefitYear = $param['benefit_rate']['year'];
            $benefitMonth = $param['benefit_rate']['month'];
            $userBenefitInfoService = new UserBenefitInformationService();
            $userBenefitInfo = $userBenefitInfoService->getData([
                'clm_list' => ['facility_user_id','effective_start_date','expiry_date','benefit_rate'],
                'facility_user_id_list' => $param['facility_user_id_list'], 'year' => $benefitYear, 'month' => $benefitMonth,
            ]);
            $userBenefitInfo = array_column($userBenefitInfo, null, 'facility_user_id');
            // 利用者情報に給付情報をマージする
            for ($i = 0,$cnt = count($facilityUser); $i < $cnt; $i++) {
                $facilityUserID = $facilityUser[$i]['facility_user_id'];
                if (array_key_exists($facilityUserID, $userBenefitInfo)) {
                    $uBenefitInfo = $userBenefitInfo[$facilityUserID];
                    $facilityUser[$i]['benefit_rate'] = $uBenefitInfo['benefit_rate'];
                } else {
                    $facilityUser[$i]['benefit_rate'] = null;
                }
            }
        }

        if (array_key_exists('care_info', $param)) {
            $paramCareInfo = $param['care_info'];
            $careInfoParam = [
                'clm_list' => $paramCareInfo['clm_list'],
                'facility_user_id_list' => $param['facility_user_id_list'],
                'year' => $paramCareInfo['year'],
                'month' => $paramCareInfo['month'],
            ];
            if (array_key_exists('with', $paramCareInfo)) {
                $careInfoParam['with'] = 'm_care_levels:'.implode(',', $paramCareInfo['with']['care_level']);
            }

            // todo: 別のサービスを呼び出しているため、どこかのタイミングでリファクタする。
            
            // 利用者情報に対象年月の介護度をマージする
            $userCareInfoService = new UserCareInformationService();
            $userCareInfo = $userCareInfoService->getApprovalStatus($careInfoParam);
            $userCareInfo = array_column($userCareInfo, null, 'facility_user_id');
            // 利用者情報に介護度情報をマージする
            for ($i = 0,$cnt = count($facilityUser); $i < $cnt; $i++) {
                $facilityUserID = $facilityUser[$i]['facility_user_id'];
                if (array_key_exists($facilityUserID, $userCareInfo)) {
                    $uCareInfo = $userCareInfo[$facilityUserID];
                    $facilityUser[$i]['care_info'] = $uCareInfo;
                } else {
                    $facilityUser[$i]['care_info'] = null;
                }
            }
        }

        if (array_key_exists('independence_information', $param)) {
            $facilityUser = $this->getJoinIndependenceInformation(
                $param['independence_information']['clm'],
                $facilityUser,
                $param['facility_user_id_list'],
                $param['independence_information']['year'],
                $param['independence_information']['month']
            );
        }

        return $facilityUser;
    }

    /**
     * 施設利用者情報を返す
     * @param array $params key: facility_user_id
     */
    public function getFacilityUser(array $params) : array
    {
        $facilityUser = FacilityUser::where('facility_user_id', $params['facility_user_id'])
            ->first()
            ->toArray();
        return $facilityUser;
    }

    /**
     * 被保険者番号を取得する
     * getFacilityUserをリファクタ(引数変更)後は、左記メソッドをラッパーする予定
     */
    public static function getInsuredNo($facilityUserId)
    {
        return FacilityUser::where('facility_user_id', $facilityUserId)->value('insured_no');
    }

    /**
     * 施設利用者情報と関連するサービス種別を返す
     */
    public function getFacilityUserServiceType($facilityUserId, $servicePlanId)
    {
        $now = Carbon::now()->format('Y-m-d');
        $fu = FacilityUser::select(
            'last_name',
            'first_name',
            'postal_code',
            'location1',
            'location2',
            'birthday'
        )
            ->find($facilityUserId);

        $carePlan = ServicePlan::where('id', $servicePlanId)->first();

        $ufsi = FacilityUser::find($facilityUserId)
            ->userFacilityServiceInformation()
            ->where('use_start', '<=', $carePlan['start_date'])
            ->where('use_end', '>=', $carePlan['start_date'])
            ->first();

        $serviceType = '';
        if ($ufsi->exists()) {
            if ($ufsi->service()->first()->exists()) {
                if ($ufsi->service()->first()->serviceType()->first()->exists()) {
                    $serviceType = $ufsi->service()->first()->serviceType()->first()->service_type_code;
                }
            }
        }

        return ['FacilityUser' => $fu, 'serviceType' => $serviceType ];
    }

    /**
     * 施設利用者情報に承認フラグを結合して返す
     * @param array $facilityUsers
     * @param string $year
     * @param string $month
     * @return array
     */
    public function getJoinApproval(array $facilityUsers, string $year, string $month) : array
    {
        // 施設利用者の承認フラグを取得する
        $facilityUserIds = array_column($facilityUsers, 'facility_user_id');
        $serviceResults = ServiceResult::whereIn('facility_user_id', $facilityUserIds)
            ->date($year, $month)
            ->where('calc_kind', ServiceResult::CALC_KIND_TOTAL)
            ->select('facility_user_id', 'approval')
            ->get()
            ->toArray();

        // 結合する
        // 施設利用者の承認フラグを、施設利用者のIDをキーとして再取得
        $serviceResultIndividual = array_column($serviceResults, null, 'facility_user_id');
        for ($i = 0,$cnt = count($facilityUsers); $i < $cnt; $i++) {
            $facilityUserId = $facilityUsers[$i]['facility_user_id'];
            $facilityUsers[$i]['approval'] = array_key_exists($facilityUserId, $serviceResultIndividual) ? $serviceResultIndividual[$facilityUserId]['approval'] : null;
        }

        return $facilityUsers;
    }

    /**
     * 施設利用者の介護度を結合して返す
     * @return array
     */
    public function getJoinCareInformation($clms, $facilityUsers, $facilityUserIdList, $with, $year, $month) : array
    {
        $careInfoParams = [
            'clm_list' => $clms,
            'facility_user_id_list' => $facilityUserIdList,
            'year' => $year,
            'month' => $month,
            'with' => $with
        ];

        $userCareInformationService = new UserCareInformationService();
        $userCareInformations = $userCareInformationService->get($careInfoParams);

        // 結合する
        // 施設利用者の介護度を、施設利用者のIDをキーとして再取得
        $userCareInfoIndividual = array_column($userCareInformations, null, 'facility_user_id');
        for ($i = 0,$cnt = count($facilityUsers); $i < $cnt; $i++) {
            $facilityUserID = $facilityUsers[$i]['facility_user_id'];
            if (array_key_exists($facilityUserID, $userCareInfoIndividual)) {
                $facilityUsers[$i]['care_info'] = $userCareInfoIndividual[$facilityUserID];
            } else {
                $facilityUsers[$i]['care_info'] = null;
            }
        }

        return $facilityUsers;
    }

    /**
     * 施設利用者の自立度を結合して返す
     * @param array $facilityUser
     * @return array
     */
    public function getJoinIndependenceInformation($clm, $facilityUser, $facilityUserIdList, $year, $month) : array
    {
        $targetDate = new CarbonImmutable("${year}-${month}-1");
        $lastDay = $targetDate->daysInMonth;

        // 施設利用者の自立度を取得
        $userIndependenceInformationService = new UserIndependenceInformationService();
        $userIndependenceInformation = $userIndependenceInformationService->get([
            'clm' => $clm,
            'facility_user_id' => $facilityUserIdList,
            'target_date' => "${year}-${month}-${lastDay}"
        ]);

        // 結合する
        // 施設利用者の自立度を、施設利用者のIDをキーとして再取得
        $independenceIndividual = array_column($userIndependenceInformation, null, 'facility_user_id');
        for ($i = 0,$cnt = count($facilityUser); $i < $cnt; $i++) {
            $fUserId = $facilityUser[$i]['facility_user_id'];
            if (array_key_exists($fUserId, $independenceIndividual)) {
                $facilityUser[$i]['independence_information'] = $independenceIndividual[$fUserId];
            } else {
                $facilityUser[$i]['independence_information'] = null;
            }
        }

        return $facilityUser;
    }

    /**
     * 利用者情報を更新する
     */
    public function update($facilityID, $facilityUserID, $contractorNumber, $facilityUserData) : void
    {
        $facilityUserData = FacilityUser::decryptFacilityUserInfo($facilityUserData);
        \DB::connection('confidential')->transaction(function() use ($facilityID, $facilityUserID, $contractorNumber, $facilityUserData){
            \DB::connection('mysql')->transaction(function() use ($facilityID, $facilityUserID, $contractorNumber, $facilityUserData){

                FacilityUser::findOrFail($facilityUserID)
                    ->fill($facilityUserData)
                    ->setConnection(null)
                    ->setTable(config('database.connections.confidential.database').'.i_facility_users')
                    ->save();

                $userFacilityInfo = UserFacilityInformation::
                    where('facility_id', $facilityID)
                    ->where('facility_user_id', $facilityUserID)
                    ->get();
                foreach ($userFacilityInfo as $key => $value) {
                    $value->fill(['facility_id' => $facilityID, 'contractor_number' => $contractorNumber])
                        ->setConnection(null)
                        ->setTable(config('database.connections.mysql.database').'.i_user_facility_informations')
                        ->save();
                }
            });
        });
    }
}
