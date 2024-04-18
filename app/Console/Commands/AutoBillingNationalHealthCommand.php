<?php

namespace App\Console\Commands;

use App\Lib\ApplicationBusinessRules\UseCases\Interactors\NationalHealthBillingSaveInteractor;
use App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries\AutoServiceCodeGetInputBoundary;
use App\Service\GroupHome\AutoBillingService;
use Illuminate\Console\Command;
use Log;

class AutoBillingNationalHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'autobilling:nationalhealth {year} {month}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'execute auto billing national health';

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
     * @param AutoServiceCodeGetInputBoundary $interactor
     * @param NationalHealthBillingSaveInteractor $usecase
     * @return mixed
     */
    public function handle(AutoServiceCodeGetInputBoundary $interactor, NationalHealthBillingSaveInteractor $useCase)
    {
        $year = $this->argument('year');
        $month = $this->argument('month');
        $logFormat = '['.__CLASS__.':'.__FUNCTION__.':'.__LINE__.']';

        Log::channel('app')->info("${logFormat} start automated processing of national health as ${year} ${month}");

        $autoBillingService = new AutoBillingService();
        $result = $autoBillingService->nationalHealthBilling($interactor, $useCase, $year, $month);
        $successFacilityUserCnt = $result['successful_facility_user_cnt'];
        $sum = $result['sum'];

        Log::channel('app')->info("${logFormat} automated processing of national health : Performed on ${successFacilityUserCnt} facility users. And the total is ${sum}.");
    }
}
