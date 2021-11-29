<?php

namespace App\Policies\News;

use App\Db\Entity\News;
use App\Db\Entity\User;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  News  $news
     * @return mixed
     */
    public function view(User $user, News $news)
    {
        return $user->isAdmin() || $user->isCustomer() || $user->isCandidate();
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
     * @param  News  $news
     * @return mixed
     */
    public function update(User $user, News $news)
    {
        return $user->isAdmin() || $user->isCustomer();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  News  $news
     * @return mixed
     */
    public function delete(User $user, News $news)
    {
        return $user->isAdmin() || $user->isCustomer();
    }


}
