<?php

namespace App\Http\Controllers\Api\Auth;

use App\Db\Entity\Role;
use App\Db\Entity\TariffUser;
use App\Db\Entity\User;
use App\Db\Service\UserDao;
use App\Http\Controllers\BaseController;
use App\Providers\RouteServiceProvider;
use App\Service\AuthService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

// to api methods
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Psy\Util\Str;

class LoginController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    protected UserDao $userDao;
    protected AuthService $authService;
    private string $env;
    protected string $frontUrl;

    protected $providers = [
        'google',
        'facebook'
    ];

    public function __construct(UserDao $userDao, AuthService $authService)
    {
        parent::__construct($authService);
        $this->frontUrl = config('app.front_url');
        $this->userDao = $userDao;
        $this->authService = $authService;
        $this->env = config('app.env');
    }

    // api method
    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request))
        {
            /** @var User $user */
            $user = $this->guard()->user();
            $authToken = $user->generateToken();
            $user->load('profile');
            $user->load('role');

            $user->apiToken = $authToken->token;
            return $this->json($user);
        }

        return $this->sendFailedLoginResponse($request);
    }

    public function logout()
    {
        if (!$this->authService->checkTokenAndLogin())
        {
            return $this->responseAccessDenied();
        }
        $user = $this->authService->getUser();
        if ($user)
        {
            $this->userDao->removeCurrentApiToken($user, $this->authService->getTokenForRequest());
        }
        $this->guard()->logout();

        return $this->json();
    }

    public function redirectToProvider($driver)
    {
        if (!$this->isProviderAllowed($driver))
        {
            Log::error("{$driver} is not currently supported");
        }

        try
        {
            return Socialite::driver($driver)->redirect();
        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage());
            return $e->getMessage();
        }
    }

    public function handleProviderCallback($driver)
    {
        $user = Socialite::driver($driver)->user();

        /** @var User $existingUser */
        $existingUser = User::query()->where('email', $user->getEmail())->first();

        if ($existingUser)
        {
            $existingUser->generateToken();
            $existingUser->load('profile');
            $existingUser->load('role');

            $this->guard()->login($existingUser);
            return redirect($this->frontUrl . '/login?token=' . $existingUser->apiToken);
        }
        else
        {
            $newUser = new User();

            if ($driver == 'google')
            {
                $newUser->name = $user->user['given_name'];
                $newUser->surname = isset($user->user['family_name']) ? $user->user['family_name'] : null;
            }
            else
            {
                list($name, $surname) = explode(' ', $user->getName());
                $newUser->name = $name;
                $newUser->surname = $surname;
            }
            $newUser->email = $user->getEmail();
            $newUser->password = bcrypt(\Illuminate\Support\Str::random(6));

            /** @var Role $role */
            $role = Role::byId(Role::ROLE_CUSTOMER);
            $newUser->role_id = $role->id;

            if ($newUser->save())
            {
                $newUser->generateToken();
                $newUser->load('profile');
                $newUser->load('role');

                $this->userDao->setPremiumTariffUser($newUser);

                $this->guard()->login($newUser);
                return redirect($this->frontUrl . '/login?token=' . $newUser->apiToken);
            }
        }
        return redirect('/');
    }

    private function isProviderAllowed($driver)
    {
        return in_array($driver, $this->providers) && config()->has("services.{$driver}");
    }
}
