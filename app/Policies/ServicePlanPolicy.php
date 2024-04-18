<?php

namespace App\Policies;

use App\Authorization\FacilityUserAccessAuthorization;
use App\Models\Institution;
use App\User;
use App\Models\ServicePlan;
use App\Service\GroupHome\FacilityUserService;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePlanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any weekly plan details.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the weekly plan detail.
     *
     * @param  \App\User  $user
     * @param  \App\ServicePlan  $servicePlan
     * @return mixed
     */
    public function view(User $user, ServicePlan $servicePlan)
    {
        $authorization = new FacilityUserAccessAuthorization();
        return $authorization->can([$servicePlan->facility_user_id]);
    }

    /**
     * Determine whether the user can create weekly plan details.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
    }

    /**
     * Determine whether the user can update the weekly plan detail.
     *
     * @param  \App\User  $user
     * @param  \App\ServicePlan  $servicePlan
     * @return mixed
     */
    public function update(User $user, ServicePlan $servicePlan)
    {
        $authorization = new FacilityUserAccessAuthorization();
        return $authorization->can([$servicePlan->facility_user_id]);
    }

    /**
     * Determine whether the user can delete the weekly plan detail.
     *
     * @param  \App\User  $user
     * @param  \App\ServicePlan  $servicePlan
     * @return mixed
     */
    public function delete(User $user, ServicePlan $servicePlan)
    {
        $authorization = new FacilityUserAccessAuthorization();
        return $authorization->can([$servicePlan->facility_user_id]);
    }

    /**
     * Determine whether the user can restore the weekly plan detail.
     *
     * @param  \App\User  $user
     * @param  \App\ServicePlan  $servicePlan
     * @return mixed
     */
    public function restore(User $user, ServicePlan $servicePlan)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the weekly plan detail.
     *
     * @param  \App\User  $user
     * @param  \App\ServicePlan  $servicePlan
     * @return mixed
     */
    public function forceDelete(User $user, ServicePlan $servicePlan)
    {
        //
    }
}
