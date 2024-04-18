<?php

namespace App\Providers;

use App\Models\ServicePlan;
use App\Policies\ServicePlanPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Route;
use App\Service\GroupHome\AuthExtentService;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $policies = [
        ServicePlan::class => ServicePlanPolicy::class
    ];

    /**
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
        Passport::tokensExpireIn(now()->addHours(1));
        Passport::refreshTokensExpireIn(now()->addDays(1));

        // api セグメントでないためここでルーティング
        Route::post('/oauth/token', [
            'uses' => '\App\Http\Controllers\Api\TokenController@issue',
            'as' => 'passport.token',
            'middleware' => [\App\Http\Middleware\RequestLog::class,'throttle']
        ]);

        //スタッフ情報タブ（権限設定）
        Gate::define('readStaff', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->authority['read'];
            }
            return false;
        });
        //請求タブ
        Gate::define('readRequest', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->request['read'];
            }
            return false;
        });
        //請求登録
        Gate::define('writeRequest', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->request['write'];
            }
            return false;
        });
        //請求削除
        Gate::define('deleteRequest', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->request['delete'];
            }
            return false;
        });
        //請求認証
        Gate::define('approveRequest', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->request['approve'];
            }
            return false;
        });
        //伝送タブ
        Gate::define('transmitRequest', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->request['transmit'];
            }
            return false;
        });

        //ケアプラン　登録
        Gate::define('writeCarePlan', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->care_plan['write'];
            }
            return false;
        });
        //ケアプラン　削除
        Gate::define('deleteCarePlan', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->care_plan['delete'];
            }
            return false;
        });
        //ケアプラン　確定
        Gate::define('decideCarePlan', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->care_plan['decide'];
            }
            return false;
        });

        //事業所タブ
        Gate::define('readFacility', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->facility['read'];
            }
            return false;
        });
        //事業所登録
        Gate::define('writeFacility', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->facility['write'];
            }
            return false;
        });
        //事業所削除
        Gate::define('deleteFacility', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->facility['delete'];
            }
            return false;
        });

        //利用者ユーザ1 登録
        Gate::define('writeFacilityUser1', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->facility_user_2['write'];
            }
            return false;
        });
        //利用者ユーザ1 削除
        Gate::define('deleteFacilityUser1', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->facility_user_2['delete'];
            }
            return false;
        });

        //利用者ユーザ2関係タブ
        Gate::define('readFacilityUser2', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->facility_user_2['read'];
            }
            return false;
        });
        //利用者ユーザ2 登録
        Gate::define('writeFacilityUser2', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->facility_user_2['write'];
            }
            return false;
        });
        //利用者ユーザ2 削除
        Gate::define('deleteFacilityUser2', function ($user) {
            if (is_null($user->staff_id)) {
                return false;
            }

            $authExtentService = new AuthExtentService();
            $autnExtents = $authExtentService->getAuthExtent($user->staff_id);
            foreach ($autnExtents as $value) {
                return $value->auth->facility_user_2['delete'];
            }
            return false;
        });
    }
}
