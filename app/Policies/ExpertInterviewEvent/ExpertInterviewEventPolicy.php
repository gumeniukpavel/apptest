<?php

namespace App\Policies\ExpertInterviewEvent;

use App\Db\Entity\ExpertInterviewEvent;
use App\Db\Entity\User;
use App\Policies\BasePolicy;

class ExpertInterviewEventPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  ExpertInterviewEvent  $expertInterviewEvent
     * @return mixed
     */
    public function view(User $user, ExpertInterviewEvent  $expertInterviewEvent)
    {
        return $user->isAdmin() || $user->isCustomer() && $expertInterviewEvent->user_id == $user->id;
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
