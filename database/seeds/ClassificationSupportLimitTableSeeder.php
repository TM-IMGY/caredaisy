<?php

use Illuminate\Database\Seeder;

class ClassificationSupportLimitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
      $dataList = \App\Utility\SeedingUtility::getData('database/seeding_src/classification_support_limit.csv');

      for ($i=0,$cnt=count($dataList); $i<$cnt; $i++) { 
        DB::table('classification_support_limit')->insert($dataList[$i]);
      }
    }
}
