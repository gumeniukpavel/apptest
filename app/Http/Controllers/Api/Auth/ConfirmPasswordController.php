<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\BaseController;
use App\Providers\RouteServiceProvider;
use App\Service\AuthService;
use Illuminate\Foundation\Auth\ConfirmsPasswords;

class ConfirmPasswordController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Confirm Password Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password confirmations and
    | uses a simple trait to include the behavior. You're free to explore
    | this trait and override any functions that require customization.
    |
    */

    use ConfirmsPasswords;

    /**
     * Where to redirect users when the intended url fails.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct(AuthService $authService)
    {
        parent::__construct($authService);
        $this->middleware('auth');
    }
}
