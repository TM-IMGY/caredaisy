<?php

use Illuminate\Database\Seeder;

class MAfterOutStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $dataList = \App\Utility\SeedingUtility::getData('database/seeding_src/m_after_out_statuses.csv');

      for ($i=0,$cnt=count($dataList); $i<$cnt; $i++) { 
        DB::table('m_after_out_statuses')->insert($dataList[$i]);
      }
    }
}
