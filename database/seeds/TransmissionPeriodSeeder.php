<?php

use Illuminate\Database\Seeder;

class TransmissionPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('transmission_period')->insert([
            [
            'start_time' => '0:10',
            'end_time' => '23:50'
            ]
        ]);
    }
}
