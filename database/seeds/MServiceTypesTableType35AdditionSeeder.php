<?php

use Illuminate\Database\Seeder;

class MServiceTypesTableType35AdditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataList = \App\Utility\SeedingUtility::getData('database/seeding_src/m_service_types_type35_addition.csv');

        for ($i=0,$cnt=count($dataList); $i<$cnt; $i++) {
          DB::table('m_service_types')->insert($dataList[$i]);
        }
    }
}
