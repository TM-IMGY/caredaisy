<?php

namespace App\Lib\MockRepository\DataSets;

/**
 * 施設利用者の給付率の記録のデータセット。
 * 初期はcsvで管理していたが可読性が低すぎたためクラスとして換装した。
 */
class FacilityUserBenefitRecordDataSets
{
    public static function get()
    {
        return [
            [
                'benefit_information_id' => 1,
                'benefit_rate' => 90,
                'benefit_type' => 1,
                'effective_start_date' => '2022/08/01',
                'expiry_date' => null,
                'facility_user_id' => 1
            ],
            [
                'benefit_information_id' => 2,
                'benefit_rate' => 90,
                'benefit_type' => 1,
                'effective_start_date' => '2022/08/01',
                'expiry_date' => null,
                'facility_user_id' => 3
            ],
            [
                'benefit_information_id' => 3,
                'benefit_rate' => 90,
                'benefit_type' => 1,
                'effective_start_date' => '2022/09/01',
                'expiry_date' => null,
                'facility_user_id' => 4
            ],
            [
                'benefit_information_id' => 4,
                'benefit_rate' => 90,
                'benefit_type' => 1,
                'effective_start_date' => '2022/08/01',
                'expiry_date' => null,
                'facility_user_id' => 5
            ],
            [
                'benefit_information_id' => 5,
                'benefit_rate' => 0,
                'benefit_type' => 1,
                'effective_start_date' => '2022/08/01',
                'expiry_date' => null,
                'facility_user_id' => 6
            ],
            [
                'benefit_information_id' => 6,
                'benefit_rate' => 90,
                'benefit_type' => 1,
                'effective_start_date' => '2022/09/01',
                'expiry_date' => null,
                'facility_user_id' => 10
            ]
        ];
    }
}
