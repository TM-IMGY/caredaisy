<?php

namespace App\Console\Commands;

use App\Models\Facility;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AddServiceToFacilityTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'test:addservice {--service_type_code_id=} {--facility_id=}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'add service to facility test command';

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
        $serviceTypeCodeId = $this->option('service_type_code_id');
        $facilityId = $this->option('facility_id');

        \DB::beginTransaction();
        try {
            $facility = Facility::where('facility_id', $facilityId);

            factory(Service::class)->create([
                'facility_id' => $facilityId,
                'service_type_code_id' => $serviceTypeCodeId,
                'area' => 5,
                'change_date' => Carbon::now()
            ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
