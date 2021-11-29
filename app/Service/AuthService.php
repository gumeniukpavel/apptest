<?php

namespace App\Service;

use App\Db\Entity\User;
use App\Db\Service\UserDao;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthService
{
    const API_TOKEN_NAME = 'api_token';

    /** @var User */
    private $user;

    /** @var UserDao */
    private $userDao;

    public function __construct(UserDao $userDao)
    {
        $this->userDao = $userDao;
    }

    public function checkTokenAndLogin(): bool
    {
        $token = $this->getTokenForRequest();
        if (!empty($token))
        {
            $this->user = $this->userDao->getByApiToken($token);
            if ($this->user)
            {
                $this->activeGuard()->setUser($this->user);
            }
            else
            {
                Log::error("Token found $token but user not");
            }
            return !!($this->user);
        }
        return false;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function isLoggedIn(): bool
    {
        return boolval($this->user);
    }


    public function check(): bool
    {
        $activeGuard = $this->activeGuard();
        return $activeGuard ? $activeGuard->check() : false;
    }

    /** @returns User | null */
    public function user(): ?Authenticatable
    {
        $activeGuard = $this->activeGuard();
        return $activeGuard ? $activeGuard->user() : null;
    }

    /**
     * @return Guard|StatefulGuard|null
     */
    public function activeGuard(): ?Guard
    {
        // Так как мы не знаем в какой из guard пользователь авторизирован то найдем его
        $defaultGuard = Auth::guard();
        if ($defaultGuard->check())
        {
            return $defaultGuard;
        }
        $apiGuard = Auth::guard('api');
        return $apiGuard ? $apiGuard : $defaultGuard;
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenForRequest()
    {
        // Request should be got in this place because tests is failing
        /** @var Request $request */
        $request = app()->make(Request::class);
        $token = $request->query(self::API_TOKEN_NAME);
        if (empty($token)) {
            $token = $request->input(self::API_TOKEN_NAME);
        }
        if (empty($token)) {
            $token = $request->bearerToken();
        }
        if (empty($token)) {
            $token = $request->getPassword();
        }
        return $token;
    }
}
