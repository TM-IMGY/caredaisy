<?php

namespace App\Console\Commands;

use App\Exceptions\Hospitac\ContructorNumberNotFoundException;
use App\Exceptions\Hospitac\FacilityIdNotFoundException;
use App\Exceptions\Hospitac\IncompelteUninsuredItemException;
use App\Models\Hospitac\HospitacFileLinkage;
use App\Models\Hospitac\PatientMedicalCare;
use App\Service\Hospitac\HospitacService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * HOSPITACファイル連携で取り込んだデータをcaredaisyのテーブル用にコンバートする
 *
 * @todo PHPバージョンアップ後にセマフォ実装
 */
class ConvertHospitacData extends Command
{
    protected $signature = 'hospitac:convert';
    protected $description = 'HOSPITACファイル連携で取り込んだデータをcaredaisyのテーブル用にコンバートする';

    /**
     * ホスピタックサービスクラス
     */
    protected $hospitacService;

    /**
     * バッチ起動時刻
     *
     * @var Carbon
     */
    protected Carbon $startTime;

    public function __construct(HospitacService $hospitacService)
    {
        parent::__construct();

        $this->startTime = now();

        $this->hospitacService = $hospitacService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 診療情報のコンバートを対象がなくなるまで繰り返す
        while (true) {
            try {
                // 未取り込みのファイルを取得
                $unconvertedFile = $this->hospitacService->getUnconvertedMedicalCare($this->startTime);

                // 存在しなければコンバートを終了する
                if (empty($unconvertedFile)) {
                    break;
                }

                // データに問題があれば例外を投げる
                if (empty($unconvertedFile->linkageSetting)) {
                    // 医療機関コードに紐づく事業所IDが設定されていない例外
                    throw new FacilityIdNotFoundException($unconvertedFile->medical_institution_code);
                } elseif (empty($unconvertedFile->userFacilityInfos)) {
                    // 医療機関コードに紐づく患者番号が設定されていない例外
                    throw new ContructorNumberNotFoundException($unconvertedFile->toJson());
                }

                DB::beginTransaction();

                // コンバート対象をid昇順で取得
                $medicalCares = $unconvertedFile->patientMedicalCares->sortBy('id');

                // processing_categoryによって登録・更新・削除をする
                switch ($unconvertedFile->processing_category) {
                    // 登録
                    case '01':
                        // 同一診療日のコンバート済みデータが存在する場合は処理をスキップ
                        $existConvertedSameMedicalCareDate = $this->hospitacService->existConvertedSameMedicalCareDate(
                            $unconvertedFile->medical_institution_code,
                            $unconvertedFile->patient_number,
                            $unconvertedFile->file_created_dt,
                            $medicalCares[0]->medical_care_date
                        );
                        if ($existConvertedSameMedicalCareDate) {
                            break;
                        }

                        // コンバート対象に診療行為情報が存在する場合コンバートする
                        $medicalPractices = $medicalCares->where('data_type', PatientMedicalCare::DATA_TYPE_MEDICAL_PRACTICE);
                        foreach ($medicalPractices as $medicalPractice) {
                            $this->convertMedicalPractice($medicalPractice, $unconvertedFile);
                        }

                        // コンバート対象に食事情報が存在する場合コンバートする
                        $foods = $medicalCares->where('data_type', PatientMedicalCare::DATA_TYPE_FOOD);
                        foreach ($foods as $food) {
                            $this->convertFood($food, $unconvertedFile);
                        }

                        // コンバート対象に自費情報が存在する場合コンバートする
                        $uninsuredCosts = $medicalCares->where('data_type', PatientMedicalCare::DATA_TYPE_UNINSURED_COST);
                        foreach ($uninsuredCosts as $uninsuredCost) {
                            $this->convertUninsuredCost($uninsuredCost, $unconvertedFile);
                        }

                        break;
                    // 更新
                    case '02':
                        $params = [
                            'institutionCode' => $unconvertedFile->medical_institution_code,
                            'patientNumber' => $unconvertedFile->patient_number,
                            'fileCreatedDt' => $unconvertedFile->file_created_dt,
                            'medicalCareDate' => $medicalCares[0]->medical_care_date
                        ];
                        //
                        $existChangeConverted = $this->hospitacService->existChangeConvertedSameMedicalCareDate($params);
                        if ($existChangeConverted) {
                            break;
                        }
                        // 前回取り込んだ患者診療情報を取得する
                        $lastTimePatientMedicalCare = $this->hospitacService->getLastTimePatientMedicalCare($params);

                        // 診療行為
                        $lastTimeMedicalPractices = $lastTimePatientMedicalCare->where('data_type', PatientMedicalCare::DATA_TYPE_MEDICAL_PRACTICE);
                        $medicalPractices = $medicalCares->where('data_type', PatientMedicalCare::DATA_TYPE_MEDICAL_PRACTICE);
                        $this->updateForMedicalPractice($lastTimeMedicalPractices, $medicalPractices, $unconvertedFile);

                        // 食事
                        $lastTimeMeals = $lastTimePatientMedicalCare->where('data_type', PatientMedicalCare::DATA_TYPE_FOOD);
                        $meals = $medicalCares->where('data_type', PatientMedicalCare::DATA_TYPE_FOOD);
                        $this->updateForMeal($lastTimeMeals, $meals, $unconvertedFile);

                        // 自費 start
                        $lastUninsuredCosts = $lastTimePatientMedicalCare->where('data_type', PatientMedicalCare::DATA_TYPE_UNINSURED_COST);
                        $uninsuredCosts = $medicalCares->where('data_type', PatientMedicalCare::DATA_TYPE_UNINSURED_COST);
                        $this->updateForUninsuredCost($lastUninsuredCosts, $uninsuredCosts, $unconvertedFile);

                        break;
                    // 削除
                    case '03':
                        // 削除対象に診療行為情報が存在する場合削除する
                        $medicalPractices = $medicalCares->where('data_type', PatientMedicalCare::DATA_TYPE_MEDICAL_PRACTICE);
                        foreach ($medicalPractices as $medicalPractice) {
                            // 処理D
                            $this->deleteMedicalPractice($medicalPractice, $unconvertedFile);
                        }

                        // 削除対象に食事情報が存在する場合削除する
                        $foods = $medicalCares->where('data_type', PatientMedicalCare::DATA_TYPE_FOOD);
                        foreach ($foods as $food) {
                            // 処理E
                            $this->deleteFood($food, $unconvertedFile);
                        }

                        // 削除対象に自費情報が存在する場合削除する
                        $uninsuredCosts = $medicalCares->where('data_type', PatientMedicalCare::DATA_TYPE_UNINSURED_COST);
                        foreach ($uninsuredCosts as $uninsuredCost) {
                            // 処理F
                            $this->deleteUninsuredCost($uninsuredCost, $unconvertedFile);
                        }

                        break;
                }

                // ステータスを取り込み済みに変更
                $unconvertedFile->status = HospitacFileLinkage::CAPTURED;
                $unconvertedFile->save();

                DB::commit();
            } catch (Exception $e) {
                // データに問題があるのでステータスを取り込みエラーに変更
                DB::rollBack();
                $unconvertedFile->status = HospitacFileLinkage::IMPORT_ERROR;
                $unconvertedFile->save();

                report($e);
            }
        }
    }

