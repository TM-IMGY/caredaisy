<?php

namespace App\Console\Commands;

use App\Models\Corporation;
use App\Models\CorporationAccount;
use App\Models\Facility;
use App\Models\Institution;
use App\Models\Service;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InsertTestUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'insert:testuser {--institution_cnt=} {--facility_cnt=} {--staff_id=}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'insert test user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        $institutionCnt = $this->option('institution_cnt');
        $facilityCnt = $this->option('facility_cnt');
        $staffId = $this->option('staff_id');

        // 作成したユーザー名を出力する変数
        $employeeNumber = null;

        \DB::beginTransaction();
        try {
            // アカウントを新規挿入する
            $user = factory(User::class)->create([
                'staff_id' => $staffId
            ]);

            $employeeNumber = $user->employee_number;

            // 法人情報を新規挿入する
            $corporation = factory(Corporation::class)->create();

            // アカウントと法人のリレーション情報を新規挿入する
            factory(CorporationAccount::class)->create([
                'account_id' => $user->account_id,
                'corporation_id' => $corporation->id
            ]);

            // 施設情報をxつ新規挿入する
            for ($i = 0; $i < $institutionCnt; $i++) {
                $institution = factory(Institution::class)->create([
                    'corporation_id' => $corporation->id
                ]);
                // 事業所をxつ新規挿入する
                for ($j = 0; $j < $facilityCnt; $j++) {
                    $facility = factory(Facility::class)->create([
                        'institution_id' => $institution->id
                    ]);

                    // サービスは運用で新規挿入されるので、ここで新規挿入する
                    // 種別32、33、36、37を追加しているが都合が悪ければ適宜修正をする
                    factory(Service::class)->create([
                        'facility_id' => $facility->facility_id,
                        'service_type_code_id' => 1,
                        'area' => 5,
                        'change_date' => Carbon::now()
                    ]);
                    factory(Service::class)->create([
                        'facility_id' => $facility->facility_id,
                        'service_type_code_id' => 2,
                        'area' => 5,
                        'change_date' => Carbon::now()
                    ]);
                    factory(Service::class)->create([
                        'facility_id' => $facility->facility_id,
                        'service_type_code_id' => 3,
                        'area' => 5,
                        'change_date' => Carbon::now()
                    ]);
                    factory(Service::class)->create([
                        'facility_id' => $facility->facility_id,
                        'service_type_code_id' => 4,
                        'area' => 5,
                        'change_date' => Carbon::now()
                    ]);
                }
            }

            \DB::commit();
        } catch (\Exception $th) {
            \DB::rollBack();
            report($th);
            $employeeNumber = null;
        }

        dump($employeeNumber);
    }
}
