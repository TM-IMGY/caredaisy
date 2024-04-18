<?php

namespace App\Lib\Repository;

use App\Lib\ApplicationBusinessRules\DataAccessInterface\NationalHealthBillingRepositoryInterface;
use App\Lib\Entity\NationalHealthBilling;
use App\Lib\Entity\ServiceItemCode;
use App\Lib\Entity\ServiceResult;
use App\Lib\Entity\SpecialMedicalCode;
use App\Lib\ValueObject\NationalHealthBilling\ResultFlag;
use Carbon\CarbonImmutable;
use DB;
use Exception;

/**
 * 国保連請求のリポジトリ。
 */
class NationalHealthBillingRepository implements NationalHealthBillingRepositoryInterface
{
    /**
     * 指定の事業所の施設利用者の国保連請求を返す。
     * @param int $facilityId 事業所ID
     * @param int $facilityUserId 施設利用者ID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return NationalHealthBilling
     */
    public function find(int $facilityId, int $facilityUserId, int $year, int $month): NationalHealthBilling
    {
        $nationalHealthBillings = $this->get($facilityId, [$facilityUserId], $year, $month);
        return $nationalHealthBillings[0];
    }

    /**
     * 事業所と施設利用者の国保連請求を全て返す。
     * @param int $facilityId 事業所ID
     * @param int[] $facilityUserIds 施設利用者ID
     * @param int $year 対象年
     * @param int $month 対象月
     * @return NationalHealthBilling[]
     */
    public function get(int $facilityId, array $facilityUserIds, int $year, int $month): array
    {
        // 対象のサービス実績レコードを全て取得する。
        $serviceResultRecords = DB::table('i_service_results')
            ->where('facility_id', $facilityId)
            ->whereYear('target_date', $year)
            ->whereMonth('target_date', $month)
            ->whereIn('facility_user_id', $facilityUserIds)
            // 種類32、33、35、36、37、55では小計計算(3)は一応計算しているくらいのレコードになる。
            ->whereIn('calc_kind', [1, 2, 4, 5])
            ->join('m_service_codes', 'i_service_results.service_item_code_id', '=', 'm_service_codes.service_item_code_id')
            ->select([
                'i_service_results.*',
                'm_service_codes.classification_support_limit_flg',
                'm_service_codes.rank as sc_rank',
                'm_service_codes.service_calcinfo_1',
                'm_service_codes.service_calcinfo_2',
                'm_service_codes.service_calcinfo_3',
                'm_service_codes.service_calcinfo_4',
                'm_service_codes.service_calcinfo_5',
                'm_service_codes.service_calculation_unit',
                'm_service_codes.service_end_date',
                'm_service_codes.service_item_code',
                'm_service_codes.service_item_code_id',
                'm_service_codes.service_item_name',
                'm_service_codes.service_kind',
                'm_service_codes.service_start_date',
                'm_service_codes.service_synthetic_unit',
                'm_service_codes.service_type_code',
                'm_service_codes.synthetic_unit_input_flg',
            ])
            ->orderBy('facility_user_id', 'asc')
            ->orderBy('m_service_codes.service_item_code_id', 'asc')
            ->get();

        // 対象のサービス実績レコードから特別診療コードIDを全て取得する。
        $specialMedicalCodeIds = $serviceResultRecords->pluck('special_medical_code_id')
            ->filter(function ($id, $index) {
                return $id !== null;
            })
            ->uniqueStrict()
            ->values();

        $targetMonthStartDate = "${year}-${month}-1";
        $targetMonthEndDate = (new CarbonImmutable($targetMonthStartDate))->endOfMonth()->format('Y-m-d');

        // 取得した特別診療コードIDから特別診療コードを取得する。
        // サービス実績テーブルと厳密にはリレーション関係にないので別々に取得している。
        $specialMedicalCodeRecords = DB::table('special_medical_codes')
            ->whereIn('id', $specialMedicalCodeIds)
            ->whereDate('start_date', '<=', $targetMonthEndDate)
            ->whereDate('end_date', '>=', $targetMonthStartDate)
            // 使用されないカラムが多いためselectするカラムを制限している。
            ->select([
                'history_num',
                'id',
                'identification_num',
                'service_type_code',
                'special_medical_name',
                'start_date',
                'end_date',
                'unit'
            ])
            ->orderBy('id')
            ->get();

        // サービス実績を確保する領域を宣言する。
        $serviceResults = [];

        foreach ($serviceResultRecords as $record) {
            // サービス項目コードを作成する。
            $serviceItemCode = new ServiceItemCode(
                $record->classification_support_limit_flg,
                $record->sc_rank,
                $record->service_calcinfo_1,
                $record->service_calcinfo_2,
                $record->service_calcinfo_3,
                $record->service_calcinfo_4,
                $record->service_calcinfo_5,
                $record->service_calculation_unit,
                $record->service_end_date,
                $record->service_item_code,
                $record->service_item_code_id,
                $record->service_item_name,
                $record->service_kind,
                $record->service_start_date,
                $record->service_synthetic_unit,
                $record->service_type_code,
                $record->synthetic_unit_input_flg
            );

            // 実績フラグを作成する。
            $resultFlag = new ResultFlag(
                $record->date_daily_rate,
                $record->date_daily_rate_one_month_ago,
                $record->date_daily_rate_two_month_ago,
                $record->service_count_date
            );

            // 特別診療コードIDがない場合はnull、ある場合は特別診療コードを作成する。
            $specialMedicalCode = null;
            if ($record->special_medical_code_id) {
                $specialMedicalCodeIndex = $specialMedicalCodeRecords->search(function ($code, $index) use ($record) {
                    return $record->special_medical_code_id === $code->id;
                });
                $specialMedicalCodeRecord = $specialMedicalCodeRecords[$specialMedicalCodeIndex];
                $specialMedicalCode = new SpecialMedicalCode(
                    $specialMedicalCodeRecord->end_date,
                    $specialMedicalCodeRecord->history_num,
                    $specialMedicalCodeRecord->id,
                    $specialMedicalCodeRecord->identification_num,
                    $specialMedicalCodeRecord->service_type_code,
                    $specialMedicalCodeRecord->special_medical_name,
                    $specialMedicalCodeRecord->start_date,
                    $specialMedicalCodeRecord->unit
                );
            }

            // サービス実績を作成する。
            $serviceResult = new ServiceResult(
                $record->approval,
                $record->benefit_rate,
                $record->burden_limit,
                $record->calc_kind,
                $record->classification_support_limit_in_range,
                $record->document_create_date,
                $record->facility_id,
                $record->facility_name_kanji,
                $record->facility_number,
                $record->facility_user_id,
                $record->insurance_benefit,
                $record->part_payment,
                $record->public_benefit_rate,
                $record->public_expenditure_unit,
                $record->public_payment,
                $record->public_spending_amount,
                $record->public_spending_count,
                $record->public_spending_unit_number,
                $record->public_unit_price,
                $record->rank,
                $resultFlag,
                $record->result_kind,
                $record->service_count,
                $record->service_end_time,
                $serviceItemCode,
                $record->service_item_code_id,
                $record->service_result_id,
                $record->service_start_time,
                $record->service_unit_amount,
                $record->service_use_date,
                $specialMedicalCode,
                $record->target_date,
                $record->total_cost,
                $record->unit_number,
                $record->unit_price
            );
            $serviceResults[] = $serviceResult;
        }

        // 国保連請求を確保する領域を宣言する。
        $nationalHealthBillings = [];

        // 施設利用者それぞれのサービス実績を取得する。
        foreach ($facilityUserIds as $facilityUserId) {
            $targetServiceResults = array_filter($serviceResults, function ($serviceResult) use ($facilityUserId) {
                return $facilityUserId === $serviceResult->getFacilityUserId();
            });
            $targetServiceResults = array_values($targetServiceResults);

            // 設利用者それぞれの国保連請求を作成する。
            $nationalHealthBilling = new NationalHealthBilling($facilityUserId, $targetServiceResults);
            $nationalHealthBillings[] = $nationalHealthBilling;
        }

        return $nationalHealthBillings;
    }

