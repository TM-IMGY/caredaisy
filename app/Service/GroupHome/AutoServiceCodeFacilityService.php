<?php

namespace App\Service\GroupHome;

class AutoServiceCodeFacilityService
{
    /**
     * 計算元のデータから、取得可能なサービスコードを算出する
     * @param array $data
     * @param array $branchTable 条件分岐表
     */
    public function calculateServiceCode(array $data, array $branchTable) : array
    {
        // 条件分岐表の各ブロックから条件に合うサービスコードを算出する
        $serviceCodes = [];
        foreach ($branchTable as $blockKey => $blockVal) {
            for ($i = 0,$cnt = count($blockVal); $i < $cnt; $i++) {
                $record = $blockVal[$i];

                // レコードに記録された条件と引数が一致する数を取得する
                $matchCnt = 0;
                foreach ($record as $recordKey => $recordVal) {
                    if (!in_array($recordKey, ['service_type_code_id','service_item_code_id'], true) && $recordVal === strval($data[$recordKey])) {
                        $matchCnt++;
                    }
                }

                // 一致した数がレコードのservice_type_code_idとservice_item_code_id以外のキーの数と同じ場合サービスコードを確保する
                if ($matchCnt === count($record) - 2) {
                    $serviceCodes[] = [
                        'addition_start_date' => $data['addition_start_date'],
                        'addition_end_date' => $data['addition_end_date'],
                        'facility_id' => $data['facility_id'],
                        'service_type_code_id' => $record['service_type_code_id'],
                        'service_item_code_id' => $record['service_item_code_id'],
                    ];
                    break;
                }
            }
        }

        return $serviceCodes;
    }

    /**
     * 自動サービスコード(事業所)を保存する
     * @param array $params: addition_start_date, before_start_date, before_end_date, care_reward_histories_id, facility_id,
     *     service_type_code_id
     * @return array
     */
    public function save($params) : array
    {
        // 自動サービスコード(事業所)のリポジトリから、計算元のデータオブジェクトを取得する
        $autoServiceCodeTable = new AutoServiceCodeFacilityTable();
        $computationSource = $autoServiceCodeTable->getComputationSource($params);

        // 条件分岐表を取得する
        $branchTable = $autoServiceCodeTable->getConditionalBranchJson(
            $computationSource['service_type_code'],
            $params['addition_start_date'],
            $params['addition_end_date']
        );

        // 計算元のデータから、取得可能なサービスコードを算出する
        $serviceCodes = $this->calculateServiceCode($computationSource, $branchTable);

        // 取得したサービスコードを登録する
        $autoServiceCodeTable->save([
            'before_start_date' => $params['before_start_date'],
            'before_end_date' => $params['before_end_date'],
            'facility_id' => $params['facility_id'],
            'service_codes' => $serviceCodes,
            'service_type_code_id' => $params['service_type_code_id']
        ]);

        return [];
    }
}
