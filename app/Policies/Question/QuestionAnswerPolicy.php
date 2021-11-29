<?php

namespace App\Policies\Question;

use App\Db\Entity\QuestionAnswer;
use App\Db\Entity\User;
use App\Policies\BasePolicy;

class QuestionAnswerPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  QuestionAnswer  $reply
     * @return mixed
     */
    public function view(User $user, QuestionAnswer $answer)
    {
        return $user->isAdmin() || $user->isCustomer();
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
     * @param  QuestionAnswer  $reply
     * @return mixed
     */
    public function update(User $user, QuestionAnswer $reply)
    {
        return $user->isAdmin() || $user->isCustomer();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  QuestionAnswer  $reply
     * @return mixed
     */
    public function delete(User $user, QuestionAnswer $reply)
    {
        return $user->isAdmin() || $user->isCustomer();
    }

}
