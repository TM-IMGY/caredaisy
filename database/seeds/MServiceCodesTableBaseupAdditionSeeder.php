<?php

use Illuminate\Database\Seeder;

/**
 * サービス項目コードマスタのベースアップ等支援加算の追加分のシーダー。
 */
class MServiceCodesTableBaseupAdditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $data = \App\Utility\SeedingUtility::getData('database/seeding_src/m_service_codes_baseup_addition.csv');

        for ($i = 0, $cnt = count($data); $i < $cnt; $i++) {
            DB::table('m_service_codes')->insert($data[$i]);
        }
    }
}
