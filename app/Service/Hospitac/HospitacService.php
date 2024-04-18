<?php

namespace App\Service\Hospitac;

use App\Lib\Common\Consts;
use App\Models\Hospitac\HospitacFileLinkage;
use App\Models\Hospitac\PatientMedicalCare;
use App\Models\Hospitac\HospitacLinkageSetting;
use App\Models\UserFacilityInformation;
use App\Models\ServiceResult;
use App\Models\InjuriesSickness;
use App\Models\InjuriesSicknessDetail;
use App\Models\InjuriesSicknessRelation;
use App\Models\FacilityUserBurdenLimit;
use App\Models\Service;
use App\Models\ServiceCode;
use App\Models\SpecialMedicalCode;
use App\Models\UserFacilityServiceInformation;
use App\Models\UninsuredItemHistory;
use App\Models\UninsuredRequest;
use App\Models\UninsuredRequestDetail;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Log;

/**
 * ホスピタック
 */
class HospitacService
{
    private $decodeFile;
    private $utf8File;
    // ファイルから抜き出したデータのリスト
    private $commonPart = []; // 共通部
    private $patientInformation = []; // 患者情報
    private $sicknessInfos = []; // 病名情報
    private $medicalCareInfos = []; // 診療情報
    private $fileName = null;

    // 職種リスト
    private const OCCUPATION_LIST = ['PT', 'OT', 'ST'];

    // 患者診療情報 共通部
    private const COMMON_PART_ROW = [
        'type' => HospitacFileLinkage::TYPE_BYTE,
        'processing_category' => HospitacFileLinkage::PROCESSING_CATEGORY_BYTE,
        'file_created_dt' => HospitacFileLinkage::FILE_CREATED_DT_BYTE,
        'file_length' => HospitacFileLinkage::FILE_LENGTH_BYTE,
        'medical_institution_code' => HospitacFileLinkage::MEDICAL_INSTITUTION_CODE_BYTE,
    ];
    // 患者診療情報 患者診療情報行
    private const PATIENT_MEDICAL_INFORMATION_ROW = [
        'patient_number' => HospitacFileLinkage::PATIENT_NUMBER_BYTE,
        'medical_care_date' => PatientMedicalCare::MEDICAL_CARE_DATE_BYTE,
        'medical_care_information_count' => PatientMedicalCare::MEDICAL_CARE_INFO_COUNT_BYTE,
    ];
    // 患者診療情報 診療情報行
    private const MEDICAL_CARE_INFORMATION_ROW = [
        'order_number' => PatientMedicalCare::ORDER_NUMBER_BYTE,
        'data_type' => PatientMedicalCare::DATA_TYPE_BYTE,
        'receipt_code' => PatientMedicalCare::RECEIPT_CODE_BYTE,
        'item_name' => PatientMedicalCare::ITEM_NAME_BYTE,
        'service_code' => PatientMedicalCare::SERVICE_CODE_BYTE,
        'uninsured_cost' => PatientMedicalCare::UNINSURED_COST_BYTE,
        'quantity' => PatientMedicalCare::QUANTITY_BYTE,
        'count' => PatientMedicalCare::COUNT_BYTE,
        'special_diet_count' => PatientMedicalCare::SPECIAL_DIET_COUNT_BYTE,
    ];
    // 患者診療情報 病名情報行
    private const SICKNESS_INFORMATION_ROW = [
        'occupation' => PatientMedicalCare::OCCUPATION_BYTE,
        'service_code' => PatientMedicalCare::MEDICAL_ROW_SERVICE_CODE_BYTE,
        'rehabilitation_sickness_name' => PatientMedicalCare::REHABILITATION_SICKNESS_NAME_BYTE,
    ];

    /**
     * 取得したファイルデータの一次登録
     * @param array $file
     */
    public function fileUpload($file)
    {
        $fileUpload = new HospitacFileLinkage;
        // statusとfile_data以外はダミーデータを入れる
        $fileUpload->file_name = 'errorファイル';
        $fileUpload->type = 'XX';
        $fileUpload->processing_category = 'XX';
        $fileUpload->file_created_dt = now();
        $fileUpload->medical_institution_code = Consts::INVALID;
        $fileUpload->patient_number = Consts::INVALID;
        $fileUpload->status = HospitacFileLinkage::FILE_IMPORT_ERROR;
        $fileUpload->file_data = $file['file_data'];

        DB::beginTransaction();
        try {
            $fileUpload->save();
            DB::commit();
            // 登録したファイルのIDをreturn
            return $fileUpload->id;

        } catch (Exception $e) {
            DB::rollback();
            throw new Exception('サーバ内で予期せぬエラーが発生しました。');
        }

    }

    /**
     * ファイル種別毎に各テーブルにデータを処理する
     * @param array $fileData
     * @param int $id
     */
    public function fileImport($fileData, $id)
    {
        self::decodeAndEncodeFile($fileData);
        // ファイル名の先頭2文字を切り出し
        $fileType = mb_substr($this->utf8File, 0 , 2);
        // ファイル種別毎の取得
        if ($fileType == HospitacFileLinkage::TYPE_OF_PATIENT_BASIC) {
            # code...
        } elseif ($fileType == HospitacFileLinkage::TYPE_OF_PATIENT_MOVE) {
            # code...
        } elseif ($fileType == HospitacFileLinkage::TYPE_OF_PATIENT_MEDICAL_CARE) {
            self::importTrTypeFile($id);
        }
    }

