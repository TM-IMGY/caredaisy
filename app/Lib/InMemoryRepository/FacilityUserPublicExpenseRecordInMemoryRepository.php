<?php

namespace App\Lib\InMemoryRepository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserPublicExpenseRecordRepositoryInterface;
use App\Lib\Entity\Facility;
use App\Lib\Entity\FacilityUserPublicExpense;
use App\Lib\Entity\FacilityUserPublicExpenseRecord;
use App\Lib\Entity\PublicExpense;

/**
 * 施設利用者の公費の記録のインメモリのリポジトリ。
 */
class FacilityUserPublicExpenseRecordInMemoryRepository implements FacilityUserPublicExpenseRecordRepositoryInterface
{
    private array $db;

    public function __construct()
    {
        $this->db = [];
    }

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
     * @return ?FacilityUserPublicExpense
     */
    public function findById(int $facilityUserPublicExpenseId): ?FacilityUserPublicExpense
    {
        // 施設利用者の公費を確保する変数。
        $facilityUserPublicExpense = null;

        foreach ($this->db as $record) {
            // IDが異なれば処理を飛ばす。
            if ($record['public_expense_information_id'] !== $facilityUserPublicExpenseId) {
                continue;
            }

            // 公費マスタ情報を取得する。
            $publicExpense = $record['public_expense'];

            // 施設利用者の公費を作成する。
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
        }

        return $facilityUserPublicExpense;
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
        // 施設利用者の公費の記録全てを確保する変数。
        $facilityUserPublicExpenseRecords = [];

        foreach ($facilityUserIds as $facilityUserId) {
            // 施設利用者の公費の全てを確保する変数。
            $facilityUserPublicExpenses = [];

            foreach ($this->db as $record) {
                // 施設利用者IDが異なれば処理を飛ばす。
                if ($record['facility_user_id'] !== $facilityUserId) {
                    continue;
                }

                // 公費マスタ情報を取得する。
                $publicExpense = $record['public_expense'];

                // 施設利用者の公費を作成する。
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

            // 施設利用者に公費がなければ処理をスキップする。
            if (count($facilityUserPublicExpenses) === 0) {
                continue;
            }

            // 施設利用者の公費の記録を作成する。
            $facilityUserPublicExpenseRecords[] = new FacilityUserPublicExpenseRecord($facilityUserId, $facilityUserPublicExpenses);
        }

        return $facilityUserPublicExpenseRecords;
    }

    /**
     * 施設利用者の公費を挿入する。
     * @param int $amountBornePerson
     * @param ?int $string
     * @param string $effectiveStartDate
     * @param ?string $expiryDate
     * @param int $facilityUserId
     * @param PublicExpense $publicExpense
     * @param string $recipientNumber
     */
    public function insert(
        int $amountBornePerson,
        ?string $bearerNumber,
        string $effectiveStartDate,
        ?string $expiryDate,
        int $facilityUserId,
        PublicExpense $publicExpense,
        string $recipientNumber
    ): int {
        $publicExpenseInformationId = count($this->db) + 1;

        $this->db[] = [
            'amount_borne_person' => $amountBornePerson,
            'application_classification' => null,
            'bearer_number' => $bearerNumber,
            'burden_stage' => null,
            'confirmation_medical_insurance_date' => null,
            'effective_start_date' => $effectiveStartDate,
            'expiry_date' => $expiryDate,
            'facility_user_id' => $facilityUserId,
            'food_expenses_burden_limit' => null,
            'hospitalization_burden' => null,
            'living_expenses_burden_limit' => null,
            'outpatient_contribution' => null,
            'public_expense_information_id' => $publicExpenseInformationId,
            'recipient_number' => $recipientNumber,
            'special_classification' => null,
            'public_expense' => [
                'benefit_rate' => $publicExpense->getBenefitRate(),
                'effective_start_date' => $publicExpense->getEffectiveStartDate(),
                'expiry_date' => $publicExpense->getExpiryDate(),
                'id' => $publicExpense->getId(),
                'legal_name' => $publicExpense->getLegalName(),
                'legal_number' => $publicExpense->getLegalNumber(),
                'priority' => $publicExpense->getPriority(),
                'service_type_code_id' => $publicExpense->getServiceTypeCodeId()
            ]
        ];

        return $publicExpenseInformationId;
    }
}
