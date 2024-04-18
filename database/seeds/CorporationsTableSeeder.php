<?php

use Illuminate\Database\Seeder;
use App\Models\Corporation;

class CorporationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        factory(Corporation::class)->create();
    }
}