    /**
     * 種別が患者診療情報のファイルデータをDBに登録する
     * @param int $id
     */
    private function importTrTypeFile($id)
    {
        self::createTrInformationLists();
        self::createFileName(
            HospitacFileLinkage::TYPE_OF_PATIENT_MEDICAL_CARE,
            $this->commonPart['file_created_dt'],
            $this->patientInformation['patient_number'],
            $this->commonPart['processing_category']
        );

        self::createSicknessNamesAndOccupationsForRegistration();
        DB::beginTransaction();
        try {
            self::updateHospitacFileLinkage($id);
            self::insertPatientMedicalCare($id);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw new Exception('サーバ内で予期せぬエラーが発生しました。');
        }
    }

    /**
     * HOSPITAC連携ファイル情報テーブルを更新する
     * @param int $id
     */
    private function updateHospitacFileLinkage($id)
    {
        $data = [
            'file_name' => $this->fileName,
            'type' => $this->commonPart['type'],
            'processing_category' => $this->commonPart['processing_category'],
            'file_created_dt' => $this->commonPart['file_created_dt'],
            'medical_institution_code' => $this->commonPart['medical_institution_code'],
            'patient_number' => $this->patientInformation['patient_number'],
            'status' => HospitacFileLinkage::NOT_CAPTURED
        ];
        HospitacFileLinkage::where('id', $id)->update($data);
    }

    /**
     * 患者診療情報テーブルに登録する
     * @param int $id
     */
    private function insertPatientMedicalCare($id)
    {
        $dt = new Carbon($this->patientInformation['medical_care_date']);
        foreach ($this->medicalCareInfos as $key => $value) {
            $data = [
                'hospitac_file_coordination_id' => $id,
                'medical_care_date' => $dt,
                'order_number' => $value['order_number'],
                'data_type' => $value['data_type'],
                'receipt_code' => $value['receipt_code'],
                'item_name' => $value['item_name'],
                'service_code' => $value['service_code'],
                'uninsured_cost' => $value['uninsured_cost'],
                'quantity' => $value['quantity'],
                'count' => $value['count'],
                'special_diet_count' => $value['special_diet_count'],
                'occupation' => $value['occupations'],
                'rehabilitation_sickness_name' => $value['rehabilitation_sickness_names']
            ];
            PatientMedicalCare::create($data);
        }
    }

    /**
     * 行の指定の位置から指定の文字数を切り出す
     * @param string $row
     * @param array $range = [from, to]
     */
    private function separate($row, $range)
    {
        $ret = null;
        for($i=$range[0]; $i<=$range[1]; $i++){
            $ret .= $row[$i];
        }
        return self::formatAcquiredData($ret);
    }

    /**
     * 各情報を切り出したリストを作成する
     * @param array $arr
     * @param string $row
     * @return array
     */
    private function createInformationList($arr, $row)
    {
        if (empty($row)) {
            return;
        }
        foreach ($arr as $key => $fromTo) {
            $cutOutInformation = self::separate($row, $fromTo);
            // keyがservice_codeの場合は整形する
            if ($key == 'service_code' && !empty($cutOutInformation)) {
                $cutOutInformation = self::formatServiceCode($cutOutInformation);
            }
            $list[$key] = $cutOutInformation !== "" ? $cutOutInformation : null;
        }
        return $list;
    }

    /**
     * 切り出した文字列を整形する
     * @param string $str
     * @return string
     */
    private function formatAcquiredData($str)
    {
        // 先頭・末尾の空白を削除及びutf-8に変換
        $conversionData = self::triming(mb_convert_encoding($str,"utf-8","sjis"));
        // 文字間の半角スペースは全角スペースに変換する
        $formatData = mb_convert_kana($conversionData, 'S');
        return $formatData;
    }

    /**
     * サービスコードを整形する
     * @param string $str
     * @return string
     */
    private function formatServiceCode($str)
    {
        $formatedData = mb_substr(str_replace('@', '000', $str), -4);
        return $formatedData;
    }

    /**
     * 先頭・末尾の空白を削除
     * @param string $str
     * @return string
     */
    private function triming($str)
    {
        if (is_null($str)) {
            return null;
        }
        return preg_replace("/(^\s+)|(\s+$)/u", "", $str);
    }

    /**
     * ファイルデータを1行ずつ取り出してデータリストを作成する
     * 患者基本情報・患者移動情報・患者診療情報それぞれファイルの内容(構成)が異なるので
     * リスト作成のメソッドはそれぞれ作ることを想定
     */
    private function createTrInformationLists()
    {
        $rows = explode("\n", $this->decodeFile);
        // データリストを作成する
        foreach ($rows as $key => $row) {
            // 病名情報数の行と空の行はスキップ
            if (mb_strlen($row) == 2 || !mb_strlen($row)) {
                continue;
            }
            // 病名情報
            if (in_array(mb_substr($row, 0, 2), self::OCCUPATION_LIST)) {
                $this->sicknessInfos[] = $this->createInformationList(self::SICKNESS_INFORMATION_ROW ,$row);
                continue;
            }
            //　共通部 1行目
            if ($key === 0) {
                $this->commonPart = $this->createInformationList(self::COMMON_PART_ROW ,$row);
                continue;
            }
            // 患者診療情報行 2行目
            if ($key === 1) {
                $this->patientInformation = $this->createInformationList(self::PATIENT_MEDICAL_INFORMATION_ROW ,$row);
                continue;
            }
            // 診療情報
            $this->medicalCareInfos[] = $this->createInformationList(self::MEDICAL_CARE_INFORMATION_ROW ,$row);
        }
    }

