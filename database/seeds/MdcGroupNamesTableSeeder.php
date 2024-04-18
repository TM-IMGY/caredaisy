<?php

use Illuminate\Database\Seeder;

/**
 * MDC分類名称マスタのシーダー。
 */
class MdcGroupNamesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $data = \App\Utility\SeedingUtility::getData('database/seeding_src/mdc_group_name.csv');

        for ($i = 0, $cnt = count($data); $i < $cnt; $i++) {
          DB::table('mdc_group_names')->insert($data[$i]);
        }
    }
}
