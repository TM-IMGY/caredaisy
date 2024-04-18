<?php

use App\Lib\Common\Consts;
use App\Models\Facility;
use Illuminate\Database\Seeder;

class AllowTransmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // グループホーム陽気（2070500448）の伝送処理対象を有効にする
        DB::table('i_facilities')
            ->where('facility_number', Facility::FACILITY_NUMBER_GROUP_HOME_YOKI)
            ->update([
                'allow_transmission' => Consts::VALID,
            ]);
    }
}
