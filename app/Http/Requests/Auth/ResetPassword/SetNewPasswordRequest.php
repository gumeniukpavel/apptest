<?php

namespace App\Http\Requests\Auth\ResetPassword;

use App\Db\Entity\PasswordResetToken;
use App\Db\Entity\User;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

/**
 * SetNewPasswordRequest
 *
 * @property string $email
 * @property string $token
 * @property string $password
 */
class SetNewPasswordRequest extends ApiFormRequest
{
    public function rules()
    {
        return [
            'token' => [
                'required',
                Rule::exists(PasswordResetToken::class, 'token')
            ],
            'password' => 'required|min:6|max:255|required_with:passwordConfirmation|same:passwordConfirmation',
            'passwordConfirmation' => 'required|min:6',
        ];
    }
}
