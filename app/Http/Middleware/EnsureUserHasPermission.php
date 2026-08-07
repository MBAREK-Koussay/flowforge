<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        abort_unless($user !== null && $user->hasAnyPermission($permissions), Response::HTTP_FORBIDDEN, 'Missing required permission.');

        return $next($request);
    }
}