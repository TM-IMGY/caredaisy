<?php

use Illuminate\Database\Seeder;

/**
 * サービス種別マスタの種別55の追加分のシーダー。
 */
class MServiceTypesTableType55AdditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $data = \App\Utility\SeedingUtility::getData('database/seeding_src/m_service_types_type55_addition.csv');

        for ($i = 0, $cnt = count($data); $i < $cnt; $i++) {
          DB::table('m_service_types')->insert($data[$i]);
        }
    }
}
