<?php

namespace App\Policies;

use App\User;
use App\Models\WeeklyPlanDetail;
use Illuminate\Auth\Access\HandlesAuthorization;

class WeeklyPlanDetailPolicy
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
     * @param  \App\WeeklyPlanDetail  $weeklyPlanDetail
     * @return mixed
     */
    public function view(User $user, WeeklyPlanDetail $weeklyPlanDetail)
    {
        //
    }

    /**
     * Determine whether the user can create weekly plan details.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the weekly plan detail.
     *
     * @param  \App\User  $user
     * @param  \App\WeeklyPlanDetail  $weeklyPlanDetail
     * @return mixed
     */
    public function update(User $user, WeeklyPlanDetail $weeklyPlanDetail)
    {
        //
    }

    /**
     * Determine whether the user can delete the weekly plan detail.
     *
     * @param  \App\User  $user
     * @param  \App\WeeklyPlanDetail  $weeklyPlanDetail
     * @return mixed
     */
    public function delete(User $user, WeeklyPlanDetail $weeklyPlanDetail)
    {
        //
    }

    /**
     * Determine whether the user can restore the weekly plan detail.
     *
     * @param  \App\User  $user
     * @param  \App\WeeklyPlanDetail  $weeklyPlanDetail
     * @return mixed
     */
    public function restore(User $user, WeeklyPlanDetail $weeklyPlanDetail)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the weekly plan detail.
     *
     * @param  \App\User  $user
     * @param  \App\WeeklyPlanDetail  $weeklyPlanDetail
     * @return mixed
     */
    public function forceDelete(User $user, WeeklyPlanDetail $weeklyPlanDetail)
    {
        //
    }
}