    /**
     * ファイル名を作成する
     * @param string $type 種別
     * @param string $createdDt ファイル作成日時
     * @param string $patientNum 患者番号
     * @param string $category 処理区分
     */
    private function createFileName($type, $createdDt, $patientNum, $category)
    {
        $this->fileName =
        $type
        .'_'
        .$createdDt
        .'_'
        .$patientNum
        .'_'
        .$category
        .'.txt';
    }

    /**
     * DB登録用のリハ病名と職種を作成する
     */
    private function createSicknessNamesAndOccupationsForRegistration()
    {
        // サービスコード毎にリハ病名を作成する
        foreach ($this->medicalCareInfos as $key => $careInfo) {
            $rehabilitationSicknessNames = null;
            $rehabilitationOccupationes = null;
            $mciServiceCode = $careInfo['service_code'];

            foreach ($this->sicknessInfos as $sicknessKey => $sicknessInfo) {
                if ($mciServiceCode != $sicknessInfo['service_code']) {
                    continue;
                }
                $rehabilitationSicknessNames .= $sicknessInfo['rehabilitation_sickness_name'].'　';
                $rehabilitationOccupationes .= $sicknessInfo['occupation'].'　';
            }
            // 末尾の全角スペースは削除する
            $this->medicalCareInfos[$key]['rehabilitation_sickness_names'] = self::triming($rehabilitationSicknessNames);
            $this->medicalCareInfos[$key]['occupations'] = self::triming($rehabilitationOccupationes);

            // データタイプが診療行為でリハ病名がひとつもなかったらエラー
            if ($this->medicalCareInfos[$key]['data_type'] == PatientMedicalCare::DATA_TYPE_MEDICAL_PRACTICE && is_null($this->medicalCareInfos[$key]['rehabilitation_sickness_names'])) {
                Log::info("{$this->medicalCareInfos[$key]['item_name']} に紐づくリハ病名が見つかりません。");
                throw new Exception('サーバ内で予期せぬエラーが発生しました。');
            }
        }
    }

    /**
     * ファイルデータをbase64でデコードする
     * utf-8にencodeしたファイルも合わせて作成する
     * @param string $fileData
     */
    private function decodeAndEncodeFile($fileData)
    {
        // base64でデコードする
        $this->decodeFile = base64_decode($fileData['file_data']);
        // utf-8にエンコード
        $this->utf8File = mb_convert_encoding($this->decodeFile,"utf-8","sjis");
    }

    /**
     * 未取込の患者診療情報を1件取得する
     *
     * @return HospitacFileLinkage|null
     */
    public function getUnconvertedMedicalCare(Carbon $startTime): ?HospitacFileLinkage
    {
        return HospitacFileLinkage::where('type', 'TR')
            ->where('status', 1)
            ->where('created_at', '<=', $startTime)
            ->orderBy('id', 'asc')
            ->first();
    }

    /**
     * 医療機関コード・患者番号・診療日が同一で、ファイル作成日が未来のコンバート済みファイルが存在するか判定する
     *
     * @param $institutionCode 医療機関コード
     * @param $patientNumber 患者番号
     * @param Carbon $fileCreatedDt 連携ファイル作成日
     * @param Carbon $medicalCareDate 診療日
     * @return boolean
     */
    public function existConvertedSameMedicalCareDate($institutionCode, $patientNumber, Carbon $fileCreatedDt, Carbon $medicalCareDate): bool
    {
        return HospitacFileLinkage::where('type', 'TR')
            ->where('status', 2)
            ->where('medical_institution_code', $institutionCode)
            ->where('patient_number', $patientNumber)
            ->where('file_created_dt', '>', $fileCreatedDt)
            ->whereHas('patientMedicalCares', function (Builder $query) use ($medicalCareDate) {
                $query->where('medical_care_date', $medicalCareDate);
            })
            ->exists();
    }

    /**
     * サービス実績を取得する
     * (14)
     *
     * @param Carbon $medicalCareDate
     * @param string $serviceCode
     * @param int $facilityUserId
     * @return ServiceResult|null
     */
    public function getServiceResult(Carbon $medicalCareDate, string $serviceCode, int $facilityUserId): ?ServiceResult
    {
        return ServiceResult::where('target_date', $medicalCareDate->copy()->startOfMonth())
            ->where('facility_user_id', $facilityUserId)
            ->whereHas('specialMedicalCode', function (Builder $query) use ($medicalCareDate, $serviceCode) {
                $query->where('service_type_code', 80)
                ->where('identification_num', $serviceCode)
                ->where('start_date', '<=', $medicalCareDate)
                ->where('end_date', '>=', $medicalCareDate);
            })
            ->with(['specialMedicalCode' => function ($query) use ($medicalCareDate, $serviceCode) {
                $query->where('service_type_code', 80)
                ->where('identification_num', $serviceCode)
                ->where('start_date', '<=', $medicalCareDate)
                ->where('end_date', '>=', $medicalCareDate);
            }])
            ->first();
    }

    /**
     * カテゴリーが"変更"で取り込み済みのファイルが存在するか確認する
     * existConvertedSameMedicalCareDate とは '>=' が違うだけ
     * @param array $params
     * @return bool
     */
    public function existChangeConvertedSameMedicalCareDate(array $params): bool
    {
        return HospitacFileLinkage::where('type', 'TR')
            ->where('status', 2)
            ->where('medical_institution_code', $params['institutionCode'])
            ->where('patient_number', $params['patientNumber'])
            ->where('file_created_dt', '>=', $params['fileCreatedDt'])
            ->whereHas('patientMedicalCares', function (Builder $query) use ($params) {
                $query->where('medical_care_date', $params['medicalCareDate']);
            })
            ->exists();
    }

