<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DoctorPolicy
{
    use HandlesAuthorization;

    private function isAdminOrStaff(User $user) {
        $role = $user->getRoleID();
        $is_admin_or_staff = $role == Role::ADMIN || $role == Role::STAFF;
        return $is_admin_or_staff;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Determine whether the user can view Doctor's appointements.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Doctor  $doctor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAppointements(User $user , Doctor $doctor)
    {
        $is_owner = $user->id == $doctor->user_id;
        return $is_owner || $this->isAdminOrStaff($user);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Doctor  $doctor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Doctor $doctor)
    {
        $is_owner = $user->id == $doctor->user_id;
        return $this->isAdminOrStaff($user) || $is_owner;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Doctor  $doctor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Doctor $doctor)
    {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Doctor  $doctor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Doctor $doctor)
    {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Doctor  $doctor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Doctor $doctor)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Doctor  $doctor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Doctor $doctor)
    {
        //
    }
}
