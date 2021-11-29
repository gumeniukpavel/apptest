<?php

namespace App\Policies\Question;

use App\Db\Entity\Question;
use App\Db\Entity\Test;
use App\Db\Entity\User;
use App\Policies\BasePolicy;
use Illuminate\Support\Collection;

class QuestionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Question  $question
     * @return mixed
     */
    public function view(User $user, Question $question)
    {
        /** @var Test | Collection */
        $test = $question->test;
        $isQuestionFromTariff = is_null($test->user_id);
        return $user->isAdmin()
            || $user->isCustomer() && $isQuestionFromTariff
            || $user->isCustomer() && $question->user_id == $user->id;
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
     * @param  Question  $question
     * @return mixed
     */
    public function update(User $user, Question $question)
    {
        if (!$question->user_id)
        {
            // System question
            return false;
        }
        return $user->isAdmin() || $user->isCustomer() && $question->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Question  $question
     * @return mixed
     */
    public function delete(User $user, Question $question)
    {
        if (!$question->user_id)
        {
            // System question
            return false;
        }
        return $user->isAdmin() || $user->isCustomer() && $question->user_id == $user->id;
    }
}