    /**
     * 前回取り込んだ診療情報を取得する
     * @param array $params
     * @return Collection
     */
    public function getLastTimePatientMedicalCare(array $params): Collection
    {
        return PatientMedicalCare::where('medical_care_date', $params['medicalCareDate'])
            ->whereHas('file', function (Builder $query) use ($params) {
                $query->where('type', 'TR')
                    ->where('status', 2)
                    ->where('medical_institution_code', $params['institutionCode'])
                    ->where('patient_number', $params['patientNumber']);
            })
            ->orderBy('id', 'DESC')
            ->get();
    }

    /**
     * memo (66) ~ (69) (79) ~ (82) (109) ~ (112)
     * 今回取り込む診療情報を登録する必要があるかチェックする
     * @param PatientMedicalCare $medicalCare
     * @param Collection $lastTimeMedicalPractices
     * @return bool
     */
    public function checkRegistrationRequired(
        PatientMedicalCare $medicalCare,
        Collection $lastTimeMedicalPractices
    ): bool {
        // 前回取り込んだ診療情報が存在するか
        $empty = $lastTimeMedicalPractices->isEmpty();
        if ($empty) {
            // 存在しなかったら追加する
            return true;
        }

        // 前回取り込んだ診療情報に今回取り込むオーダー番号と一致するデータが存在するかチェックする
        $orderNumbers = $lastTimeMedicalPractices->pluck('order_number');
        $containOrderNum = $orderNumbers->containsStrict($medicalCare->order_number);
        if (!$containOrderNum) {
            // 存在しなかったら追加する
            return true;
        }

        // 前回取り込んだ診療情報からオーダー番号が一致するデータを取得する
        $lastTimeRecord = $lastTimeMedicalPractices->first(function ($item, $key) use ($medicalCare) {
            return $item->order_number === $medicalCare->order_number;
        });
        // 前回取り込んだ診療情報と今回取り込む診療情報で各項目に差分があるかチェックする
        $diff = collect($medicalCare)->diffAssoc($lastTimeRecord);
        $diffCheckColumns = collect(PatientMedicalCare::DIFF_CHECK_COLUMNS);
        $hasDifference = false;
        // 見つかった差分に差分チェックの対象項目が含まれているかチェックする
        $hasDifference = $diffCheckColumns->containsStrict(function ($value, $key) use ($diff) {
            return $diff->has($value) === true;
        });
        if ($hasDifference) {
            // 差分があったら追加する
            return true;
        }

        return false;
    }

    /**
     * 日割対象日・サービス回数・回数/日数を更新する
     * @param string $newDateDailyRate
     * @param int $id
     */
    public function updateServiceResultDateDailyRateAndServiceCount(string $newDateDailyRate, int $id): void
    {
        // 日割対象日を合計してサービス回数を算出する
        preg_match_all("([0-9])", $newDateDailyRate, $DailyRateArr);
        $serviceCount = array_sum($DailyRateArr[0]);
        ServiceResult::where('service_result_id', $id)
            ->update([
                'date_daily_rate' => $newDateDailyRate,
                'service_count' => $serviceCount,
                'service_count_date' => $serviceCount
            ]);
    }

    /**
     * 実績を削除する
     * @param int $id
     */
    public function deleteServiceResult(ServiceResult $dataList): void
    {
        $dataList->delete();
    }

    /**
     * 対象日の実績を0にした日割対象日を作成する
     * @param ServiceResult $dataList
     * @param string $medicalCareDate
     * @return string
     */
    public function formatDateDailyRateToZero(ServiceResult $dataList, string $medicalCareDate): string
    {
        // 日割対象日を抜き出す
        $dateDailyRate = $dataList->date_daily_rate;
        // 削除対象日を抜き出す
        $formatDate = new Carbon($medicalCareDate);
        $targetDate = $formatDate->day;
        // 削除対象日の実績を0にする
        $afterChangeDateDailyRate = substr_replace($dateDailyRate, 0, $targetDate -1 , 1);
        return $afterChangeDateDailyRate;
    }

    /**
     * 現在有効な利用者負担額レコードを取得
     * @param PatientMedicalCare $food
     * @param int $facilityUserId
     * @return Collection
     */
    public function getFacilityUserBurdenLimit(PatientMedicalCare $food, int $facilityUserId): Collection
    {
        return FacilityUserBurdenLimit::where('facility_user_id', $facilityUserId)
            ->where('start_date', '<=', $food->medical_care_date)
            ->where('end_date', '>=', $food->medical_care_date)
            ->get();
    }

    /**
     * 保険外請求の品目を取得する
     * @param Carbon $medicalCareDate
     * @param int $facilityUserId
     * @param string $itemName
     * @return UninsuredRequest|null
     */
    public function getUninsuredRequestItem(Carbon $medicalCareDate, int $facilityUserId, string $itemName): ?UninsuredRequest
    {
        return UninsuredRequest::where('month', $medicalCareDate->copy()->startOfMonth())
            ->where('facility_user_id', $facilityUserId)
            ->where('name', $itemName)
            ->first();
    }

    /**
     * 保険外費用の品目に紐づく保険外請求品目を取得する
     * @param Carbon $medicalCareDate
     * @param int $facilityUserId
     * @param string $itemName
     * @return UninsuredRequest|null
     */
    public function getUninsuredItem(Carbon $medicalCareDate, int $facilityUserId, string $itemName): ?UninsuredRequest
    {
        return UninsuredRequest::where('facility_user_id', $facilityUserId)
            ->where('month', $medicalCareDate->copy()->startOfMonth())
            ->whereHas('uninsuredItemHistory', function (Builder $query) use ($itemName) {
                $query->where('item', $itemName);
            })
            ->first();
    }