    /**
     * 診療行為情報を更新する
     * @param Collection $lastTimeMedicalPractices
     * @param Collection $medicalPractices
     * @param HospitacFileLinkage $unconvertedFile
     */
    public function updateForMedicalPractice(Collection $lastTimeMedicalPractices, Collection $medicalPractices, HospitacFileLinkage $unconvertedFile)
    {
        $orderNumbers = $medicalPractices->pluck('order_number');
        // 今回の変更情報に前回取り込んだ診療行為のオーダー番号が含まれていない場合コンバートする
        foreach ($lastTimeMedicalPractices as $lastTimeMedicalPractice) {
            if (!$orderNumbers->contains($lastTimeMedicalPractice->order_number)) {
                // 処理D
                $this->deleteMedicalPractice($lastTimeMedicalPractice, $unconvertedFile);
            }
        }
        // 今回の変更情報(診療行為)を登録する必要あるかチェックする
        foreach ($medicalPractices as $medicalPractice) {
            $registrationRequired = $this->hospitacService->checkRegistrationRequired($medicalPractice, $lastTimeMedicalPractices);
            if ($registrationRequired) {
                $this->convertMedicalPractice($medicalPractice, $unconvertedFile);
            }
        }
    }

    /**
     * 食事情報を更新する
     * @param Collection $lastTimeMeals
     * @param Collection $meals
     * @param HospitacFileLinkage $unconvertedFile
     */
    public function updateForMeal(Collection $lastTimeMeals, Collection $meals, HospitacFileLinkage $unconvertedFile)
    {
        $orderNumbers = $meals->pluck('order_number');
        //(84) (132) 今回の変更情報に前回取り込んだ食事のオーダー番号と項目名称が含まれていない場合コンバートする
        foreach ($lastTimeMeals as $lastTimeMeal) {
            if ($orderNumbers->contains($lastTimeMeal->order_number) && $meals->pluck('item_name')->contains($lastTimeMeal->item_name)) {
                continue;
            }
            // 処理E
            $this->deleteFood($lastTimeMeal, $unconvertedFile);
        }
        // 今回の変更情報(食事)を登録する必要あるかチェックする
        foreach ($meals as $meal) {
            $registrationRequired = $this->hospitacService->checkRegistrationRequired($meal, $lastTimeMeals);
            if ($registrationRequired) {
                $this->convertFood($meal, $unconvertedFile);
            }
        }
    }

