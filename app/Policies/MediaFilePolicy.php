<?php

namespace App\Policies;

use App\Db\Entity\MediaFile;
use App\Db\Entity\User;

class MediaFilePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  MediaFile  $mediaFile
     * @return mixed
     */
    public function view(User $user, MediaFile $mediaFile)
    {
        return $user->isAdmin() || $user->isCustomer() && $mediaFile->user_id == $user->id;
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
     * @param  MediaFile  $mediaFile
     * @return mixed
     */
    public function update(User $user, MediaFile $mediaFile)
    {
        return $user->isAdmin() || $user->isCustomer() && $mediaFile->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  MediaFile  $mediaFile
     * @return mixed
     */
    public function delete(User $user, MediaFile $mediaFile)
    {
        return $user->isAdmin() || $user->isCustomer() && $mediaFile->user_id == $user->id;
    }
}
