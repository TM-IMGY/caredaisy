<?php

namespace App\Http\Controllers;

use App\Models\External;
use App\Models\Facility;
use App\Models\FacilityUser;
use App\Models\ServiceType;

use App\Models\Service;
use App\Models\UserFacilityInformation;

use App\Service\GroupHome\FacilityUserService;
use App\Models\UserFacilityServiceInformation;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ExternalCaptureController extends Controller
{
    public function getFacilityList()
    {
        $result = Facility::select('facility_id', 'facility_number', 'facility_name_kanji')
            ->get()
            ->toArray();

        return response()->json($result);
    }

    public function getFacilityId(Request $request)
    {
        $facilityNum = $request->facilityNum;

        $getFacilityId = Facility::where('facility_number', $facilityNum)
            ->select('facility_id')
            ->get()
            ->toArray();

        $csvData = External::where('facility_id', $getFacilityId)
            ->select('facility_user_id', 'external_user_id')
            ->get()
            ->toArray();

        return $csvData;
    }

    public function getExternalDatas(Request $request)
    {
        $facilityNum = $request->facilityNum;
        $selectFileType = $request->selectFileType;

        $facility = Facility::where('facility_number', $facilityNum)
            ->select('facility_id')
            ->get();

        if (!count($facility) || empty($selectFileType)) {
            return [];
        }

        $facilityId = $facility[0]->facility_id;

        $externalDatas = External::where('facility_id', $facilityId)
            ->select('facility_user_id', 'external_user_id')
            ->get();

        return response()->json($externalDatas);
    }

    public function getExternalRelationCsv(Request $request)
    {
        $facilityNum = $request->facilityNum;

        $callBack = function() use ($facilityNum){
            //ファイル作成する
            $stream = fopen('php://output', 'w');
            //文字コードをShiftJisに変換
            stream_filter_prepend($stream, 'convert.iconv.utf8/cp932//TRANSLIT');

            $facility = Facility::where('facility_number', $facilityNum)
                ->select('facility_id')
                ->get();

            $facilityId = $facility[0]->facility_id;

            $externalDatas = External::where('facility_id', $facilityId)
                ->select('facility_user_id', 'external_user_id')
                ->get();

            //レコードの追加
            foreach ($externalDatas as $externalData) {
                fputcsv($stream, [
                    $externalData->facility_user_id,
                    $externalData->external_user_id,
                ]);
            }
            fclose($stream);
        };

        $fileName = sprintf('%s_利用者ID紐づけ_%s.csv', $facilityNum, date('Ymd'));

        $header = [
            'ContentType' => 'text/csv',
        ];
        return response()->streamDownload($callBack, $fileName, $header);
    }

    public function getServiceTypeList(Request $request)
    {
        $serviceTypeCodeIdList = Service::where('facility_id', $request->facility_id)
        ->select('service_type_code_id')
        ->get()
        ->toArray();

        $serviceTypeCodeIds = array_column($serviceTypeCodeIdList, 'service_type_code_id');

        $serviceTypeAll = ServiceType::whereIn('service_type_code_id', $serviceTypeCodeIds)
            ->select('service_type_code', 'service_type_name')
            ->get()
            ->toArray();

        return response()->json($serviceTypeAll);
    }

    public function csvRegist(Request $request){
        $csvList = $request->csvArr;
        $facilityId = $request->facilityId;
        $selectFileTypeVal = $request->selectFileTypeVal;
        $facilityNum = $request->facilityNum;
        $serviceTypeCode = $request->serviceTypeCode;

        $date = Carbon::now();

        // ServiceIDが知りたい
        $serviceType = ServiceType::where("service_type_code", $serviceTypeCode)
            ->date($date->year, $date->month)
            ->select("service_type_code_id")
            ->first();
        $serviceTypeCodeId = $serviceType->service_type_code_id;

        $service = Service::where("service_type_code_id", $serviceTypeCodeId)
            ->where("facility_id", $facilityId)
            ->first();
        $serviceId = $service->id;

        $facilityUserID = UserFacilityInformation::where('facility_id', $facilityId)
            ->select('facility_user_id')
            ->get()
            ->toArray();

        $facilityUserList = array_column($facilityUserID, 'facility_user_id');

        $userFacilityService = new UserFacilityServiceInformation;

        $array = [];
        $row_num = [];
        $count1 = 0;
        $count2 = 0;
        $newUserCount = 0;
        $connectAcount = [];
        for ($i = 0; $i < count($csvList); $i++) {
            // 誕生日と所属施設が合致するユーザーの抽出
            $result = FacilityUser::whereIn('facility_user_id', $facilityUserList)
                ->where('birthday', $csvList[$i]['birthday'])
                ->select('facility_user_id', 'last_name', 'first_name', 'birthday')
                ->get()
                ->toArray();

            $isExists = false;
            $targetFacilityUserId = "";
            foreach ($result as $row) {
                if ($row["last_name"] == $csvList[$i]['last_name'] && $row["first_name"] == $csvList[$i]['first_name']) {
                    $isExists = true;
                    $targetFacilityUserId = $row["facility_user_id"];
                }
            }

            //caredaisyに登録済みのユーザー
            if ($result && $isExists) {
                $alreadyRegist = External::where('facility_id', $facilityId)
                    ->where('import_file_format_type', $selectFileTypeVal)
                    ->where('facility_user_id', $targetFacilityUserId)
                    ->where('external_user_id', $csvList[$i]['external_user_id'])
                    ->get()
                    ->toArray();

                if (!$alreadyRegist) {
                    // Caredaisy側に登録済で未取り込み
                    $array[$count1]['facility_user_id'] = $targetFacilityUserId;
                    $array[$count1]['external_user_id'] = $csvList[$i]['external_user_id'];
                    $connectAcount[$csvList[$i]['external_user_id']] = $targetFacilityUserId;
                    $count1++;
                } else {
                    // Caredaisy側に登録済で取り込み済
                    $row_num[$count2]['row_num'] = $csvList[$i]['row_num'];
                    $count2++;
                }
            } else {
                $afterOutStatusId = null;
                // 退去日 サービス終了日
                $endDate = $serviceEndDate = null;

                if ($csvList[$i]['gender'] === "男性") {
                    $gender = 1;
                } else {
                    $gender = 2;
                }

                if ($csvList[$i]['end_date']) {
                    //サービス終了日
                    $serviceEndDate = $endDate = $csvList[$i]['end_date'];
                    $afterOutStatusId = 1;
                } else {
                    //サービス終了日
                    $serviceEndDate = "2024/03/31"; // デフォでこれだそうで。
                    // 退去日
                    $endDate = null;
                }

                $externalUserDatas = [
                    'last_name' => $csvList[$i]['last_name'],
                    'first_name' => $csvList[$i]['first_name'],
                    'last_name_kana' => $csvList[$i]['last_name_kana'],
                    'first_name_kana' => $csvList[$i]['first_name_kana'],
                    'insured_no' => '0000000001',
                    'insurer_no' => '000001',
                ];

                $facility = Facility::where('facility_number', $facilityNum)
                    ->select('facility_id')
                    ->get();

                $facilityId = $facility[0]->facility_id;

                $facilityUserService = new FacilityUserService;

                $facilityUserData = [];
                $facilityUserData["last_name"] = $externalUserDatas['last_name'];
                $facilityUserData["first_name"] = $externalUserDatas['first_name'];
                $facilityUserData["last_name_kana"] = $externalUserDatas['last_name_kana'];
                $facilityUserData["first_name_kana"] = $externalUserDatas['first_name_kana'];
                $facilityUserData["gender"] = $gender;
                $facilityUserData["birthday"] = $csvList[$i]['birthday'];
                $facilityUserData["start_date"] = $csvList[$i]['start_date'];
                $facilityUserData["end_date"] = $endDate;
                $facilityUserData["before_in_status_id"] = 1;
                $facilityUserData["afterOutStatusId"] = $afterOutStatusId;
                $facilityUserData["insured_no"] = $externalUserDatas['insured_no'];
                $facilityUserData["insurer_no"] = $externalUserDatas['insurer_no'];
                $facilityUserData["invalid_flag"] = 0;

                // ここもTransactionに含めるべき
                $facilityUserId = $facilityUserService->insert($facilityUserData, $facilityId, null);

                $externalUserId = $csvList[$i]['external_user_id'];
                $startDate = $csvList[$i]['start_date'];

                //DB保存
                \DB::connection('mysql')->transaction(function() use ($facilityId, $selectFileTypeVal, $externalUserId, $serviceId, $startDate, $serviceEndDate, $facilityUserId, $userFacilityService){

                    // サービスとの紐付け
                    $informationData = [
                        "facility_id" => $facilityId,
                        "service_id" => $serviceId,
                        "usage_situation" => 1,
                        "use_start" => $startDate,
                        "use_end" => $serviceEndDate,
                        "facility_user_id" => $facilityUserId
                    ];
                    UserFacilityServiceInformation::create($informationData);

                    External::insert([
                        'facility_id' => $facilityId,
                        'import_file_format_type' => $selectFileTypeVal,
                        'facility_user_id' => $facilityUserId,
                        'external_user_id' => $externalUserId,
                    ]);
                });
                // Caredaisy側に存在しないので、新規ユーザー作成
                $newUserCount++;
            }
        }

        // Caredaisyとの紐付けなしデータがあったら紐付ける
        $alreadyRegist = External::select("external_user_id")
            ->where('facility_id', $facilityId)
            ->where('import_file_format_type', $selectFileTypeVal)
            ->get()->toArray();

        $alreadyRegistExternalUserIdList = array_column($alreadyRegist, "external_user_id");

        $unregistDataCount = 0;
        for ($j = 0; $j < count($array); $j++) {
            if (in_array($array[$j]['external_user_id'], $alreadyRegistExternalUserIdList)) {
                continue;
            }
            External::insert([
                'facility_id' => $facilityId,
                'import_file_format_type' => $selectFileTypeVal,
                'facility_user_id' => $array[$j]['facility_user_id'],
                'external_user_id' => $array[$j]['external_user_id'],
            ]);
            $unregistDataCount++;
        }
        $returnList = [];
        $returnList["row_num"] = array_column($row_num, 'row_num');
        $returnList["capture_count"] = $unregistDataCount;
        $returnList["captured_count"] = $count2;
        $returnList["new_record"] = $newUserCount;
        $returnList["no_already_regist"] = $count1;
        $returnList["connect_account"] = $connectAcount;

        return response()->json($returnList);
    }
}