    /**
     * 自費情報を更新する
     * @param Collection $lastUninsuredCosts
     * @param Collection $uninsuredCosts
     * @param HospitacFileLinkage $unconvertedFile
     */
    public function updateForUninsuredCost(Collection $lastUninsuredCosts, Collection $uninsuredCosts, HospitacFileLinkage $unconvertedFile)
    {
        $orderNumbers = $uninsuredCosts->pluck('order_number');
        // 今回の変更情報に前回取り込んだ自費のオーダー番号が含まれていない場合コンバートする
        foreach ($lastUninsuredCosts as $lastUninsuredCost) {
            if (!$orderNumbers->contains($lastUninsuredCost->order_number)) {
                // 処理F
                $this->deleteUninsuredCost($lastUninsuredCost, $unconvertedFile);
            }
        }
        // 今回の変更情報(自費)を登録する必要あるかチェックする
        foreach ($uninsuredCosts as $uninsuredCost) {
            $registrationRequired = $this->hospitacService->checkRegistrationRequired($uninsuredCost, $lastUninsuredCosts);
            if ($registrationRequired) {
                $this->convertUninsuredCost($uninsuredCost, $unconvertedFile);
            }
        }
    }

    /**
     * 診療行為情報をコンバートする
     * 処理A
     *
     * @param PatientMedicalCare $medicalPractice
     * @param HospitacFileLinkage $unconvertedFile
     * @return void
     */
    private function convertMedicalPractice(PatientMedicalCare $medicalPractice, HospitacFileLinkage $unconvertedFile): void
    {
        // サービス実績を取得
        $serviceResult = $this->hospitacService->getServiceResult($medicalPractice->medical_care_date, $medicalPractice->service_code, $unconvertedFile->userFacilityInfos->facility_user_id);

        if (empty($serviceResult)) {
            // 実績がない場合は新規作成
            $this->hospitacService->createSpecialMedicalServiceResult($medicalPractice, $unconvertedFile);
        } else {
            // 実績がある場合は更新
            $this->hospitacService->updateServiceResult($serviceResult, $medicalPractice, $medicalPractice->count);
        }

        // 現在適用中の傷病情報を取得する
        $applyingInjuriesSickness = $this->hospitacService->getApplyingInjuriesSickness($unconvertedFile->userFacilityInfos->facility_user_id, $medicalPractice->medical_care_date);

        // 存在してない場合は診療日当月開始の傷病名情報を登録して終了
        if (empty($applyingInjuriesSickness)) {
            $this->hospitacService->createInjuriesSickness($unconvertedFile->userFacilityInfos->facility_user_id, $medicalPractice);
            return;
        }

        // 傷病名情報の開始月が診療日と同月でない場合
        // 月初日が同一年月日かでチェックする
        if (Carbon::createFromDate($applyingInjuriesSickness->start_date)->startOfMonth() != $medicalPractice->medical_care_date->copy()->startOfMonth()) {
            $this->hospitacService->updateInjuriesSicknessEndDate($applyingInjuriesSickness, $medicalPractice->medical_care_date);
            $this->hospitacService->createInjuriesSickness($unconvertedFile->userFacilityInfos->facility_user_id, $medicalPractice);
            return;
        }

        // 傷病名情報に登録済みのサービスコードを取得
        $identificationNums = $applyingInjuriesSickness->injuriesSicknessDetails->pluck('injuriesSicknessRelations.*.specialMedicalCode.identification_num')->collapse();

        // 存在している場合は何もせず終了
        if ($identificationNums->contains($medicalPractice->service_code)) {
            return;
        }

        // (23) 一致する傷病名を抽出
        $sameSicknessNameDetail = $applyingInjuriesSickness->injuriesSicknessDetails->firstwhere('name', $medicalPractice->rehabilitation_sickness_name);

        if (empty($sameSicknessNameDetail)) {
            // (25) 傷病名詳細と傷病名関連情報を登録
            $this->hospitacService->createInjuriesSicknessDetailAndRelation($applyingInjuriesSickness, $medicalPractice);
        } else {
            // (24) 傷病名詳細を登録
            $this->hospitacService->createInjuriesSicknessRelation($sameSicknessNameDetail, $medicalPractice);
        }
    }

