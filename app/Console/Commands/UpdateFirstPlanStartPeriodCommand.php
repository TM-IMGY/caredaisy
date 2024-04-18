<?php

namespace App\Console\Commands;

use App\Models\FacilityUser;
use App\Models\FirstServicePlan;
use App\Models\ServicePlan;
use Illuminate\Console\Command;

class UpdateFirstPlanStartPeriodCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updatefirstplanstartperiod';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初回施設サービス計画作成日を更新する';

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
     *
     * @return mixed
     */
    public function handle()
    {
        //ユーザーリスト取得
        $facilityUserIds = ServicePlan::groupBy('facility_user_id')
            ->get('facility_user_id')
            ->toArray();

        foreach ($facilityUserIds as $facilityUserId) {
            $firstPlanStartPeriod = self::getFirstPlanStartPeriod($facilityUserId);
            if (isset($firstPlanStartPeriod)) {
                self::update($facilityUserId,$firstPlanStartPeriod);
            }
        }
    }

    /**
     * 交付済みの初回作成日を取得
     */
    public function getFirstPlanStartPeriod($facilityUserId)
    {
        // 初回のサービスプランidを取得
        $firstDivisionPlans = FirstServicePlan::where('plan_division', 1)
            ->select('service_plan_id')
            ->get()
            ->toArray();

        // 交付済みの作成日を取得
        $planStartPeriod = ServicePlan::whereIn('id', $firstDivisionPlans)
            ->where('facility_user_id', $facilityUserId)
            ->where('status', ServicePlan::STATUS_ISSUED)
            ->select('plan_start_period')
            ->first();

        // 交付済みのデータがない場合は入居日を取得
        if(empty($planStartPeriod)){
            $startDate = FacilityUser::where('facility_user_id', $facilityUserId)
                ->select('start_date')
                ->first();

            $firstPlanStartPeriod = $startDate->start_date;
        }else{
            $firstPlanStartPeriod = $planStartPeriod->plan_start_period;
        }

        return $firstPlanStartPeriod;
    }

    /**
     * 初回施設サービス計画作成日更新
     */
    public function update($facilityUserId,$firstPlanStartPeriod)
    {   
        \DB::beginTransaction();
        try {
            ServicePlan::where('facility_user_id', $facilityUserId)
                ->update(['first_plan_start_period' => $firstPlanStartPeriod]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
