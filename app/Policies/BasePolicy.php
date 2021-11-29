<?php

namespace App\Policies;

use App\Db\Entity\Question;
use App\Db\Entity\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Voyager politics permissions
     *
     * @param  User  $user
     * @return mixed
     */
    public function browse(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Voyager politics permissions
     *
     * @param  User  $user
     * @return mixed
     */
    public function read(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Voyager politics permissions
     *
     * @param  User  $user
     * @return mixed
     */
    public function add(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Voyager politics permissions
     *
     * @param  User  $user
     * @return mixed
     */
    public function edit(User $user, $record)
    {
        return $user->isAdmin();
    }
}
