<?php

use Illuminate\Database\Seeder;

class InsurerMasterTable20221128AdditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = \App\Utility\SeedingUtility::getData('database/seeding_src/insurer_master_20221128_addition.csv');

        for ($i=0,$cnt=count($data); $i<$cnt; $i++) {
          DB::table('insurer_master')->insert($data[$i]);
        }
    }
}
