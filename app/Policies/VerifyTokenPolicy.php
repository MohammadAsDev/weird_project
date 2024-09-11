<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\VerifyToken;
use Illuminate\Auth\Access\HandlesAuthorization;

class VerifyTokenPolicy
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
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\VerifyToken  $verifyToken
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, VerifyToken $verifyToken)
    {
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
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\VerifyToken  $verifyToken
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, VerifyToken $verifyToken)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\VerifyToken  $verifyToken
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, VerifyToken $verifyToken)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\VerifyToken  $verifyToken
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, VerifyToken $verifyToken)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\VerifyToken  $verifyToken
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, VerifyToken $verifyToken)
    {
        //
    }
}
