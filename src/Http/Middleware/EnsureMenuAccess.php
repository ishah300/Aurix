<?php

declare(strict_types=1);

namespace Aurix\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Aurix\Contracts\PermissionResolver;
use Aurix\Services\MenuAccessService;
use Symfony\Component\HttpFoundation\Response;

class EnsureMenuAccess
{
    public function __construct(
        private readonly MenuAccessService $menuService,
        private readonly PermissionResolver $resolver
    ) {
    }

    public function handle(Request $request, Closure $next, string $menuSlug, string $action = 'view'): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        // Check if user is super admin
        $superAdminRoles = (array) config('aurix.rbac.super_admin_roles', ['admin', 'super-admin']);
        foreach ($superAdminRoles as $role) {
            if ($this->resolver->hasRole($user, (string) $role)) {
                return $next($request);
            }
        }

        // Check menu permission
        if ($this->menuService->can($user, $menuSlug, $action)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}