    /**
     * 食事情報をコンバートする
     * 処理B
     *
     * @param PatientMedicalCare $medicalPractice
     * @param HospitacFileLinkage $unconvertedFile
     * @return void
     */
    private function convertFood(PatientMedicalCare $food, HospitacFileLinkage $unconvertedFile): void
    {
        // 適用中の利用者負担額を取得
        $applyingBuedenLimits = $this->hospitacService->getFacilityUserBurdenLimit($food, $unconvertedFile->userFacilityInfos->facility_user_id);

        // 存在する場合は更新、しない場合は新規登録
        if ($applyingBuedenLimits->isNotEmpty()) {
            // (133) 対象ユーザー・診療年月のi_uninsured_requests.sortの最大値を取得
            $maxSort = $this->hospitacService->getMaxUninsuredRequestSort($unconvertedFile->userFacilityInfos->facility_user_id, $food->medical_care_date);

            // 実績情報を取得
            $serviceResult = $this->hospitacService->getNursingCareHospitalFoodExpenses($food->medical_care_date, $unconvertedFile->userFacilityInfos->facility_user_id);

            if (empty($serviceResult)) {
                $this->hospitacService->createServiceResult($food, $unconvertedFile, $applyingBuedenLimits->first());
            } else {
                $this->hospitacService->updateServiceResult($serviceResult, $food);
            }

            // (47) 食費の保険外請求情報を取得する
            $uninsuredRequest = $this->hospitacService->getUninsuredRequestByName($unconvertedFile->userFacilityInfos->facility_user_id, '食費', $food->medical_care_date);

            // 保険外請求がなければ詳細ごとインサート、存在していれば詳細のみインサート
            if (empty($uninsuredRequest)) {
                // (49)
                $this->hospitacService->createSpecialUnisuredRequestAndDetail($food, $unconvertedFile, $applyingBuedenLimits->first(), $maxSort + 1);
            } else {
                // (50) 保険外請求詳細を削除
                $this->hospitacService->deleteUninsuredRequestDetail($uninsuredRequest->id, $food->medical_care_date);
                // (51) 保険外請求を作成
                $this->hospitacService->createUninsuredRequestDetail($uninsuredRequest->id, $food);
            }

            return;
        }

        // (32) 保険外費用を取得
        $userFacilityServiceInformation = $this->hospitacService->getUserFacilityServiceInformation($unconvertedFile->userFacilityInfos->facility_user_id, $food->medical_care_date);

        // (33) (32)で取得した保険外費用に紐づく履歴が3件でない場合は例外を投げる
        if (empty($userFacilityServiceInformation) || $userFacilityServiceInformation->uninsuredItems->pluck('uninsuredItemHistories')->collapse()->count() != 3) {
            // (131)
            $errorMessage = 'ファイル名： ' . $unconvertedFile->file_name . ' 、医療機関コード： ' . $unconvertedFile->medical_institution_code;
            $errorMessage .= '、患者番号： ' . $unconvertedFile->patient_number . '、事業所ID： ' . $unconvertedFile->linkageSetting->facility_id;
            $errorMessage .= '、 利用者ID： ' . $unconvertedFile->userFacilityInfos->facility_user_id;
            throw new IncompelteUninsuredItemException($errorMessage);
        }

        foreach (explode('・', $food->item_name) as $itemName) {
            // (134) 利用者に紐づく保険外請求のsort最大値を取得
            $maxSort = $this->hospitacService->getMaxUninsuredRequestSort($unconvertedFile->userFacilityInfos->facility_user_id, $food->medical_care_date);

            // 対象の履歴を取得
            $histories = $this->hospitacService->getUninsuredItemHistories($itemName, $unconvertedFile->userFacilityInfos->facility_user_id, $food->medical_care_date);

            if ($histories->isEmpty()) {
                // 履歴が存在しない場合は新規登録
                // (130) 同名の履歴を抽出
                $history = $userFacilityServiceInformation->uninsuredItems->first()->uninsuredItemHistories->firstWhere('item', $itemName);
                // (39) 保険外請求詳細と履歴を登録
                $this->hospitacService->createFoodUnisuredRequestAndDetail($food, $unconvertedFile, $history, $maxSort + 1);
            } else {
                // 履歴が存在する場合は削除後に再登録
                // (40)
                $this->hospitacService->deleteUninsuredRequestDetail($histories->first()->uninsuredRequest->id, $food->medical_care_date);
                // (41)
                $this->hospitacService->createUninsuredRequestDetail($histories->first()->uninsuredRequest->id, $food);
            }
        }

        return;
    }

