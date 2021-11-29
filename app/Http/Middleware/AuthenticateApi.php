<?php

namespace App\Http\Middleware;

use App\Service\AuthService;
use Closure;
use Illuminate\Http\Request;

class AuthenticateApi
{
    /** @var AuthService */
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next)
    {
        $this->authService->checkTokenAndLogin();
        return $next($request);
    }
}
