<?php

use Illuminate\Database\Seeder;

class MCareLevelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $dataList = \App\Utility\SeedingUtility::getData('database/seeding_src/m_care_levels.csv');

      for ($i=0,$cnt=count($dataList); $i<$cnt; $i++) { 
        DB::table('m_care_levels')->insert($dataList[$i]);
      }
    }
}
