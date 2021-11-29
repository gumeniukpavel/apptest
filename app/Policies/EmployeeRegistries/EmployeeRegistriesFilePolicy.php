<?php

namespace App\Policies\EmployeeRegistries;

use App\Db\Entity\EmployeeRegistriesFile;
use App\Db\Entity\User;
use App\Policies\BasePolicy;

class EmployeeRegistriesFilePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  EmployeeRegistriesFile  $employeeRegistriesFile
     * @return mixed
     */
    public function view(User $user, EmployeeRegistriesFile  $employeeRegistriesFile)
    {
        return $user->isAdmin() || $user->isCustomer() && $employeeRegistriesFile->user_id == $user->id;
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
     * @param  EmployeeRegistriesFile  $employeeRegistriesFile
     * @return mixed
     */
    public function update(User $user, EmployeeRegistriesFile  $employeeRegistriesFile)
    {
        return $user->isAdmin() || $user->isCustomer() && $employeeRegistriesFile->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  EmployeeRegistriesFile  $employeeRegistriesFile
     * @return mixed
     */
    public function delete(User $user, EmployeeRegistriesFile  $employeeRegistriesFile)
    {
        return $user->isAdmin() || $user->isCustomer() && $employeeRegistriesFile->user_id == $user->id;
    }
}
