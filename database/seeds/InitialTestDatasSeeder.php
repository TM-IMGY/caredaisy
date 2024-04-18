<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Models\CorporationAccount;
use App\Models\Corporation;
use App\Models\Institution;
use App\Models\Facility;
use App\Models\UserFacilityInformation;
use App\Models\FacilityUser;
use Illuminate\Support\Facades\Crypt;

class InitialTestDatasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $columns = collect(['id', 'password', 'insurer_no']);
        $values = collect([
            [1, 'alice', 'alice@example.com'],
            [2, 'bob', 'bob@example.co.jp'],
        ]);
        $rows = $values->map(fn($x) => $columns->combine($x))->toArray();

        $id = 8;//DBにデータがないなら0スタート
        foreach ($rows as $key => $value) {            
            $id++;
            factory(User::class)->create([
                'password'=>Hash::make($value['password']),
                'account_id' => $id
            ]);
            
            factory(Corporation::class)->create([
                'id' => $id
            ]);

            factory(CorporationAccount::class)->create([
                'account_id'=> $id,
                'corporation_id' => $id
            ]);

            factory(Institution::class)->create([
                'id' => $id,
                'corporation_id' => $id
            ]);

            factory(Facility::class)->create([
                'facility_id' => $id,
                'institution_id' => $id
            ]);

            factory(FacilityUser::class)->create([
                'facility_user_id' => $id,
                'insurer_no' => Crypt::encrypt($value['insurer_no'])
            ]);

            factory(UserFacilityInformation::class)->create([
                'facility_user_id'=> $id,
                'facility_id' => $id
            ]);

        }
    }
}