    /**
     * 保険外請求に登録されている品目を削除する
     * @param Carbon $medicalCareDate
     * @param UninsuredRequest $uninsuredItem
     */
    public function deleteUninsuredRequestRerationOfRecord(Carbon $medicalCareDate, UninsuredRequest $uninsuredItem)
    {
        // 対象日のレコードを削除する
        UninsuredRequestDetail::where('uninsured_request_id', $uninsuredItem->id)
            ->where('date_of_use', $medicalCareDate)
            ->delete();

        // 該当品目の実績が立っているレコードを取得する
        $datalist = UninsuredRequestDetail::where('uninsured_request_id', $uninsuredItem->id)
            ->where('quantity', '>=', 1)
            ->get();

        // 他日付に実績が立っていなければ該当品目を削除する
        if (empty($datalist->toArray())) {
            $uninsuredItem->delete();
        }
    }

    /**
     * uninsured_request_idと利用日に該当する保険外請求詳細を削除する
     * (40), (50)
     *
     * @param integer $uninsuredRequestId
     * @param Carbon $dateOfUse
     * @return void
     */
    public function deleteUninsuredRequestDetail(int $uninsuredRequestId, Carbon $dateOfUse): void
    {
        UninsuredRequestDetail::where('uninsured_request_id', $uninsuredRequestId)
            ->where('date_of_use', $dateOfUse)
            ->delete();
    }

    /**
     * (86) falseルート
     * 保険外請求とサービス実績を削除する
     * @param PatientMedicalCare $meal
     * @param int $facilityUserId
     */
    public function deleteServiceResultAndUninsuredRequestOfMeal(PatientMedicalCare $meal, int $facilityUserId)
    {
        $serviceResult = self::getNursingCareHospitalFoodExpenses($meal->medical_care_date, $facilityUserId);
        if (!is_null($serviceResult)) {
            $afterChangeDateDailyRate = self::formatDateDailyRateToZero($serviceResult, $meal->medical_care_date);
            self::deleteOrUpdateToServiceResult($afterChangeDateDailyRate, $serviceResult);
        }

        // (102)~
        $item = self::getUninsuredRequestItem($meal->medical_care_date, $facilityUserId, '食費');
        if (!is_null($item)) {
            self::deleteUninsuredRequestRerationOfRecord($meal->medical_care_date, $item);
        }
    }

    /**
     * 特定入所者介護サービス実績の「介護医療院食費」を取得する
     * @param Carbon $medicalCareDate
     * @param int $facilityUserId
     * @return ServiceResult|null
     */
    public function getNursingCareHospitalFoodExpenses(Carbon $medicalCareDate, int $facilityUserId): ?ServiceResult
    {
        // 「介護医療院食費」を取得
        $foodExpenses = ServiceCode::where('service_type_code', 59)
            ->where('service_item_code', 5511)
            ->first();

        return ServiceResult::where('target_date', $medicalCareDate->copy()->startOfMonth())
            ->where('service_item_code_id', $foodExpenses->service_item_code_id)
            ->where('facility_user_id', $facilityUserId)
            ->first();
    }

    /**
     * 実績情報を更新または削除
     * @param string $newDateDailyRate
     * @param ServiceResult $serviceResult
     */
    public function deleteOrUpdateToServiceResult(string $newDateDailyRate, ServiceResult $serviceResult)
    {
        // 他の日付にフラグが立っていたらupdate
        if (preg_match('/[1-9]/', $newDateDailyRate)) {
            self::updateServiceResultDateDailyRateAndServiceCount($newDateDailyRate, $serviceResult->service_result_id);
        } else {
            // 他の日付にフラグが立っていなかったらdelete
            self::deleteServiceResult($serviceResult);
        }
    }

    /**
     * 現在適用中の傷病情報を取得する
     * (18)
     *
     * @param integer $facilityUserId
     * @param Carbon $medicalCareDate
     * @return InjuriesSickness|null
     */
    public function getApplyingInjuriesSickness(int $facilityUserId, Carbon $medicalCareDate): ?InjuriesSickness
    {
        return InjuriesSickness::where('facility_user_id', $facilityUserId)
            ->where('start_date', '<=', $medicalCareDate)
            ->where('end_date', '>=', $medicalCareDate)
            ->whereHas('injuriesSicknessDetails.injuriesSicknessRelations.specialMedicalCode', function (Builder $query) use ($medicalCareDate) {
                $query->where('service_type_code', 80)
                    ->where('start_date', '<=', $medicalCareDate)
                    ->where('end_date', '>=', $medicalCareDate);
            })
            ->with(['injuriesSicknessDetails.injuriesSicknessRelations.specialMedicalCode' => function ($query) use ($medicalCareDate) {
                $query->where('service_type_code', 80)
                    ->where('start_date', '<=', $medicalCareDate)
                    ->where('end_date', '>=', $medicalCareDate);
            }])
            ->first();
    }

