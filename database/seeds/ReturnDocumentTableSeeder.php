<?php

use Illuminate\Database\Seeder;
use App\Models\ReturnDocument;

class ReturnDocumentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ReturnDocument::class)->create();
    }
}
