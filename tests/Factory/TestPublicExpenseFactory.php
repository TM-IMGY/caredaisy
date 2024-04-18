<?php

namespace Tests\Factory;

use App\Lib\Entity\PublicExpense;

/**
 * テスト用の公費のファクトリ。
 */
class TestPublicExpenseFactory
{
    /**
     * 原爆助成を種類32で生成する。
     */
    public function generateAtomicBomb32(): PublicExpense
    {
        return new PublicExpense(
            // benefit_rate
            100,
            // effective_start_date
            '2021/09/01',
            // expiry_date
            '9999/12/31',
            // id
            1,
            // legal_name
            '原爆助成',
            // legal_number
            81,
            // priority
            12,
            // service_type_code_id
            1
        );
    }

    /**
     * 中国残留法人等を種類32で生成する。
     */
    public function generateChina32(): PublicExpense
    {
        return new PublicExpense(
            // benefit_rate
            100,
            // effective_start_date
            '2021/09/01',
            // expiry_date
            '9999/12/31',
            // id
            1,
            // legal_name
            '中国残留法人等',
            // legal_number
            25,
            // priority
            13,
            // service_type_code_id
            1
        );
    }

    /**
     * 難病公費を種類32で生成する。
     */
    public function generateIncurableDisease32(): PublicExpense
    {
        return new PublicExpense(
            // benefit_rate
            100,
            // effective_start_date
            '2021/09/01',
            // expiry_date
            '9999/12/31',
            // id
            1,
            // legal_name
            '難病公費',
            // legal_number
            54,
            // priority
            5,
            // service_type_code_id
            6
        );
    }

    /**
     * 生活保護を種類32で生成する。
     */
    public function generatePublicAssistance32(): PublicExpense
    {
        return new PublicExpense(
            // benefit_rate
            100,
            // effective_start_date
            '2021/09/01',
            // expiry_date
            '9999/12/31',
            // id
            1,
            // legal_name
            '生活保護',
            // legal_number
            12,
            // priority
            14,
            // service_type_code_id
            1
        );
    }

    /**
     * 自立更生を種類55で生成する。
     */
    public function generateRehabilitation55(): PublicExpense
    {
        return new PublicExpense(
            // benefit_rate
            100,
            // effective_start_date
            '2021/09/01',
            // expiry_date
            '9999/12/31',
            // id
            1,
            // legal_name
            '自立更生',
            // legal_number
            15,
            // priority
            3,
            // service_type_code_id
            6
        );
    }
}
