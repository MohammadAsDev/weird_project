<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\RoutineTest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoutineTestPolicy
{
    use HandlesAuthorization;

    private function isAdminOrStaff(User $user) {
        $role = $user->getRoleID();
        return $role == Role::ADMIN || $role == Role::STAFF;
    }

    private function isDoctor(User $user) {
        $role = $user->getRoleID();
        return $role == Role::DOCTOR;
    }


    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */

    public function viewAny(User $user)
    {
        //
        return $this->isAdminOrStaff($user);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RoutineTest  $routineTest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, RoutineTest $routineTest)
    {
        //
        return $this->isAdminOrStaff($user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
        return $this->isDoctor($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RoutineTest  $routineTest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, RoutineTest $routineTest)
    {
        //
        return $this->isDoctor($user) && $routineTest->doctor_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RoutineTest  $routineTest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, RoutineTest $routineTest)    // no one can delete tests
    {
        //
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RoutineTest  $routineTest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, RoutineTest $routineTest)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RoutineTest  $routineTest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, RoutineTest $routineTest)
    {
        //
    }
}
