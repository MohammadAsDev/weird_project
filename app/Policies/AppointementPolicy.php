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

    private function isDoctorOrPatient(User $user) {
        return $this->isDoctor($user) || $this->isPatient($user);    
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
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAnyAsPatient(User $user)
    {
        return $this->isPatient($user);
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAnyAsDoctor(User $user)
    {
        return $this->isDoctor($user);
    }


    /**
     * Determine whether the user can view models as patient.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAsPatient(User $user , Appointement $appointement)
    {
        $patient_id = $appointement->patient_id;
        return $this->isPatient($user) && $user->id == $patient_id;
    }


    /**
     * Determine whether the user can view models as doctor.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAsDoctor(User $user , Appointement $appointement)
    {
        $doctor_id = $appointement->doctor_id;
        return $this->isDoctor($user) && $user->id == $doctor_id;
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

        $issued_patient = $user->id == $patient_id && $this->isPatient($user);
        $issued_doctor = $user->id == $doctor_id && $this->isDoctor($user);
        
        return $this->isAdminOrStaff($user) || $issued_patient || $issued_doctor;
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

        $issued_patient = $user->id == $patient_id && $this->isPatient($user);
        $issued_doctor = $user->id == $doctor_id && $this->isDoctor($user);
        
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
