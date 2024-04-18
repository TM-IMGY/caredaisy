<?php

namespace App\Lib\MockRepository\DataSets;

/**
 * 施設利用者の公費の記録のデータセット。
 * 初期はcsvで管理していたが可読性が低すぎたためクラスとして換装した。
 */
class FacilityUserPublicExpenseRecordDataSets
{
    public static function get()
    {
        return [
            [
                'amount_borne_person' => 0,
                'application_classification' => null,
                'bearer_number' => null,
                'burden_stage' => null,
                'confirmation_medical_insurance_date' => null,
                'effective_start_date' => '2022-08-02',
                'expiry_date' => '2022-08-12',
                'facility_user_id' => 1,
                'food_expenses_burden_limit' => null,
                'hospitalization_burden' => null,
                'living_expenses_burden_limit' => null,
                'outpatient_contribution' => null,
                'public_expense_information_id' => 1,
                'recipient_number' => null,
                'special_classification' => null,
                'public_expense' => [
                    'benefit_rate' => 100,
                    'effective_start_date' => '2021/09/01',
                    'expiry_date' => '9999/12/31',
                    'id' => 1,
                    'legal_name' => '生活保護',
                    'legal_number' => 12,
                    'priority' => 14,
                    'service_type_code_id' => 1
                ]
            ],
            [
                'amount_borne_person' => 0,
                'application_classification' => null,
                'bearer_number' => null,
                'burden_stage' => null,
                'confirmation_medical_insurance_date' => null,
                'effective_start_date' => '2022-08-01',
                'expiry_date' => '2022-08-31',
                'facility_user_id' => 3,
                'food_expenses_burden_limit' => null,
                'hospitalization_burden' => null,
                'living_expenses_burden_limit' => null,
                'outpatient_contribution' => null,
                'public_expense_information_id' => 2,
                'recipient_number' => null,
                'special_classification' => null,
                'public_expense' => [
                    'benefit_rate' => 100,
                    'effective_start_date' => '2021/09/01',
                    'expiry_date' => '9999/12/31',
                    'id' => 23,
                    'legal_name' => '生活保護',
                    'legal_number' => 12,
                    'priority' => 14,
                    'service_type_code_id' => 6
                ]
            ],
            [
                'amount_borne_person' => 2500,
                'application_classification' => null,
                'bearer_number' => null,
                'burden_stage' => null,
                'confirmation_medical_insurance_date' => null,
                'effective_start_date' => '2022-08-01',
                'expiry_date' => '2022-08-31',
                'facility_user_id' => 5,
                'food_expenses_burden_limit' => null,
                'hospitalization_burden' => null,
                'living_expenses_burden_limit' => null,
                'outpatient_contribution' => null,
                'public_expense_information_id' => 5,
                'recipient_number' => null,
                'special_classification' => null,
                'public_expense' => [
                    'benefit_rate' => 100,
                    'effective_start_date' => '2021/09/01',
                    'expiry_date' => '9999/12/31',
                    'id' => 16,
                    'legal_name' => '難病公費',
                    'legal_number' => 54,
                    'priority' => 5,
                    'service_type_code_id' => 6
                ]
            ],
            [
                'amount_borne_person' => 0,
                'application_classification' => null,
                'bearer_number' => null,
                'burden_stage' => null,
                'confirmation_medical_insurance_date' => null,
                'effective_start_date' => '2022-08-01',
                'expiry_date' => '2022-08-31',
                'facility_user_id' => 6,
                'food_expenses_burden_limit' => null,
                'hospitalization_burden' => null,
                'living_expenses_burden_limit' => null,
                'outpatient_contribution' => null,
                'public_expense_information_id' => 6,
                'recipient_number' => null,
                'special_classification' => null,
                'public_expense' => [
                    'benefit_rate' => 100,
                    'effective_start_date' => '2021/09/01',
                    'expiry_date' => '9999/12/31',
                    'id' => 23,
                    'legal_name' => '生活保護',
                    'legal_number' => 12,
                    'priority' => 14,
                    'service_type_code_id' => 6
                ]
            ]
        ];
    }
}