    /**
     * 自費情報をコンバートする
     * 処理C
     *
     * @param PatientMedicalCare $uninsuredCost
     * @param HospitacFileLinkage $unconvertedFile
     * @return void
     */
    private function convertUninsuredCost(PatientMedicalCare $uninsuredCost, HospitacFileLinkage $unconvertedFile): void
    {
        // (135) 対象ユーザー・診療年月のi_uninsured_requests.sortの最大値を取得
        $maxSort = $this->hospitacService->getMaxUninsuredRequestSort($unconvertedFile->userFacilityInfos->facility_user_id, $uninsuredCost->medical_care_date);

        // (54) 品目名から保険外請求を取得する
        $uninsuredRequest = $this->hospitacService->getUninsuredRequestByName($unconvertedFile->userFacilityInfos->facility_user_id, $uninsuredCost->item_name, $uninsuredCost->medical_care_date);

        // 保険外請求の有無によって新規登録or更新
        if (empty($uninsuredRequest)) {
            // (56) 存在しない場合は新規登録して終了
            $this->hospitacService->createUnisuredRequestAndDetail($uninsuredCost, $unconvertedFile, $maxSort + 1);
            return;
        }

        // 存在する場合は更新
        // (57) 取得した保険外請求に紐づく詳細を削除
        $uninsuredRequest->details()
            ->where('date_of_use', $uninsuredCost->medical_care_date)
            ->delete();

        // (58) 詳細情報を登録
        $this->hospitacService->createUninsuredRequestDetail($uninsuredRequest->id, $uninsuredCost, $uninsuredCost->count);

        // (59) 取得した詳細情報を更新
        $uninsuredRequest->fill([
            'unit_cost' => $uninsuredCost->uninsured_cost,
            'name' => $uninsuredCost->item_name,
        ])->save();
    }

    /**
     * 診療行為情報を削除する
     * 処理D
     */
    private function deleteMedicalPractice(PatientMedicalCare $medicalPractice, HospitacFileLinkage $unconvertedFile)
    {
        $medicalCareDate = $medicalPractice['medical_care_date'];
        $serviceCode = $medicalPractice['service_code'];
        $dataList = $this->hospitacService->getServiceResult($medicalPractice->medical_care_date, $serviceCode, $unconvertedFile->userFacilityInfos->facility_user_id);
        // 該当実績がなかったら終了
        if (is_null($dataList)) {
            return;
        }

        // 日割対象日の削除対象日を0にする
        $afterChangeDateDailyRate = $this->hospitacService->formatDateDailyRateToZero($dataList, $medicalCareDate);
        $this->hospitacService->deleteOrUpdateToServiceResult($afterChangeDateDailyRate, $dataList);
    }

    /**
     * 食事情報を削除する
     * 処理E
     */
    private function deleteFood(PatientMedicalCare $food, HospitacFileLinkage $unconvertedFile)
    {
        $burdenLimit = $this->hospitacService->getFacilityUserBurdenLimit($food, $unconvertedFile->userFacilityInfos->facility_user_id);
        // 有効な負担限度額がなかったら保険外請求を削除
        if (empty($burdenLimit->toArray())) {
            $mealNames = explode('・',$food->item_name);
            foreach ($mealNames as $key => $mealName) {
                // memo (89)
                $uninsuredItem = $this->hospitacService->getUninsuredItem(
                    $food->medical_care_date,
                    $unconvertedFile->userFacilityInfos->facility_user_id,
                    $mealName
                );
                if (is_null($uninsuredItem)) {
                    continue;
                }
                // (91)
                $this->hospitacService->deleteUninsuredRequestRerationOfRecord($food->medical_care_date, $uninsuredItem);
            }
            return;
        } else {
            // 有効な負担限度額があったら保険外請求とサービス実績を削除
            $this->hospitacService->deleteServiceResultAndUninsuredRequestOfMeal($food, $unconvertedFile->userFacilityInfos->facility_user_id);
        }
    }

    /**
     * 自費情報をコンバートする
     * 処理F
     */
    private function deleteUninsuredCost(PatientMedicalCare $uninsuredCost, HospitacFileLinkage $unconvertedFile)
    {
        $uninsuredItem = $this->hospitacService->getUninsuredRequestItem(
            $uninsuredCost->medical_care_date,
            $unconvertedFile->userFacilityInfos->facility_user_id,
            $uninsuredCost->item_name
        );
        if (!is_null($uninsuredItem)) {
            $this->hospitacService->deleteUninsuredRequestRerationOfRecord($uninsuredCost->medical_care_date, $uninsuredItem);
        }
    }
}
