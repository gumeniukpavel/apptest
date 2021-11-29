<?php

namespace App\Policies\CompanyPosition;

use App\Db\Entity\CompanyPosition;
use App\Db\Entity\User;
use App\Policies\BasePolicy;

class CompanyPositionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User  $user
     * @param CompanyPosition $companyPosition
     * @return mixed
     */
    public function view(User $user, CompanyPosition $companyPosition)
    {
        return $user->isAdmin() || $user->isCustomer() && $companyPosition->user_id == $user->id;
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
     * @param  CompanyPosition $companyPosition
     * @return mixed
     */
    public function update(User $user, CompanyPosition $companyPosition)
    {
        return $user->isAdmin() || $user->isCustomer() && $companyPosition->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  CompanyPosition $companyPosition
     * @return mixed
     */
    public function delete(User $user, CompanyPosition $companyPosition)
    {
        return $user->isAdmin() || $user->isCustomer() && $companyPosition->user_id == $user->id;
    }
}
