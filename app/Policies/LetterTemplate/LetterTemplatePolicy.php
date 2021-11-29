<?php

namespace App\Policies\LetterTemplate;

use App\Db\Entity\LetterTemplate;
use App\Db\Entity\User;
use App\Policies\BasePolicy;

class LetterTemplatePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  LetterTemplate  $letterTemplate
     * @return mixed
     */
    public function view(User $user, LetterTemplate $letterTemplate)
    {
        return $user->isAdmin() || $user->isCustomer() && $letterTemplate->user_id == $user->id;
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
     * @param  LetterTemplate  $letterTemplate
     * @return mixed
     */
    public function update(User $user, LetterTemplate $letterTemplate)
    {
        return $user->isAdmin() || $user->isCustomer() && $letterTemplate->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  LetterTemplate  $letterTemplate
     * @return mixed
     */
    public function delete(User $user, LetterTemplate $letterTemplate)
    {
        return $user->isAdmin() || $user->isCustomer() && $letterTemplate->user_id == $user->id;
    }
}
