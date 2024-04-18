<?php

namespace App\Service\GroupHome;

/**
 * 利用料請求書・領収書の種類55向けSQLをまとめたクラス
 * 将来的には比較的共通な(OwnUninsuranceBillServiceに記載されている)SQL群をスーパークラスとして継承したい
 */
class UsageFeesForType55CalcService
{
    /**
     * 保険対象自己負担額　内訳
     */
    public function getInsuredCostDetails($inClauseSr)
    {
        $sql = <<<SQL
            SELECT
                sr.facility_user_id,
                sr.service_count_date,
                sr.unit_price,
                sc.service_item_name,
                sr.unit_number,
                sr.service_result_id,
                smc.special_medical_name
            FROM
                i_service_results sr
            INNER JOIN
                m_service_codes sc ON sc.service_item_code_id = sr.service_item_code_id
            LEFT JOIN
                special_medical_codes smc ON smc.id = sr.special_medical_code_id
            WHERE
                {$inClauseSr}
                sr.facility_id = ?
                AND sr.target_date = ?
                AND sr.calc_kind IN (1, 2, 4)
                AND sr.approval = 1
        SQL;

        return $sql;
    }

    /**
     * 負担割合
     */
    public function getOwnPaymentRate(string $inClauseSr)
    {
        $sql = <<<SQL
            SELECT
                sr.facility_user_id,
            TRUNCATE(((100 - sr.benefit_rate) / 10), 0) AS own_payment_rate
            FROM
                i_service_results sr
            WHERE
                {$inClauseSr}
                sr.facility_id = ?
                AND sr.target_date = ?
                AND sr.calc_kind = 5
                AND sr.approval = 1
                AND sr.result_kind = 1
            ORDER BY sr.facility_user_id ASC
        SQL;

        return $sql;
    }

    /**
     * 保険対象請求分 サービス種類と特別診療費の合計
     */
    public function getTotalInsuredClaims(string $inClauseSr)
    {
        $sql = <<<SQL
            SELECT
                sr.facility_user_id,
                SUM(sr.service_unit_amount) AS service_unit_amount,
                SUM(sr.total_cost) AS total_cost,
                SUM(sr.part_payment) AS part_payment,
                SUM(sr.public_payment) AS public_payment,
                SUM(sr.part_payment + COALESCE(sr.public_payment, 0)) AS insurance_self_pay,
                -- SUM(FLOOR(sr.classification_support_limit_over * sr.unit_price)) AS limit_over
                SUM(sr.public_spending_amount) AS public_spending_amount,
                SUM(sr.insurance_benefit) AS insurance_benefit
            FROM
                i_service_results sr
            WHERE
                {$inClauseSr}
                sr.facility_id = ?
                AND sr.target_date = ?
                AND sr.calc_kind = 5
                AND sr.result_kind IN (1, 2)
                AND sr.approval = 1
            GROUP BY sr.facility_user_id
        SQL;

        return $sql;
    }
}