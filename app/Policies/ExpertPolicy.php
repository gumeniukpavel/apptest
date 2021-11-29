<?php

namespace App\Policies;

use App\Db\Entity\Expert;
use App\Db\Entity\User;

class ExpertPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Expert  $expert
     * @return mixed
     */
    public function view(User $user, Expert $expert)
    {
        return $user->isAdmin() || $user->isCustomer() && $expert->customer_id == $user->id;
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
     * @param  Expert  $expert
     * @return mixed
     */
    public function update(User $user, Expert $expert)
    {
        return $user->isAdmin() || $user->isCustomer() && $expert->customer_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Expert  $expert
     * @return mixed
     */
    public function delete(User $user, Expert $expert)
    {
        return $user->isAdmin() || $user->isCustomer() && $expert->customer_id == $user->id;
    }
}
