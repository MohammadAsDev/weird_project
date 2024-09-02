<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClinicPolicy
{
    use HandlesAuthorization;

    private function isAdminOrStaff(User $user) {
        $role = $user->getRoleID();
        return $role == Role::ADMIN || $role == Role::STAFF;
    }

    private function isAdminOrStaffOrPatient(User $user) {
        $role = $user->getRoleID();
        return $role == Role::ADMIN || $role == Role::STAFF || $role == Role::PATIENT;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)     // patients should have access to all clinics
    {
        return $this->isAdminOrStaffOrPatient($user);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Clinic  $clinic
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Clinic $clinic) { // patients should have access to all clinics
        $owner_id = $clinic->doctor_id;
        return $owner_id == $user->id || $this->isAdminOrStaffOrPatient($user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user) {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Clinic  $clinic
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Clinic $clinic) {
        return $this->isAdminOrStaff($user);       
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Clinic  $clinic
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Clinic $clinic) {
        return $this->isAdminOrStaff($user);       
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Clinic  $clinic
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Clinic $clinic)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Clinic  $clinic
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Clinic $clinic)
    {
        //
    }
}
