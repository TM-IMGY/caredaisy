<?php

namespace App\Service\GroupHome;

use \App\Models\CareRewardHistory;
use \App\Models\FacilityAddition;
use \App\Models\ServiceType;
use \App\Models\Service;

class AutoServiceCodeFacilityTable
{
    public const STORAGE_PATH = 'json/auto_service_code_facility/';

    /**
     * 事業所加算を削除する
     * @param array $params key: facility_id, start_date, end_date
     * @return void
     */
    public function delete(array $params) : void
    {
        FacilityAddition::
            where('facility_id', $params['facility_id'])
            ->where('service_type_code_id', $params['service_type_code_id'])
            ->whereDate('addition_start_date', $params['start_date'])
            ->whereDate('addition_end_date', $params['end_date'])
            ->delete();
    }

    /**
     * 計算の元データ配列を返す
     * @param array $params
     * @return array
     */
    public function getComputationSource(array $params) : array
    {
        // 介護報酬履歴情報を取得する
        $careRewardHistory = CareRewardHistory::
            where('id', $params['care_reward_histories_id'])
            ->select(
                // 種別32と37が混合している
                'improvement_of_specific_treatment',
                'nutrition_management',
                'oral_hygiene_management',
                'scientific_nursing',
                'treatment_improvement',
                // 種別33
                'adl_maintenance_etc',
                'discharge_cooperation',
                'individual_function_training_1',
                'individual_function_training_2',
                'medical_institution_cooperation',
                'night_nursing_system',
                'service_form',
                'support_continued_occupancy',
                'support_persons_disabilities',
                // 種類55
                'excretion_support',
                'promotion_independence_support',
                // ベースアップ等支援加算
                'baseup',
            )
            ->first();
        if ($careRewardHistory === null) {
            throw new \Exception('the registered care reward history is incorrect');
        }

        // サービス種別コードデータを取得する
        $serviceTypeCode = ServiceType::
            where('service_type_code_id', $params['service_type_code_id'])
            ->select('service_type_code')
            ->first();
        if ($serviceTypeCode === null) {
            throw new \Exception('the registered service type is incorrect');
        }
        $serviceTypeCode = $serviceTypeCode->service_type_code;

        return [
            'addition_start_date' => $params['addition_start_date'],
            'addition_end_date' => $params['addition_end_date'],
            'adl_maintenance_etc' => $careRewardHistory['adl_maintenance_etc'],
            'discharge_cooperation' => $careRewardHistory['discharge_cooperation'],
            'facility_id' => $params['facility_id'],
            'improvement_of_specific_treatment' => $careRewardHistory['improvement_of_specific_treatment'],
            'individual_function_training_1' => $careRewardHistory['individual_function_training_1'],
            'individual_function_training_2' => $careRewardHistory['individual_function_training_2'],
            'medical_institution_cooperation' => $careRewardHistory['medical_institution_cooperation'],
            'night_nursing_system' => $careRewardHistory['night_nursing_system'],
            'nutrition_management' => $careRewardHistory['nutrition_management'],
            'oral_hygiene_management' => $careRewardHistory['oral_hygiene_management'],
            'scientific_nursing' => $careRewardHistory['scientific_nursing'],
            'service_form' => $careRewardHistory['service_form'],
            'service_type_code' => $serviceTypeCode,
            'support_continued_occupancy' => $careRewardHistory['support_continued_occupancy'],
            'support_persons_disabilities' => $careRewardHistory['support_persons_disabilities'],
            'treatment_improvement' => $careRewardHistory['treatment_improvement'],
            'excretion_support' => $careRewardHistory['excretion_support'],
            'promotion_independence_support' => $careRewardHistory['promotion_independence_support'],
            'baseup' => $careRewardHistory['baseup']
        ];
    }

    /**
     * 条件分岐表を返す
     * @param string $serviceType
     * @param string $startDateStr
     * @param string $endDateStr
     * @return array
     */
    public function getConditionalBranchJson(string $serviceType, string $startDateStr, string $endDateStr) : array
    {
        // ストレージ内のファイルのリストを取得する
        $dirPath = database_path(self::STORAGE_PATH);
        $files = glob($dirPath."*.json");
        if (count($files) === 0) {
            throw new \Exception('can not get conditional branch table');
        }

        // 対象年月用のjsonファイルのパスを取得する
        $jsonFilePath = null;
        $startDate = new \DateTime($startDateStr);
        $endDate = new \DateTime($endDateStr);
        for ($i = 0, $cnt = count($files); $i < $cnt; $i++) {
            preg_match("#${dirPath}${serviceType}_([0-9]{4})([0-9]{2})_([0-9]{4})([0-9]{2}).json#", $files[$i], $result);
            if (count($result) === 0) {
                continue;
            }
            $startY = $result[1];
            $startM = $result[2];
            $endY = $result[3];
            $endM = $result[4];
            $startYM = new \DateTime("${startY}-${startM}-1");
            $endYM = (new \DateTime("${endY}-${endM}-1"))->modify('last day of');
            if ($startYM <= $startDate && $startDate <= $endYM && $startYM <= $endDate && $endDate <= $endYM) {
                $jsonFilePath = $files[$i];
                break;
            }
        }
        if ($jsonFilePath === null) {
            throw new \Exception('can not get conditional branch table');
        }

        // jsonファイルを取得して、デコードして返す
        $jsonData = \File::get($jsonFilePath);
        $json = json_decode($jsonData, true);

        // jsonファイルのキー名がサービス種別によって異なるので調整する
        // 32と37の場合
        $jsonFileKey = 'dementia_communal_living_care';
        if (in_array($serviceType,['33', '35', '36', '55'])) {
            $jsonFileKey = 'care_addition_information';
        }

        return $json[$jsonFileKey];
    }

    /**
     * 保存する
     * @param array $params key: before_start_date, before_end_date, facility_id, service_codes, service_type_code_id
     * @return void
     */
    public function save(array $params) : void
    {
        \DB::beginTransaction();
        try {
            // 既に登録されている事業所加算を削除する
            $this->delete([
                'facility_id' => $params['facility_id'],
                'service_type_code_id' => $params['service_type_code_id'],
                'start_date' => $params['before_start_date'],
                'end_date' => $params['before_end_date'],
            ]);

            // 事業所加算を登録しなおす
            if (count($params['service_codes']) > 0) {
                FacilityAddition::insert($params['service_codes']);
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
