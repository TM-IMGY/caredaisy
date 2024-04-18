<?php

namespace App\Lib\MockRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserPublicExpenseRecordRepositoryInterface;
use App\Lib\Entity\Facility;
use App\Lib\Entity\FacilityUserPublicExpense;
use App\Lib\Entity\FacilityUserPublicExpenseRecord;
use App\Lib\MockRepository\DataSets\FacilityUserPublicExpenseRecordDataSets;
use DB;

/**
 * 施設利用者の公費の記録のモックリポジトリ。
 */
class FacilityUserPublicExpenseRecordMockRepository implements FacilityUserPublicExpenseRecordRepositoryInterface
{
    /**
     * 施設利用者の公費の記録を返す。getのラッパー。
     * TODO: 対象年月で絞っていない。
     * @param Facility $facility 事業所
     * @param int $facilityUserId 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return ?FacilityUserPublicExpenseRecord
     */
    public function find(
        Facility $facility,
        int $facilityUserId,
        int $year,
        int $month
    ): ?FacilityUserPublicExpenseRecord {
        $facilityUserPublicExpenseRecords = $this->get($facility, [$facilityUserId], $year, $month);
        if (count($facilityUserPublicExpenseRecords) === 0) {
            return null;
        }
        return $facilityUserPublicExpenseRecords[0];
    }

    /**
     * 施設利用者の公費を返す。
     * 施設利用者の公費は対象年月単位で取り扱うのが原則だが、一部で個別で取得する必要があったため追加された。
     * @param int $facilityUserPublicExpenseId 施設利用者の公費のID
     */
    public function findById(
        int $facilityUserPublicExpenseId
    ): ?FacilityUserPublicExpense {
        // TODO: モック実装する。
    }

    /**
     * 施設利用者の公費の記録を返す。
     * TODO: 対象年月で絞っていない。
     * @param Facility $facility 事業所。事業所を渡すことで、そのサービス種類から公費を絞ることができる。
     * @param array $facilityUserIds 施設利用者のID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return FacilityUserPublicExpenseRecord[]
     */
    public function get(Facility $facility, array $facilityUserIds, int $year, int $month): array
    {
        // 指定の施設利用者ごとに公費の記録を作成する。
        $dataSets = FacilityUserPublicExpenseRecordDataSets::get();
        $facilityUserPublicExpenseRecords = [];
        foreach ($facilityUserIds as $facilityUserId) {
            // 施設利用者の公費の記録を作成する。
            $facilityUserPublicExpenses = [];
            foreach ($dataSets as $record) {
                if ($record['facility_user_id'] !== $facilityUserId) {
                    continue;
                }

                $publicExpense = $record['public_expense'];
                $facilityUserPublicExpense = new FacilityUserPublicExpense(
                    $record['amount_borne_person'],
                    $record['application_classification'],
                    $record['bearer_number'],
                    $record['burden_stage'],
                    $record['confirmation_medical_insurance_date'],
                    $record['effective_start_date'],
                    $record['expiry_date'],
                    $record['facility_user_id'],
                    $record['food_expenses_burden_limit'],
                    $record['hospitalization_burden'],
                    $record['living_expenses_burden_limit'],
                    $record['outpatient_contribution'],
                    $record['public_expense_information_id'],
                    $record['recipient_number'],
                    $record['special_classification'],
                    $publicExpense['benefit_rate'],
                    $publicExpense['effective_start_date'],
                    $publicExpense['expiry_date'],
                    $publicExpense['id'],
                    $publicExpense['legal_name'],
                    $publicExpense['legal_number'],
                    $publicExpense['priority'],
                    $publicExpense['service_type_code_id']
                );
    
                $facilityUserPublicExpenses[] = $facilityUserPublicExpense;
            }

            if (count($facilityUserPublicExpenses) === 0) {
                continue;
            }

            $facilityUserPublicExpenseRecords[] = new FacilityUserPublicExpenseRecord($facilityUserId, $facilityUserPublicExpenses);
        }

        return $facilityUserPublicExpenseRecords;
    }
}
