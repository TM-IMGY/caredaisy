<?php

use Illuminate\Database\Seeder;

/**
 * 特別診療費コードマスタのシーダー。
 */
class SpecialMedicalCodesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $data = \App\Utility\SeedingUtility::getData('database/seeding_src/special_medical_codes.csv');

        for ($i = 0, $cnt = count($data); $i < $cnt; $i++) { 
          DB::table('special_medical_codes')->insert($data[$i]);
        }
    }
}
