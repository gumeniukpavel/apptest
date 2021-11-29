<?php

namespace App\Policies\Question;

use App\Db\Entity\Category;
use App\Db\Entity\User;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy extends BasePolicy
{    
    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Db\Entity\User  $user
     * @param  \App\Db\Entity\Category  $category
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->isAdmin() || $user->isCustomer();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Db\Entity\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isAdmin() || $user->isCustomer();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Db\Entity\User  $user
     * @param  \App\Db\Entity\Category  $category
     * @return mixed
     */
    public function update(User $user, Category $category)
    {
        return $user->isAdmin() || $user->isCustomer();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Db\Entity\User  $user
     * @param  \App\Db\Entity\Category  $category
     * @return mixed
     */
    public function delete(User $user, Category $category)
    {
        return $user->isAdmin() || $user->isCustomer();
    }

   
}
