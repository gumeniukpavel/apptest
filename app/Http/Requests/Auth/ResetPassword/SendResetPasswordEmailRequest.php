<?php

namespace App\Http\Requests\Auth\ResetPassword;

use App\Db\Entity\Category;
use App\Db\Entity\Test;
use App\Db\Entity\User;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

/**
 * SendResetPasswordEmailRequest
 *
 * @property string $email
 */
class SendResetPasswordEmailRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'email' => [
                'required',
                Rule::exists(User::class, 'email')
            ],
        ];
    }
}
