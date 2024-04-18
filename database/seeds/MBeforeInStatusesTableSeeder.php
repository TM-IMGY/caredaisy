<?php

use Illuminate\Database\Seeder;

class MBeforeInStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $dataList = \App\Utility\SeedingUtility::getData('database/seeding_src/m_before_in_statuses.csv');

      for ($i=0,$cnt=count($dataList); $i<$cnt; $i++) { 
        DB::table('m_before_in_statuses')->insert($dataList[$i]);
      }
    }
}
