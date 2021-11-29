<?php

namespace App\Policies\User;

use App\Db\Entity\User;
use App\Policies\BasePolicy;

class UserPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  User  $model
     * @return mixed
     */
    public function view(User $user, User $model)
    {
        return $user->isAdmin() || $user->id == $model->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isAdmin() || $user->isCustomer();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  User  $model
     * @return mixed
     */
    public function update(User $user, User $model)
    {
        return $user->isAdmin() || $user->id == $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  User  $model
     * @return mixed
     */
    public function delete(User $user, User $model)
    {
        return $user->isAdmin() || $user->id == $model->id;
    }

//    public function editRoles(User $user)
//    {
//        return $user->isAdmin();
//    }
}
