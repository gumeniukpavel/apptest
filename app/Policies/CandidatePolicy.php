<?php

namespace App\Policies;

use App\Db\Entity\Candidate;
use App\Db\Entity\User;

class CandidatePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Candidate  $candidate
     * @return mixed
     */
    public function view(User $user, Candidate $candidate)
    {
        return $user->isAdmin() || $user->isCustomer() && $candidate->customer_id == $user->id;
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
     * @param  Candidate  $candidate
     * @return mixed
     */
    public function update(User $user, Candidate $candidate)
    {
        return $user->isAdmin() || $user->isCustomer() && $candidate->customer_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Candidate  $candidate
     * @return mixed
     */
    public function delete(User $user, Candidate $candidate)
    {
        return $user->isAdmin() || $user->isCustomer() && $candidate->customer_id == $user->id;
    }
}
