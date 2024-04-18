<?php

namespace App\Console\Commands;

use App\Service\GroupHome\AutoBillingService;
use Illuminate\Console\Command;

class AutoBillingUninsuredCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'autobilling:uninsured {year} {month}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'execute auto billing uninsured';

    /**
     * Create a new command instance.
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
        $year = $this->argument('year');
        $month = $this->argument('month');
        $logFormat = '['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.']';

        \Log::channel('app')->info("${logFormat} start automated processing of uninsured as ${year} ${month}");

        $autoBillingService = new AutoBillingService();
        $result = $autoBillingService->uninsuredBilling($year, $month);
        $successFacilityUserCnt = $result['successful_facility_user_cnt'];
        $sum = $result['sum'];

        \Log::channel('app')->info("${logFormat} automated processing of uninsured : Performed on ${successFacilityUserCnt} facility users. And the total is ${sum}.");
    }
}
