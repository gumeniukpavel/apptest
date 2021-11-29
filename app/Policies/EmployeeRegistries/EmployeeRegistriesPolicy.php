<?php

namespace App\Policies\EmployeeRegistries;

use App\Db\Entity\EmployeeRegistries;
use App\Db\Entity\User;
use App\Policies\BasePolicy;

class EmployeeRegistriesPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User  $user
     * @param EmployeeRegistries $employeeRegistries
     * @return mixed
     */
    public function view(User $user, EmployeeRegistries $employeeRegistries)
    {
        return $user->isAdmin() || $user->isCustomer() && $employeeRegistries->user_id == $user->id;
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
     * @param  EmployeeRegistries $employeeRegistries
     * @return mixed
     */
    public function update(User $user, EmployeeRegistries $employeeRegistries)
    {
        return $user->isAdmin() || $user->isCustomer() && $employeeRegistries->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  EmployeeRegistries $employeeRegistries
     * @return mixed
     */
    public function delete(User $user, EmployeeRegistries $employeeRegistries)
    {
        return $user->isAdmin() || $user->isCustomer() && $employeeRegistries->user_id == $user->id;
    }
}
