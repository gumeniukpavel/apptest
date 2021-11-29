<?php

namespace App\Http\Controllers\Api\Auth;

use App\Db\Entity\PasswordResetToken;
use App\Db\Entity\User;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\ResetPassword\SendResetPasswordEmailRequest;
use App\Http\Requests\Auth\ResetPassword\SetNewPasswordRequest;
use App\Notifications\Auth\PasswordResetNotification;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;

class ResetPasswordController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function sendPasswordResetEmail(SendResetPasswordEmailRequest $request)
    {
        $this->sendMail($request->email);
        return $this->json();
    }

    public function sendMail($email)
    {
        /** @var User $user */
        $user = User::query()->where('email', $email)->first();
        $token = $this->generateToken($email);
        $user->notify(new PasswordResetNotification($token));
    }

    public function generateToken($email)
    {
        /** @var PasswordResetToken $isOtherToken */
        $isOtherToken = PasswordResetToken::query()->where('email', $email)->first();

        if($isOtherToken)
        {
            return $isOtherToken->token;
        }

        $token = Str::random(80);
        $this->storeToken($token, $email);
        return $token;
    }

    public function storeToken($token, $email)
    {
        $passwordReset = new PasswordResetToken();
        $passwordReset->token = $token;
        $passwordReset->email = $email;
        $passwordReset->save();
    }

    public function setNewPassword(SetNewPasswordRequest $request)
    {
        /** @var PasswordResetToken $passwordReset */
        $passwordReset = PasswordResetToken::query()->where([
            'token' => $request->token
        ])->first();

        if ($passwordReset)
        {
            /** @var User $user */
            $user = User::query()->where('email', $passwordReset->email)->first();
            $user->password = $request->password;
            $user->save();

            PasswordResetToken::query()
                ->where('email', $passwordReset->email)
                ->delete();
            return $this->json();
        }
        else
        {
            return $this->jsonError();
        }
    }
}
