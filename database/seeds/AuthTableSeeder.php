<?php

use Illuminate\Database\Seeder;
use App\Models\Auth;

class AuthTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //管理権限
        factory(Auth::class)->create([
            'request' => [
                'read' => true,
                'write' => true,
                'delete' => true,
                'approve' => true,
                'transmit' => true,
            ],
            'authority' => [
                'read' => true,
                'write' => true,
                'delete' => true,
            ],
            'care_plan' => [
                'read' => true,
                'write' => true,
                'delete' => true,
                'decide' => true,
            ],
            'facility' => [
                'read' => true,
                'write' => true,
                'delete' => true,
            ],
            'facility_user_1' => [
                'read' => true,
                'write' => true,
                'delete' => true,
            ],
            'facility_user_2' => [
                'read' => true,
                'write' => true,
                'delete' => true,
            ],
        ]);
        //請求伝送者
        factory(Auth::class)->create([
            'request' => [
                'read' => true,
                'write' => true,
                'delete' => true,
                'approve' => true,
                'transmit' => true,
            ],
            'authority' => [
                'read' => false,
                'write' => false,
                'delete' => false,
            ],
            'care_plan' => [
                'read' => true,
                'write' => false,
                'delete' => false,
                'decide' => false,
            ],
            'facility' => [
                'read' => true,
                'write' => false,
                'delete' => false,
            ],
            'facility_user_1' => [
                'read' => true,
                'write' => true,
                'delete' => false,
            ],
            'facility_user_2' => [
                'read' => true,
                'write' => true,
                'delete' => false,
            ],
        ]);
        //ケアマネ
        factory(Auth::class)->create([
            'request' => [
                'read' => true,
                'write' => false,
                'delete' => false,
                'approve' => false,
                'transmit' => false,
            ],
            'authority' => [
                'read' => false,
                'write' => false,
                'delete' => false,
            ],
            'care_plan' => [
                'read' => true,
                'write' => true,
                'delete' => true,
                'decide' => true,
            ],
            'facility' => [
                'read' => true,
                'write' => false,
                'delete' => false,
            ],
            'facility_user_1' => [
                'read' => true,
                'write' => false,
                'delete' => false,
            ],
            'facility_user_2' => [
                'read' => true,
                'write' => false,
                'delete' => false,
            ],
        ]);
        
        //請求伝送者かつケアマネ
        factory(Auth::class)->create([
            'request' => [
                'read' => true,
                'write' => true,
                'delete' => true,
                'approve' => true,
                'transmit' => true,
            ],
            'authority' => [
                'read' => false,
                'write' => false,
                'delete' => false,
            ],
            'care_plan' => [
                'read' => true,
                'write' => true,
                'delete' => true,
                'decide' => true,
            ],
            'facility' => [
                'read' => true,
                'write' => false,
                'delete' => false,
            ],
            'facility_user_1' => [
                'read' => true,
                'write' => true,
                'delete' => false,
            ],
            'facility_user_2' => [
                'read' => true,
                'write' => true,
                'delete' => false,
            ],
        ]);
        
    }
}
