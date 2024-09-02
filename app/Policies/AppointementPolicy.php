<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Appointement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointementPolicy
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

    private function isDoctorOrPatient(User $user) {
        $role = $user->getRoleID();
        return $role == Role::DOCTOR || $role == Role::PATIENT;    
    }

    private function isDoctor(User $user) {
        $role = $user->getRoleID();
        return $role == Role::DOCTOR;
    }

    private function isPatient(User $user) {
        $role = $user->getRoleID();
        return $role == Role::PATIENT;
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
     * Determine whether the user can view models as patient.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAsPatient(User $user)
    {
        return $this->isPatient($user);
    }


    /**
     * Determine whether the user can view models as doctor.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAsDoctor(User $user)
    {
        return $this->isDoctor($user);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Appointement  $appointement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Appointement $appointement)
    {
        $patient_id = $appointement->patient_id;
        $doctor_id = $appointement->doctor_id;
        $is_patient = $user->id == $patient_id;
        $is_doctor = $user->id == $doctor_id;
        return $this->isAdminOrStaff($user) || $is_patient || $is_doctor;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $this->isDoctorOrPatient($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Appointement  $appointement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Appointement $appointement)
    {
        $patient_id = $appointement->patient_id;
        $doctor_id = $appointement->doctor_id;

        $issued_patient = $user->id == $patient_id;
        $issued_doctor = $user->id == $doctor_id;
        
        return $this->isAdminOrStaff($user) || $issued_patient || $issued_doctor;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Appointement  $appointement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Appointement $appointement)      // apoitements should never be deleted
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Appointement  $appointement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Appointement $appointement)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Appointement  $appointement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Appointement $appointement)
    {
        //
    }
}
