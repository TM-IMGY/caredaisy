<?php

use Illuminate\Database\Seeder;

class AuthTableNotDisableAuthoritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * スタッフ情報（Authority）の権限をfalseにして利用できないようにする
     * @return void
     */
    public function run()
    {
        $update_data = [
            'authority->read' => false,
            'authority->write' => false,
            'authority->delete' => false
        ];
        $auths = App\Models\Auth::Where('authority->read',true)->update($update_data);

    }
}
