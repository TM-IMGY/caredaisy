<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $this->call(AuthTableSeeder::class);
        $this->call(MServiceTypesTableSeeder::class);
        $this->call(MServiceCodesTableSeeder::class);
        $this->call(MAfterOutStatusesTableSeeder::class);
        $this->call(MBeforeInStatusesTableSeeder::class);
        $this->call(MCareLevelsTableSeeder::class);
        $this->call(MPublicSpendingsTableSeeder::class);
        $this->call(InsurerMasterTableSeeder::class);
        $this->call(ClassificationSupportLimitTableSeeder::class);
        $this->call(SpecialMedicalCodesTableSeeder::class);
        $this->call(MdcGroupNamesTableSeeder::class);
    }
}
