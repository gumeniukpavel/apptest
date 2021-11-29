<?php

namespace App\Policies\Payment;

use App\Db\Entity\Payment;
use App\Db\Entity\Project;
use App\Db\Entity\User;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Payment  $payment
     * @return mixed
     */
    public function view(User $user, Payment  $payment)
    {
        return $user->isAdmin() || $user->isCustomer() && $payment->user_id == $user->id;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->isBookkeeper();
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
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->isAdmin() || $user->isBookkeeper();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->isAdmin();
    }
}
