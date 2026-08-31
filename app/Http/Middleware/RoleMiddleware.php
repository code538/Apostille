<?php

namespace App\Http\Middleware;

use App\Services\ApiResponseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {
        $user = $request->user();

        if (! $user) {
            return $this->response->error(
                'Unauthenticated.',
                401
            );
        }

        if (! $user->hasAnyRole($roles)) {
            return $this->response->error(
                'You do not have permission to access this resource.',
                403
            );
        }

        return $next($request);
    }
}
