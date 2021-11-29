<?php

namespace App\Policies\Test;

use App\Db\Entity\Test;
use App\Db\Entity\User;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class TestPolicy extends BasePolicy
{

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Test  $test
     * @return mixed
     */
    public function view(User $user, Test $test)
    {
        return $user->isAdmin() || $user->isCustomer() && $test->user_id == $user->id || !$test->user_id;
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
     * @param  Test  $test
     * @return mixed
     */
    public function update(User $user, Test $test)
    {
        if (!$test->user_id)
        {
            // System test
            return false;
        }
        return $user->isAdmin() || $user->isCustomer() && $test->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Test  $test
     * @return mixed
     */
    public function delete(User $user, Test $test)
    {
        if (!$test->user_id)
        {
            // System test
            return false;
        }
        return $user->isAdmin() || $user->isCustomer() && $test->user_id == $user->id;
    }
}
