<?php

namespace App\Policies;

use App\Db\Entity\ExpertFile;
use App\Db\Entity\User;

class ExpertImagePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  ExpertFile  $expertImage
     * @return mixed
     */
    public function view(User $user, ExpertFile $expertImage)
    {
        return $user->isAdmin() || $user->isCustomer() && $expertImage->user_id == $user->id;
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
     * @param  ExpertFile  $expertImage
     * @return mixed
     */
    public function update(User $user, ExpertFile $expertImage)
    {
        return $user->isAdmin() || $user->isCustomer() && $expertImage->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  ExpertFile  $expertImage
     * @return mixed
     */
    public function delete(User $user, ExpertFile $expertImage)
    {
        return $user->isAdmin() || $user->isCustomer() && $expertImage->user_id == $user->id;
    }
}
