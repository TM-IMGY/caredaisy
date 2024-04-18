<?php

use Illuminate\Database\Seeder;

/**
 * 公費マスタの種類59の追加分のシーダー。
 */
class MPublicSpendingsTableType59AdditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $data = \App\Utility\SeedingUtility::getData('database/seeding_src/m_public_spendings_type59_addition.csv');

        for ($i = 0, $cnt = count($data); $i < $cnt; $i++) {
            DB::table('m_public_spendings')->insert($data[$i]);
        }
    }
}
