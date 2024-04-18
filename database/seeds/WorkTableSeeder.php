<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

use App\User;
use App\Models\CorporationAccount;
use App\Models\Corporation;
use App\Models\Institution;
use App\Models\Facility;
use App\Models\UserFacilityInformation;
use App\Models\FacilityUser;
use App\Models\UserBenefitInformation;
use App\Models\UserPublicExpenseInformation;
use App\Models\UserCareInformation;
use App\Models\UserIndependenceInformation;
use App\Models\Approval;
use App\Models\FacilityAddition;
use App\Models\Service;
use App\Models\UserFacilityServiceInformation;
use App\Models\CareReward;
use App\Models\CareRewardHistory;
use App\Models\UninsuredItem;
use App\Models\UninsuredItemHistory;
use App\Models\UninsuredRequest;
use App\Models\ServicePlan;
use App\Models\FirstServicePlan;
use App\Models\SecondServicePlan;
use App\Models\ServicePlanNeed;
use App\Models\ServiceLongPlan;
use App\Models\ServiceShortPlan;
use App\Models\ServicePlanSupport;
use App\Models\Invoice;
use App\Models\InvoiceDetail;

class WorkTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 作ったFactoryが動作するか確認するため
     *
     * @return void
     */
    public function run()
    {
        // factory(UserBenefitInformation::class)->create();
        // factory(UserCareInformation::class)->create();
        factory(ServicePlanNeed::class)->create();

    }
}
