<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityAdditionsRepositoryInterface;
use App\Lib\Entity\FacilityAddition;
use App\Lib\Entity\FacilityAdditions;
use App\Lib\MockRepository\DataSets\FacilityAdditionDataSets;

/**
 * 事業所加算の集まりのモックリポジトリクラス。
 */
class FacilityAdditionsMockRepository implements FacilityAdditionsRepositoryInterface
{
    /**
     * 事業所加算の集まりを返す。
     * TODO: 対象年月で絞っていない。
     * TODO: 同様にEXCLUSION_SERVICE_ITEM_CODE_IDSも除外する。
     * @param int $facilityId 事業所ID
     * @param int $serviceTypeCodeId サービス種類コードID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityAdditions
     */
    public function getByFacilityId(int $facilityId, int $serviceTypeCodeId, int $year, int $month): FacilityAdditions
    {
        $dataSets = FacilityAdditionDataSets::get();
        $facilityAdditionRecords = [];
        foreach ($dataSets as $record) {
            if ($record['facility_id'] === $facilityId && $record['service_type_code_id'] === $serviceTypeCodeId) {
                $facilityAdditionRecords[] = $record;
            }
        }

        // 事業所加算を作成する。
        $facilityAdditions = [];
        $serviceItemCodesMockRepository = new ServiceItemCodesMockRepository();
        foreach ($facilityAdditionRecords as $record) {
            // サービス項目コードを取得する。
            $serviceItemCode = $serviceItemCodesMockRepository->find($record['service_item_code_id'], $year, $month);

            $facilityAdditions[] = new FacilityAddition(
                $record['addition_end_date'],
                $record['addition_start_date'],
                $record['facility_addition_id'],
                $record['facility_id'],
                $serviceItemCode,
                $record['service_type_code_id'],
            );
        }

        return new FacilityAdditions($facilityAdditions);
    }
}
