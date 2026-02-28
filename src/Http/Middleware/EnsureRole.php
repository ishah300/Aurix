<?php

declare(strict_types=1);

namespace Aurix\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Aurix\Contracts\PermissionResolver;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function __construct(private readonly PermissionResolver $resolver)
    {
    }

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        foreach ($roles as $role) {
            if ($this->resolver->hasRole($user, $role)) {
                return $next($request);
            }
        }

        abort(403, 'This action requires a valid role.');
    }
}
