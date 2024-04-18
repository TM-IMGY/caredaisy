<?php

use Illuminate\Database\Seeder;

class InsurerMasterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $data = \App\Utility\SeedingUtility::getData('database/seeding_src/insurer_master.csv');

        for ($i=0,$cnt=count($data); $i<$cnt; $i++) { 
          DB::table('insurer_master')->insert($data[$i]);
        }
    }
}
