<?php

namespace App\Policies\UserEvent;

use App\Db\Entity\User;
use App\Db\Entity\UserEvent;
use App\Policies\BasePolicy;

class UserEventPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  UserEvent  $userEvent
     * @return mixed
     */
    public function view(User $user, UserEvent  $userEvent)
    {
        return $user->isAdmin() || $user->isCustomer() && $userEvent->user_id == $user->id;
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
}
