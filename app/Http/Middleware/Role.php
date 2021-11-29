<?php

namespace App\Http\Middleware;

use App\Service\AuthService;
use Closure;
use Illuminate\Http\Request;

class Role
{
    private AuthService $authService;

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
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $this->authService->getUser();
        if (!$request->expectsJson() && !$user)
        {
            return route('login');
        }

        if(!in_array($user->role->name, $roles))
        {
            return response()->json(['message' => "В доступе отказано."], 403);
        }

        return $next($request);
    }
}
