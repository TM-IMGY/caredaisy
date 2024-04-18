<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\NationalHealthBillingRepositoryInterface;
use App\Lib\Entity\NationalHealthBilling;
use App\Lib\Entity\ServiceItemCode;
use App\Lib\Entity\ServiceResult;
use App\Lib\Entity\SpecialMedicalCode;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use App\Lib\MockRepository\DataSets\NationalHealthBillingDataSets;

/**
 * 国保連請求のモックリポジトリ。
 */
class NationalHealthBillingMockRepository implements NationalHealthBillingRepositoryInterface
{
    /**
     * 指定の事業所の施設利用者の国保連請求を返す。
     * TODO: 対象年月で絞っていない。
     * @param int $facilityId 事業所ID
     * @param int $facilityUserId 施設利用者ID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return NationalHealthBilling
     */
    public function find(int $facilityId, int $facilityUserId, int $year, int $month): NationalHealthBilling
    {
        $serviceItemCodesMockRepository = new ServiceItemCodesMockRepository();
        $specialMedicalCodesMockRepository = new SpecialMedicalCodesMockRepository();

        // 対象のサービス実績レコードを全て取得する。
        $datasets = NationalHealthBillingDataSets::get();
        $serviceResultRecords = [];
        foreach ($datasets as $record) {
            $isTargetFacilityUser = $record['facility_user_id'] === $facilityUserId;
            $isApproval = $record['approval'];
            if ($isTargetFacilityUser && $isApproval) {
                $serviceResultRecords[] = $record;
            }
        }

        // 対象のサービス実績レコードから特別診療コードIDを全て取得する。
        $specialMedicalCodeIds = array_filter($serviceResultRecords, function ($record) {
            return $record['special_medical_code_id'] !== null;
        });
        $specialMedicalCodeIds = array_values($specialMedicalCodeIds);

        // サービス実績を作成する。
        $serviceResults = [];
        foreach ($serviceResultRecords as $record) {
            // サービス項目コードを取得する。
            $serviceItemCode = $serviceItemCodesMockRepository->find($record['service_item_code_id'], $year, $month);

            $resultFlag = new ResultFlag(
                $record['date_daily_rate'],
                $record['date_daily_rate_one_month_ago'],
                $record['date_daily_rate_two_month_ago'],
                $record['service_count_date']
            );

            // 特別診療コードIDがない場合はnull、ある場合は特別診療コードを作成する。
            $specialMedicalCode = null;
            if ($record['special_medical_code_id'] !== null) {
                $specialMedicalCode = $specialMedicalCodesMockRepository->find($record['special_medical_code_id'], $year, $month);
            }

            $serviceResult = new ServiceResult(
                $record['approval'],
                $record['benefit_rate'],
                $record['burden_limit'],
                $record['calc_kind'],
                $record['classification_support_limit_in_range'],
                $record['document_create_date'],
                $record['facility_id'],
                $record['facility_name_kanji'],
                $record['facility_number'],
                $record['facility_user_id'],
                $record['insurance_benefit'],
                $record['part_payment'],
                $record['public_benefit_rate'],
                $record['public_expenditure_unit'],
                $record['public_payment'],
                $record['public_spending_amount'],
                $record['public_spending_count'],
                $record['public_spending_unit_number'],
                $record['public_unit_price'],
                $record['rank'],
                $resultFlag,
                $record['result_kind'],
                $record['service_count'],
                $record['service_end_time'],
                $serviceItemCode,
                $record['service_item_code_id'],
                $record['service_result_id'],
                $record['service_start_time'],
                $record['service_unit_amount'],
                $record['service_use_date'],
                $specialMedicalCode,
                $record['target_date'],
                $record['total_cost'],
                $record['unit_number'],
                $record['unit_price']
            );
            $serviceResults[] = $serviceResult;
        }

        // 国保連請求を作成する。
        $nationalHealthBilling = new NationalHealthBilling($facilityUserId, $serviceResults);

        return $nationalHealthBilling;
    }

    /**
     * 事業所と施設利用者の国保連請求を全て返す。
     * TODO: 対象年月で絞っていない。
     * @param int $facilityId 事業所ID
     * @param int[] $facilityUserIds 施設利用者ID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return NationalHealthBilling[]
     */
    public function get(int $facilityId, array $facilityUserIds, int $year, int $month): array
    {
        // 未実装
    }

    /**
     * 国保連請求を保存する。
     * @param int $facilityUserId 施設利用者ID
     * @param ServiceResult[] $serviceResults
     * @param int $year 対象年
     * @param int $month 対象月
     * @return void
     */
    public function save(int $facilityUserId, array $serviceResults, int $year, int $month): void
    {
        // 未実装。
    }

    /**
     * 指定の施設利用者の国保連請求の承認状態を更新する。
     * @param int $facilityUserId 施設利用者ID
     * @param int $flag 承認フラグ
     * @param int $year 対象年
     * @param int $month 対象月
     * @return void
     */
    public function updateApproval(int $facilityUserId, int $flag, int $year, int $month): void
    {
        // 未実装。
    }
}