    /**
     * 特別診療と食費の両方に共通するデータをセットしたモデルを作成する
     *
     * @param PatientMedicalCare $medicalPractice
     * @param HospitacFileLinkage $unconvertedFile
     * @param integer $count
     * @return ServiceResult
     */
    private function createBaseServiceResult(PatientMedicalCare $medicalPractice, HospitacFileLinkage $unconvertedFile, int $count = 1): ServiceResult
    {
        $serviceResult = new ServiceResult();

        $dateDailyDate = substr_replace('0000000000000000000000000000000', $count, $medicalPractice->medical_care_date->day - 1, 1);
        preg_match_all("([0-9])", $dateDailyDate, $serviceTotal);
        $serviceCount = array_sum($serviceTotal[0]);

        return $serviceResult->fill([
            'facility_user_id' => $unconvertedFile->userFacilityInfos->facility_user_id,
            'facility_id' => $unconvertedFile->userFacilityInfos->facility_id,
            'document_create_date' => today(),
            'service_use_date' => today(),
            'date_daily_rate' => $dateDailyDate,
            'service_start_time' => 9999,
            'service_end_time' => 9999,
            'service_count' => $serviceCount,
            'facility_number' => $unconvertedFile->userFacilityInfos->facility->facility_number,
            'facility_name_kanji' => $unconvertedFile->userFacilityInfos->facility->facility_name_kanji,
            'service_count_date' => $serviceCount,
            'classification_support_limit_in_range' => 0,
            'unit_price' => 0,
            'total_cost' => 0,
            'benefit_rate' => 0,
            'insurance_benefit' => 0,
            'part_payment' => 0,
            'calc_kind' => 1,
            'approval' => 0,
        ]);
    }

    /**
     * 特定入居者サービス(食費)の実績情報をインサートする
     *
     * @param PatientMedicalCare $medicalPractice
     * @param HospitacFileLinkage $unconvertedFile
     * @param FacilityUserBurdenLimit $burdenLimit
     * @return void
     */
    public function createServiceResult(PatientMedicalCare $medicalPractice, HospitacFileLinkage $unconvertedFile, FacilityUserBurdenLimit $burdenLimit): void
    {
        $serviceResult = $this->createBaseServiceResult($medicalPractice, $unconvertedFile);

        $serviceResult->target_date = $medicalPractice->medical_care_date->copy()->startOfMonth();
        $serviceResult->result_kind = 3;

        // サービスコードマスタから必要情報取得しモデルにセット
        $serviceCode = ServiceCode::where('service_type_code', 59)
            ->where('service_item_code', 5511)
            ->where('service_start_date', '<=', $medicalPractice->medical_care_date)
            ->where('service_end_date', '>=', $medicalPractice->medical_care_date)
            ->first();
        $serviceResult->service_item_code_id = $serviceCode->service_item_code_id;
        $serviceResult->unit_number = $serviceCode->service_synthetic_unit;

        $serviceResult->burden_limit = $burdenLimit->food_expenses_burden_limit;

        $serviceResult->save();
    }

    /**
     * 特別診療の実績情報をインサートする
     *
     * @param PatientMedicalCare $medicalPractice
     * @param HospitacFileLinkage $unconvertedFile
     * @return void
     */
    public function createSpecialMedicalServiceResult(PatientMedicalCare $medicalPractice, HospitacFileLinkage $unconvertedFile): void
    {
        $serviceResult = $this->createBaseServiceResult($medicalPractice, $unconvertedFile, $medicalPractice->count);

        $serviceResult->target_date = $medicalPractice->medical_care_date->copy()->startOfMonth();
        $serviceResult->result_kind = 2;

        // 特別診療情報から必要情報取得しモデルにセット
        $specialMedicalCode = SpecialMedicalCode::where('service_type_code', 80)
            ->where('identification_num', $medicalPractice->service_code)
            ->where('start_date', '<=', $medicalPractice->medical_care_date)
            ->where('end_date', '>=', $medicalPractice->medical_care_date)
            ->first();
        $serviceResult->special_medical_code_id = $specialMedicalCode->id;
        $serviceResult->unit_number = $specialMedicalCode->unit;

        // サービスコードマスタから必要情報取得しモデルにセット
        $serviceCode = ServiceCode::where('service_type_code', 55)
            ->where('service_item_code', 9950)
            ->where('service_start_date', '<=', $medicalPractice->medical_care_date)
            ->where('service_end_date', '>=', $medicalPractice->medical_care_date)
            ->first();
        $serviceResult->service_item_code_id = $serviceCode->service_item_code_id;

        $serviceResult->save();
    }

    /**
     * 実績情報の特別診療費と食費を更新する
     *
     * @param ServiceResult $serviceResult 更新対象の実績情報モデル
     * @param PatientMedicalCare $medicalPractice
     * @param integer $count
     * @return void
     */
    public function updateServiceResult(ServiceResult $serviceResult , PatientMedicalCare $medicalPractice, int $count = 1): void
    {
        $dateDailyDate = substr_replace($serviceResult->date_daily_rate, $count, $medicalPractice->medical_care_date->day - 1, 1);
        preg_match_all("([0-9])", $dateDailyDate, $serviceTotal);
        $serviceCount = array_sum($serviceTotal[0]);

        $serviceResult->date_daily_rate = $dateDailyDate;
        $serviceResult->service_count = $serviceCount;
        $serviceResult->service_count_date = $serviceCount;

        $serviceResult->save();
    }

    /**
     * 診療日当月開始の傷病名情報を作成する
     *
     * @param integer $facilityUserId
     * @param PatientMedicalCare $medicalPractice
     * @return void
     */
    public function createInjuriesSickness(int $facilityUserId, PatientMedicalCare $medicalPractice): void
    {
        $injuriesSickness = new InjuriesSickness();
        $injuriesSickness->fill([
            'facility_user_id' => $facilityUserId,
            'start_date' => $medicalPractice->medical_care_date->startOfMonth(),
            'end_date' => $medicalPractice->medical_care_date->endOfMonth(),
        ])->save();

        $detail = new InjuriesSicknessDetail();
        $detail->fill([
            'injuries_sicknesses_id' => $injuriesSickness->id,
            'group' => 1,
            'name' => $medicalPractice->rehabilitation_sickness_name,
        ])->save();

        $specialMedicalCode = SpecialMedicalCode::where('service_type_code', 80)
            ->where('identification_num', $medicalPractice->service_code)
            ->where('start_date', '<=', $medicalPractice->medical_care_date)
            ->where('end_date', '>=', $medicalPractice->medical_care_date)
            ->first();

        $relations = new InjuriesSicknessRelation();
        $relations->fill([
            'injuries_sicknesses_detail_id' => $detail->id,
            'special_medical_code_id' => $specialMedicalCode->id,
            'selected_position' => 1,
        ])->save();
    }

