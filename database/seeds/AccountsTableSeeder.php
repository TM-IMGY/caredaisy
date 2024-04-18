<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $columns = collect(['account_id', 'password', 'email']);
        $values = collect([
            [10, 'alice', 'alice@example.com'],
            [11, 'bob', 'bob@example.co.jp'],
        ]);
        $rows = $values->map(fn($x) => $columns->combine($x))->toArray();

        foreach ($rows as $key => $value) {
            factory(User::class)->create([
                'password'=>Hash::make($value['password']),
                'account_id' => $value['account_id']
            ]);
        }
        // $password = 'Password';
        // factory(User::class)->create(['password'=>Hash::make($password)]);
    }
}
