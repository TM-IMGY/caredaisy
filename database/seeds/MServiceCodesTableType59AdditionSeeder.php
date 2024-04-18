<?php

use Illuminate\Database\Seeder;

/**
 * サービス項目コードマスタの種類59の追加分のシーダー。
 */
class MServiceCodesTableType59AdditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $data = \App\Utility\SeedingUtility::getData('database/seeding_src/m_service_codes_type59_addition.csv');

        for ($i = 0, $cnt = count($data); $i < $cnt; $i++) {
            DB::table('m_service_codes')->insert($data[$i]);
        }
    }
}