    /**
     * 傷病名情報の適用終了月を前月で閉じる
     * (26)
     *
     * @param InjuriesSickness $injuriesSickness
     * @param Carbon $medicalCareDate
     * @return void
     */
    public function updateInjuriesSicknessEndDate(InjuriesSickness $injuriesSickness, Carbon $medicalCareDate): void
    {
        $injuriesSickness->end_date = $medicalCareDate->subMonthNoOverflow()->endOfMonth();
        $injuriesSickness->save();
    }

    /**
     * 傷病名関連情報を登録する
     * (24)
     *
     * @param InjuriesSicknessDetail $detail
     * @param PatientMedicalCare $medicalPractice
     * @return void
     */
    public function createInjuriesSicknessRelation(InjuriesSicknessDetail $detail, PatientMedicalCare $medicalPractice): void
    {
        $specialMedicalCode = SpecialMedicalCode::where('service_type_code', 80)
            ->where('identification_num', $medicalPractice->service_code)
            ->where('start_date', '<=', $medicalPractice->medical_care_date)
            ->where('end_date', '>=', $medicalPractice->medical_care_date)
            ->first();

        // 既存レコードのselected_positionの連番
        $selectedPosition = $detail->injuriesSicknessRelations->max('selected_position') + 1;

        $relations = new InjuriesSicknessRelation();
        $relations->fill([
            'injuries_sicknesses_detail_id' => $detail->id,
            'special_medical_code_id' => $specialMedicalCode->id,
            'selected_position' => $selectedPosition,
        ])->save();
    }

    /**
     * 傷病名詳細と関連情報を登録する
     * (25)
     *
     * @param InjuriesSickness $injuriesSickness
     * @param PatientMedicalCare $medicalPractice
     * @return void
     */
    public function createInjuriesSicknessDetailAndRelation(InjuriesSickness $injuriesSickness, PatientMedicalCare $medicalPractice): void
    {
        $detail = new InjuriesSicknessDetail();
        $detail->fill([
            'injuries_sicknesses_id' => $injuriesSickness->id,
            // 既存レコードのgroupの連番
            'group' => $injuriesSickness->injuriesSicknessDetails->max('group') + 1,
            'name' => $medicalPractice->rehabilitation_sickness_name,
        ])->save();

        $specialMedicalCode = SpecialMedicalCode::where('service_type_code', 80)
            ->where('identification_num', $medicalPractice->service_code)
            ->where('start_date', '<=', $medicalPractice->medical_care_date)
            ->where('end_date', '>=', $medicalPractice->medical_care_date)
            ->first();

        $relations = new InjuriesSicknessRelation();
        $relations->fill([
            'injuries_sicknesses_detail_id' => $detail->id,
            'special_medical_code_id' => $specialMedicalCode->id,
            'selected_position' => 1,
        ])->save();
    }

    /**
     * 利用者のサービス種類に紐づく保険外費用を1件取得
     * (32)
     *
     * @param integer $facilityUserId
     * @param Carbon $medicalCareDate
     * @return UserFacilityServiceInformation|null
     */
    public function getUserFacilityServiceInformation(int $facilityUserId, Carbon $medicalCareDate): ?UserFacilityServiceInformation
    {
        return UserFacilityServiceInformation::where('facility_user_id', $facilityUserId)
            // INNER JOINに相当する制約を加えるwhereHas句
            ->whereHas('uninsuredItems', function (Builder $query) use ($medicalCareDate) {
                $query->where('start_month', '<=', $medicalCareDate->copy()->endOfMonth())
                    ->where(function ($query) use ($medicalCareDate) {
                        $query->where('end_month', '>=', $medicalCareDate->copy()->startOfMonth())
                            ->orWhereNull('end_month');
                    })
                    ->whereHas('uninsuredItemHistories',  function (Builder $query) {
                        $query->whereIn('item', ['朝食', '昼食', '夕食']);
                    });
            })
            ->with(['uninsuredItems' => function ($query) use ($medicalCareDate) {
                $query->where('start_month', '<=', $medicalCareDate->copy()->endOfMonth())
                    ->where(function ($query) use ($medicalCareDate) {
                        $query->where('end_month', '>=', $medicalCareDate->copy()->startOfMonth())
                            ->orWhereNull('end_month');
                    })
                    ->with(['uninsuredItemHistories' => function ($query) {
                        $query->whereIn('item', ['朝食', '昼食', '夕食']);
                    }]);
            }])
            ->first();
    }

    /**
     * item_nameに紐づく保険外請求情報を取得
     *
     * @param string $itemName
     * @param integer $facilityUserId
     * @param Carbon $medicalCareDate
     * @return Collection
     */
    public function getUninsuredItemHistories(string $itemName, int $facilityUserId, Carbon $medicalCareDate): Collection
    {
        return UninsuredItemHistory::where('item', $itemName)
            ->whereHas('uninsuredRequest', function (Builder $query) use ($facilityUserId, $medicalCareDate) {
                $query->where('facility_user_id', $facilityUserId)
                    ->where('month', $medicalCareDate->copy()->startOfMonth());
            })
            ->with(['uninsuredRequest' => function ($query) use ($facilityUserId, $medicalCareDate) {
                $query->where('facility_user_id', $facilityUserId)
                    ->where('month', $medicalCareDate->copy()->startOfMonth());
            }])->get();
    }

