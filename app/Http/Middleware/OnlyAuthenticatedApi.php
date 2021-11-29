<?php

namespace App\Http\Middleware;

use App\Service\AuthService;
use Closure;
use Illuminate\Http\Request;

class OnlyAuthenticatedApi
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
        $isLoggedIn = $this->authService->getUser();
        // Этот метод срабатывает в случае если запрос не содержит Json
        if (!$request->expectsJson() && !$isLoggedIn)
        {
            return route('login');
        }
        if (!$isLoggedIn)
        {
            return response()->json(['message' => "В доступе отказано."], 403);
        }
        return $next($request);
    }
}
