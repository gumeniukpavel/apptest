<?php

namespace App\Policies\SystemSetting;

use App\Db\Entity\SystemSetting;
use App\Db\Entity\User;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class SystemSettingPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  SystemSetting  $systemSetting
     * @return mixed
     */
    public function view(User $user, SystemSetting $systemSetting)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  SystemSetting  $systemSetting
     * @return mixed
     */
    public function update(User $user, SystemSetting $systemSetting)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  SystemSetting  $systemSetting
     * @return mixed
     */
    public function delete(User $user, SystemSetting $systemSetting)
    {
        return $user->isAdmin();
    }

}
