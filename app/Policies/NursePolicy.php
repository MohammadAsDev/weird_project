<?php

namespace App\Policies;

use App\Models\Nurse;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Enums\Role;

class NursePolicy
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
        $role = $user->getRoleID();
        $is_admin_or_staff = $role == Role::ADMIN || $role == Role::STAFF;
        return $is_admin_or_staff;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Nurse  $nurse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Nurse $nurse)
    {
        $role = $user->getRoleID();
        $is_admin_or_staff = $role == Role::ADMIN || $role == Role::STAFF;
        $is_owner = $user->id == $nurse->user_id;
        return $is_admin_or_staff || $is_owner;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        $role = $user->getRoleID();
        $is_admin_or_staff = $role == Role::ADMIN || $role == Role::STAFF;
        return $is_admin_or_staff;    
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Nurse  $nurse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Nurse $nurse)
    {
        $role = $user->getRoleID();
        $is_admin_or_staff = $role == Role::ADMIN || $role == Role::STAFF;
        return $is_admin_or_staff;    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Nurse  $nurse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Nurse $nurse)
    {
        $role = $user->getRoleID();
        $is_admin_or_staff = $role == Role::ADMIN || $role == Role::STAFF;
        return $is_admin_or_staff;   
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Nurse  $nurse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Nurse $nurse)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Nurse  $nurse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Nurse $nurse)
    {
        //
    }
}
