<?php

namespace App\Http\Controllers\GroupHome\CarePlanInfo;

use App\Http\Controllers\Controller;

use App\Models\ServicePlan;
use App\Models\WeeklyPlanDetail;
use App\Models\WeeklyService;
use App\Models\WeeklyServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class ThirdServicePlanController extends Controller
{
    public function schedule(ServicePlan $servicePlan)
    {
        $this->authorize('view', $servicePlan);

        $schedules = $servicePlan->weeklyPlanDetails()->with('weeklyService')->get();

        return compact('schedules');
    }

    /**
     * 週間計画詳細
     */
    public function weeklyServiceMaster(Request $request)
    {
        $services = WeeklyServiceCategory::isGeneral()
            ->commonOrFacilityIs($request->facility_id)
            ->with(['weeklyServices' =>  function ($q) use ($request) {
                $q->commonOrFacilityIs($request->facility_id);
            }])
            ->get();

        return compact('services');
    }

    public function mainWorkServiceMaster(Request $request)
    {
        $services = WeeklyServiceCategory::isEveryday()
            ->commonOrFacilityIs($request->facility_id)
            ->with('weeklyServices')
            ->get();

        return compact('services');
    }

    /**
     * 週単位以外マスター
     */
    public function otherServiceMaster(Request $request)
    {
        $services = WeeklyService::isNotWeekly()->commonOrFacilityIs($request->facility_id)->get();

        return compact('services');
    }

    /**
     * 更新
     */
    public function update(Request $request, ServicePlan $servicePlan)
    {
        $this->authorize('update', $servicePlan);

        DB::beginTransaction();
        try {
            // 週単位更新
            WeeklyPlanDetail::upsertManyWeekly($request->weekly, $servicePlan, $request->facility_id);

            // 主な日常生活更新
            WeeklyPlanDetail::upsertManyMainWork($request->mainAction, $servicePlan, $request->facility_id);

            // 週単位以外更新
            WeeklyPlanDetail::upsertOtherService($request->otherService, $servicePlan, $request->facility_id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            logger($e);
            return ['result' => false];
        }

        return ['result' => true];
    }

    /**
     *
     * */
    public function pdf(Request $request, ServicePlan $servicePlan)
    {
        $schedules = $servicePlan->weeklyPlanDetails()->with('weeklyService')->get();
        $username = $servicePlan->facilityUser->last_name.$servicePlan->facilityUser->first_name;
        $title = '週間サービス計画表_'.$username;
        //return view('components/group_home/care_plan_info/service_plan3_pdf', compact('schedules', 'servicePlan'));

        return PDF::loadView('components/group_home/care_plan_info/service_plan3_pdf', compact('schedules', 'servicePlan', 'title'))
            ->setOption('encoding', 'utf-8')
            ->setOption('user-style-sheet', public_path(). '/css/group_home/care_plan_info/service_plan3_pdf.css')
            ->setPaper('A4', "landscape")
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 5)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('footer-font-size', 12)
            ->setOption('footer-center', "[page]/[topage]ページ")
            ->inline("週間サービス計画表_{$username}.pdf");
    }
}
