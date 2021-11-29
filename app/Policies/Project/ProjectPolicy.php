<?php

namespace App\Policies\Project;

use App\Db\Entity\Project;
use App\Db\Entity\User;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Project  $project
     * @return mixed
     */
    public function view(User $user, Project $project)
    {
        return $user->isAdmin() || $user->isCustomer() && $project->user_id == $user->id;
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
     * @param  Project  $project
     * @return mixed
     */
    public function update(User $user, Project $project)
    {
        return $user->isAdmin() || $user->isCustomer() && $project->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Project  $project
     * @return mixed
     */
    public function delete(User $user, Project $project)
    {
        return $user->isAdmin() || $user->isCustomer() && $project->user_id == $user->id;
    }
}
