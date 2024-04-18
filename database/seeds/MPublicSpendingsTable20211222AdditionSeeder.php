<?php

use Illuminate\Database\Seeder;

class MPublicSpendingsTable20211222AdditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $dataList = \App\Utility\SeedingUtility::getData('database/seeding_src/m_public_spendings_20211222_addition.csv');

        for ($i=0,$cnt=count($dataList); $i<$cnt; $i++) {
            DB::table('m_public_spendings')->insert($dataList[$i]);
        }
    }
}
