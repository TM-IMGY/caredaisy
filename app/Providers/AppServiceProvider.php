<?php

namespace App\Providers;

use App\Models\WeeklyPlanDetail;
use App\Models\WeeklyService;
use App\Observers\WeeklyPlanDetailObserver;
use App\Observers\WeeklyServiceObserver;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\CareRewardHistoryRepositoryInterface::class,
            \App\Lib\Repository\CareRewardHistoryRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityAdditionsRepositoryInterface::class,
            \App\Lib\Repository\FacilityAdditionsRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityRepositoryInterface::class,
            \App\Lib\Repository\FacilityRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUser\StayOutRecordRepositoryInterface::class,
            \App\Lib\Repository\FacilityUser\StayOutRecordRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserBenefitRecordRepositoryInterface::class,
            \App\Lib\Repository\FacilityUserBenefitRecordRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserCareRecordRepositoryInterface::class,
            \App\Lib\Repository\FacilityUserCareRecordRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserIndependenceRepositoryInterface::class,
            \App\Lib\Repository\FacilityUserIndependenceRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserPublicExpenseRecordRepositoryInterface::class,
            \App\Lib\Repository\FacilityUserPublicExpenseRecordRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserRegisterRepositoryInterface::class,
            \App\Lib\Repository\FacilityUserRegisterRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserRepositoryInterface::class,
            \App\Lib\Repository\FacilityUserRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\FacilityUserServiceRecordRepositoryInterface::class,
            \App\Lib\Repository\FacilityUserServiceRecordRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\InjuriesSicknessRepositoryInterface::class,
            \App\Lib\Repository\InjuriesSicknessRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\NationalHealthBillingRepositoryInterface::class,
            \App\Lib\Repository\NationalHealthBillingRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\ServiceCodeConditionalBranchRepositoryInterface::class,
            \App\Lib\Repository\ServiceCodeConditionalBranchRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\ServiceItemCodesRepositoryInterface::class,
            \App\Lib\Repository\ServiceItemCodesRepository::class
        );

        $this->app->singleton(
            \App\Lib\ApplicationBusinessRules\DataAccessInterface\SpecialMedicalCodesRepositoryInterface::class,
            \App\Lib\Repository\SpecialMedicalCodesRepository::class
        );

        $this->app->bind(
            \App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries\AutoServiceCodeGetInputBoundary::class,
            \App\Lib\ApplicationBusinessRules\UseCases\Interactors\AutoServiceCodeGetInteractor::class
        );

        $this->app->bind(
            \App\Lib\ApplicationBusinessRules\UseCases\InputBoundaries\GetFormInputBoundary::class,
            \App\Lib\ApplicationBusinessRules\UseCases\Interactors\GetFormInteractor::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        WeeklyService::observe(WeeklyServiceObserver::class);
        WeeklyPlanDetail::observe(WeeklyPlanDetailObserver::class);

        if (config('app.env') === 'testing') {
            return;
        }
        $url->forceScheme('https');
        $this->app['request']->server->set('HTTPS', 'on');//ペジネーションを行う際にhttpにURLを変更されpage移動が出来なくなる為使用。
    }
}