    /**
     * 国保連請求を保存する。
     * @param int $facilityUserId 施設利用者ID
     * @param ServiceResult[] $serviceResults サービス実績
     * @param int $year 対象年
     * @param int $month 対象月
     */
    public function save(int $facilityUserId, array $serviceResults, int $year, int $month): void
    {
        DB::beginTransaction();
        try {
            DB::table('i_service_results')
                ->where('facility_user_id', $facilityUserId)
                ->whereYear('target_date', $year)
                ->whereMonth('target_date', $month)
                ->delete();

            if (count($serviceResults) > 0) {
                // 挿入するデータを作成する。
                $data = [];
                foreach ($serviceResults as $result) {
                    $resultFlag = $result->getResultFlag();
                    $specialMedicalCodeId = $result->hasSpecialMedicalCode() ? $result->getSpecialMedicalCode()->getSpecialMedicalCodeId() : null;

                    $data[] = [
                        'approval' => $result->getApproval(),
                        'benefit_rate' => $result->getBenefitRate(),
                        'burden_limit' => $result->getBurdenLimit(),
                        'calc_kind' => $result->getCalcKind(),
                        'classification_support_limit_in_range' => $result->getClassificationSupportLimitInRange(),
                        'date_daily_rate' => $resultFlag->getDateDailyRate(),
                        'date_daily_rate_one_month_ago' => $resultFlag->getDateDailyRateOneMonthAgo(),
                        'date_daily_rate_two_month_ago' => $resultFlag->getDateDailyRateTwoMonthAgo(),
                        'document_create_date' => $result->getDocumentCreateDate(),
                        'facility_id' => $result->getFacilityId(),
                        'facility_name_kanji' => $result->getFacilityNameKanji(),
                        'facility_number' => $result->getFacilityNumber(),
                        'facility_user_id' => $result->getFacilityUserId(),
                        'insurance_benefit' => $result->getInsuranceBenefit(),
                        'part_payment' => $result->getPartPayment(),
                        'public_benefit_rate' => $result->getPublicBenefitRate(),
                        'public_expenditure_unit' => $result->getPublicExpenditureUnit(),
                        'public_payment' => $result->getPublicPayment(),
                        'public_spending_amount' => $result->getPublicSpendingAmount(),
                        'public_spending_count' => $result->getPublicSpendingCount(),
                        'public_spending_unit_number' => $result->getPublicSpendingUnitNumber(),
                        'public_unit_price' => $result->getPublicUnitPrice(),
                        'result_kind' => $result->getResultKind(),
                        'service_count' => $result->getServiceCount(),
                        'service_count_date' => $resultFlag->getServiceCountDate(),
                        'service_item_code_id' => $result->getServiceItemCodeId(),
                        'service_start_time' => $result->getServiceStartTime(),
                        'service_end_time' => $result->getServiceEndTime(),
                        'service_unit_amount' => $result->getServiceUnitAmount(),
                        'service_use_date' => $result->getServiceUseDate(),
                        'special_medical_code_id' => $specialMedicalCodeId,
                        'target_date' => $result->getTargetDate(),
                        'total_cost' => $result->getTotalCost(),
                        'unit_number' => $result->getUnitNumber(),
                        'unit_price' => $result->getUnitPrice()
                    ];
                }
    
                DB::table('i_service_results')->insert($data);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
        $query = <<< SQL
UPDATE
    i_service_results
SET
    approval = ?
WHERE
    year(target_date) = ?
    AND month(target_date) = ?
    AND facility_user_id = ?
    AND calc_kind IN (1, 2, 4, 5)
SQL;

        try {
            DB::select($query, [$flag, $year, $month, $facilityUserId]);
        } catch (Exception $e) {
            report($e);
            throw $e;
        }
    }
}
