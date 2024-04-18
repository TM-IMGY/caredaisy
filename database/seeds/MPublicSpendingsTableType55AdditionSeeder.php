<?php

use Illuminate\Database\Seeder;

/**
 * 公費マスタの種別55の追加分のシーダー。
 */
class MPublicSpendingsTableType55AdditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $data = \App\Utility\SeedingUtility::getData('database/seeding_src/m_public_spendings_type55_addition.csv');

        for ($i = 0, $cnt = count($data); $i < $cnt; $i++) {
            DB::table('m_public_spendings')->insert($data[$i]);
        }
    }
}
