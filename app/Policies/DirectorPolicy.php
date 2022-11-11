<?php

namespace App\Policies;

use App\Enums\RoleType;
use App\Models\Director;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DirectorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        if ($user->role->name === RoleType::SuperAdmin || $user->role->name === RoleType::Admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Director  $director
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Director $director)
    {
        dd($director);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        if ($user->role->name === RoleType::SuperAdmin || $user->role->name === RoleType::Admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Director  $director
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Director $director)
    {
        if ($user->role->name === RoleType::SuperAdmin || $user->role->name === RoleType::Admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Director  $director
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Director $director)
    {
        if (($user->role->name !== RoleType::SuperAdmin && $user->role->name !== RoleType::Admin) || $director->movies()->count() > 0) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Director  $director
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Director $director)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Director  $director
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Director $director)
    {
        //
    }
}
