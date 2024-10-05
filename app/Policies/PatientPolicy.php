<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatientPolicy
{
    use HandlesAuthorization;

    private function isAdminOrStaff(User $user) {
        $role = $user->getRoleID();
        $is_admin_or_staff = $role == Role::ADMIN || $role == Role::STAFF;
        return $is_admin_or_staff;
    }

    private function isOwner(User $user, Patient $patient) {
        $is_owner = $user->id == $patient->user_id;
        return $is_owner;
    }

    private function isAnonymous(User $user) {
        $role = $user->getRoleID();
        return $role == Role::ANONYMOUS;
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
     * Determine whether the user can view the patient's doctors.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewDoctors(User $user, Patient $patient)
    {
        return $this->isAdminOrStaff($user) || $this->isOwner($user , $patient);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Patient $patient)
    {
        return $this->isAdminOrStaff($user) || $this->isOwner($user , $patient);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Patient $patient)
    {
        return $this->isAdminOrStaff($user);    
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Patient $patient)
    {
        return $this->isAdminOrStaff($user);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Patient $patient)
    {
        //
    }

    /**
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function confirm(User $user)
    {
        return $this->isAnonymous($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Patient $patient)
    {
        //
    }
}
