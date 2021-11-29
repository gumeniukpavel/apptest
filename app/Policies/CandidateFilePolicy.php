<?php

namespace App\Policies;

use App\Db\Entity\CandidateFile;
use App\Db\Entity\User;

class CandidateFilePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  CandidateFile  $CandidateFile
     * @return mixed
     */
    public function view(User $user, CandidateFile $CandidateFile)
    {
        return $user->isAdmin() || $user->isCustomer() && $CandidateFile->user_id == $user->id;
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
     * @param  CandidateFile  $CandidateFile
     * @return mixed
     */
    public function update(User $user, CandidateFile $CandidateFile)
    {
        return $user->isAdmin() || $user->isCustomer() && $CandidateFile->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  CandidateFile  $CandidateFile
     * @return mixed
     */
    public function delete(User $user, CandidateFile $CandidateFile)
    {
        return $user->isAdmin() || $user->isCustomer() && $CandidateFile->user_id == $user->id;
    }
}
