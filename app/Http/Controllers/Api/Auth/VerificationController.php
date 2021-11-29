<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\BaseController;
use App\Providers\RouteServiceProvider;
use App\Service\AuthService;
use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct(AuthService $authService)
    {
        parent::__construct($authService);
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}
