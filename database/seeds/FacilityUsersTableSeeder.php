<?php

use Illuminate\Database\Seeder;
use App\Models\FacilityUser;
use Illuminate\Support\Facades\Crypt;

class FacilityUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $insurer_no = 'abcdefg';
        factory(FacilityUser::class)->create(['insurer_no' => Crypt::encrypt($insurer_no)]);
    }
}