    /**
     * ユーザーIDと品目名、診療月で絞り込んだ保険外請求を1件取得する
     *
     * @param integer $facilityUserId
     * @param string $name
     * @param Carbon $medicalCareDate
     * @return UninsuredRequest|null
     */
    public function getUninsuredRequestByName(int $facilityUserId ,string $name, Carbon $medicalCareDate): ?UninsuredRequest
    {
        return UninsuredRequest::where('name', $name)
            ->where('facility_user_id', $facilityUserId)
            ->where('month', $medicalCareDate->copy()->startOfMonth())
            ->first();
    }

    /**
     * 自費の保険外請求とその詳細を新規登録する
     * (56) 患者診療情報(自費)5.INSERT
     *
     * @param PatientMedicalCare $uninsuredCost
     * @param HospitacFileLinkage $unconvertedFile
     * @param integer $sort
     * @return void
     */
    public function createUnisuredRequestAndDetail(PatientMedicalCare $uninsuredCost, HospitacFileLinkage $unconvertedFile, int $sort): void
    {
        // 保険外請求を登録
        $uninsuredRequest = new UninsuredRequest();
        $uninsuredRequest->fill([
            'facility_user_id' => $unconvertedFile->userFacilityInfos->facility_user_id,
            'month' => $uninsuredCost->medical_care_date->startOfMonth(),
            'unit_cost' => $uninsuredCost->uninsured_cost,
            'name' => $uninsuredCost->item_name,
            'sort' => $sort,
        ])->save();

        // 詳細を登録
        $detail = new UninsuredRequestDetail();
        $detail->fill([
            'uninsured_request_id' => $uninsuredRequest->id,
            'quantity' => $uninsuredCost->count,
            'date_of_use' => $uninsuredCost->medical_care_date,
        ])->save();
    }

    /**
     * 特定入所者サービスの保険外請求とその詳細を新規登録する
     *
     * @param PatientMedicalCare $uninsuredCost
     * @param HospitacFileLinkage $unconvertedFile
     * @param FacilityUserBurdenLimit $burdenLimit
     * @param integer $sort
     * @return void
     */
    public function createSpecialUnisuredRequestAndDetail(PatientMedicalCare $uninsuredCost, HospitacFileLinkage $unconvertedFile, FacilityUserBurdenLimit $burdenLimit, int $sort): void
    {
        // 保険外請求を登録
        $uninsuredRequest = new UninsuredRequest();
        $uninsuredRequest->fill([
            'facility_user_id' => $unconvertedFile->userFacilityInfos->facility_user_id,
            'month' => $uninsuredCost->medical_care_date->startOfMonth(),
            'unit_cost' => $burdenLimit->food_expenses_burden_limit,
            'name' => '食費',
            'sort' => $sort,
        ])->save();

        // 詳細を登録
        $detail = new UninsuredRequestDetail();
        $detail->fill([
            'uninsured_request_id' => $uninsuredRequest->id,
            'quantity' => 1,
            'date_of_use' => $uninsuredCost->medical_care_date,
        ])->save();
    }

    /**
     * 食事の保険外請求とその詳細を新規登録する
     * (39)
     *
     * @param PatientMedicalCare $uninsuredCost
     * @param HospitacFileLinkage $unconvertedFile
     * @param UninsuredItemHistory $history
     * @param integer $sort
     * @return void
     */
    public function createFoodUnisuredRequestAndDetail(PatientMedicalCare $uninsuredCost, HospitacFileLinkage $unconvertedFile, UninsuredItemHistory $history, int $sort): void
    {
        // 保険外請求を登録
        $uninsuredRequest = new UninsuredRequest();
        $uninsuredRequest->fill([
            'uninsured_item_history_id' => $history->id,
            'facility_user_id' => $unconvertedFile->userFacilityInfos->facility_user_id,
            'month' => $uninsuredCost->medical_care_date->startOfMonth(),
            'unit_cost' => $history->unit_cost,
            'sort' => $sort,
        ])->save();

        // 詳細を登録
        $detail = new UninsuredRequestDetail();
        $detail->fill([
            'uninsured_request_id' => $uninsuredRequest->id,
            'quantity' => 1,
            'date_of_use' => $uninsuredCost->medical_care_date,
        ])->save();
    }

    /**
     * 保険外請求詳細情報を登録する
     * (39),(41),(49),(51),(58), 自費6.INSERT
     *
     * @param integer $uninsuredRequestId
     * @param PatientMedicalCare $uninsuredCost
     * @param integer $quantity
     * @return void
     */
    public function createUninsuredRequestDetail(int $uninsuredRequestId, PatientMedicalCare $uninsuredCost, int $quantity = 1): void
    {
        $detail = new UninsuredRequestDetail();
        $detail->fill([
            'uninsured_request_id' => $uninsuredRequestId,
            'quantity' => $quantity,
            'date_of_use' => $uninsuredCost->medical_care_date,
        ])->save();
    }

    /**
     * ユーザーIDと診療日で絞り込んだ保険外請求の最大sortを取得する
     *
     * @param integer $facilityUserId
     * @param Carbon $medicalCareDate
     * @return integer|null
     */
    public function getMaxUninsuredRequestSort(int $facilityUserId, Carbon $medicalCareDate): ?int
    {
        return UninsuredRequest::where('facility_user_id', $facilityUserId)
            ->where('month', $medicalCareDate->copy()->startOfMonth()->toDateString())
            ->max('sort');
    }
}
