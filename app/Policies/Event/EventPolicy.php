<?php

namespace App\Policies\Event;

use App\Db\Entity\Event;
use App\Db\Entity\User;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Event  $event
     * @return mixed
     */
    public function view(User $user, Event $event)
    {
        return $user->isAdmin() || $user->isCustomer() && $event->user_id == $user->id;
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
     * @param  Event $event
     * @return mixed
     */
    public function update(User $user, Event $event)
    {
        return $user->isAdmin() || $user->isCustomer() && $event->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Event $event
     * @return mixed
     */
    public function delete(User $user, Event $event)
    {
        return $user->isAdmin() || $user->isCustomer() && $event->user_id == $user->id;
    }
}
