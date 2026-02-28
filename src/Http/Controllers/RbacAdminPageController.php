<?php

declare(strict_types=1);

namespace Aurix\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Throwable;
use Aurix\Models\Menu;
use Aurix\Models\Role;

class RbacAdminPageController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('aurix.rbac.setup');
    }

    public function setup(): View
    {
        $fullAccessRoleSlugs = collect(array_merge(
            ['admin', 'super-admin'],
            (array) config('aurix.rbac.super_admin_roles', [])
        ))
            ->map(static fn ($slug): string => strtolower((string) $slug))
            ->unique()
            ->values();

        $roles = Role::query()->get(['id', 'slug']);
        $adminRoleIds = $roles
            ->filter(fn (Role $role): bool => $fullAccessRoleSlugs->contains(strtolower((string) $role->slug)))
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $tables = config('aurix.tables');
        $connection = DB::connection(config('aurix.database.connection'));

        $adminUsersCount = $adminRoleIds === []
            ? 0
            : (int) $connection->table($tables['user_roles'])
                ->whereIn('role_id', $adminRoleIds)
                ->distinct('user_id')
                ->count('user_id');

        $activeMenusCount = (int) Menu::query()->where('is_active', true)->count();
        $nonAdminRolesCount = (int) $roles->filter(
            fn (Role $role): bool => ! $fullAccessRoleSlugs->contains(strtolower((string) $role->slug))
        )->count();

        $permissionRowsCount = (int) $connection->table($tables['role_menu_permissions'])
            ->where(function ($q): void {
                $q->where('can_create', true)
                    ->orWhere('can_update', true)
                    ->orWhere('can_delete', true)
                    ->orWhere('can_edit', true);
            })->count();

        $autoGuardEnabled = (bool) config('aurix.menus.auto_enforce_web_routes', true);

        $healthItems = [
            [
                'title' => 'Admin Bootstrap',
                'ok' => $adminUsersCount > 0,
                'detail' => $adminUsersCount > 0
                    ? $adminUsersCount . ' admin/super-admin user(s) found.'
                    : 'No admin user found. Run: php artisan aurix:make-admin your-email@example.com',
            ],
            [
                'title' => 'Menus Configured',
                'ok' => $activeMenusCount > 0,
                'detail' => $activeMenusCount . ' active menu(s) available.',
            ],
            [
                'title' => 'Business Roles',
                'ok' => $nonAdminRolesCount > 0,
                'detail' => $nonAdminRolesCount > 0
                    ? $nonAdminRolesCount . ' non-admin role(s) found.'
                    : 'No non-admin roles found yet.',
            ],
            [
                'title' => 'Role Permissions',
                'ok' => $permissionRowsCount > 0,
                'detail' => $permissionRowsCount > 0
                    ? $permissionRowsCount . ' menu permission row(s) assigned.'
                    : 'No role menu permissions assigned yet.',
            ],
            [
                'title' => 'Route Auto Guard',
                'ok' => $autoGuardEnabled,
                'detail' => $autoGuardEnabled
                    ? 'Automatic menu-based URL protection is enabled.'
                    : 'Route auto guard is disabled (AURIX_MENUS_AUTO_ENFORCE_WEB_ROUTES=false).',
            ],
        ];

        return $this->renderPage('aurix::rbac.partials.setup', [
            'rolesPageUrl' => route('aurix.rbac.roles'),
            'usersPageUrl' => route('aurix.rbac.users'),
            'menusPageUrl' => route('aurix.rbac.menus'),
            'appearancePageUrl' => route('aurix.rbac.appearance'),
            'postsPageUrl' => $this->postsPageUrl(),
            'setupPageUrl' => route('aurix.rbac.setup'),
            'healthItems' => $healthItems,
            'healthOkCount' => collect($healthItems)->where('ok', true)->count(),
            'healthTotalCount' => count($healthItems),
            'auditCommand' => 'php artisan aurix:audit-routes --fail-on-issues',
            'setupCommand' => 'php artisan aurix:setup --admin-email=you@example.com',
        ], 'aurix::rbac.layouts.standalone');
    }

    public function roles(): View
    {
        return $this->renderPage('aurix::rbac.partials.roles', [
            'setupPageUrl' => route('aurix.rbac.setup'),
            'appearancePageUrl' => route('aurix.rbac.appearance'),
            'rightsRouteTemplate' => route('aurix.rbac.rights', ['role' => '__ROLE__']),
            'usersPageUrl' => route('aurix.rbac.users'),
            'menusPageUrl' => route('aurix.rbac.menus'),
            'postsPageUrl' => $this->postsPageUrl(),
        ]);
    }

    public function rights(Role $role): View
    {
        $fullAccessRoles = collect(array_merge(
            ['admin', 'super-admin'],
            (array) config('aurix.rbac.super_admin_roles', [])
        ))
            ->map(static fn ($slug): string => strtolower((string) $slug))
            ->unique()
            ->values()
            ->all();

        return $this->renderPage('aurix::rbac.partials.rights', [
            'role' => $role,
            'setupPageUrl' => route('aurix.rbac.setup'),
            'appearancePageUrl' => route('aurix.rbac.appearance'),
            'rolesPageUrl' => route('aurix.rbac.roles'),
            'usersPageUrl' => route('aurix.rbac.users'),
            'menusPageUrl' => route('aurix.rbac.menus'),
            'postsPageUrl' => $this->postsPageUrl(),
            'isFullAccessRole' => in_array(strtolower((string) $role->slug), $fullAccessRoles, true),
        ]);
    }

    public function appearance(): View
    {
        $previewRoutes = [
            ['label' => 'Login', 'url' => route('aurix.rbac.appearance.preview', ['screen' => 'login'])],
            ['label' => 'Register', 'url' => route('aurix.rbac.appearance.preview', ['screen' => 'register'])],
            ['label' => 'Forgot Password', 'url' => route('aurix.rbac.appearance.preview', ['screen' => 'forgot'])],
        ];

        return $this->renderPage('aurix::rbac.partials.appearance', [
            'setupPageUrl' => route('aurix.rbac.setup'),
            'rolesPageUrl' => route('aurix.rbac.roles'),
            'usersPageUrl' => route('aurix.rbac.users'),
            'menusPageUrl' => route('aurix.rbac.menus'),
            'postsPageUrl' => $this->postsPageUrl(),
            'previewRoutes' => $previewRoutes,
        ], 'aurix::rbac.layouts.standalone');
    }

    public function appearancePreview(string $screen): View
    {
        $screen = strtolower(trim($screen));
        if (! in_array($screen, ['login', 'register', 'forgot'], true)) {
            $screen = 'login';
        }

        $viewName = $this->resolveAuthPreviewViewName($screen);
        if ($viewName !== null) {
            try {
                $view = view($viewName, [
                    'aurixPreviewMode' => true,
                    'aurixPreviewScreen' => $screen,
                ]);
                // Pre-render to validate components/routes used by the host view.
                $view->render();

                return $view;
            } catch (Throwable) {
                // Fall back to package preview when host auth views are unavailable/incompatible.
            }
        }

        return view('aurix::rbac.preview.auth-screen', [
            'screen' => $screen,
        ]);
    }

    public function users(): View
    {
        return $this->renderPage('aurix::rbac.partials.users', [
            'setupPageUrl' => route('aurix.rbac.setup'),
            'appearancePageUrl' => route('aurix.rbac.appearance'),
            'rolesPageUrl' => route('aurix.rbac.roles'),
            'menusPageUrl' => route('aurix.rbac.menus'),
            'postsPageUrl' => $this->postsPageUrl(),
        ]);
    }

    public function menus(): View
    {
        return $this->renderPage('aurix::rbac.partials.menus', [
            'setupPageUrl' => route('aurix.rbac.setup'),
            'appearancePageUrl' => route('aurix.rbac.appearance'),
            'rolesPageUrl' => route('aurix.rbac.roles'),
            'usersPageUrl' => route('aurix.rbac.users'),
            'postsPageUrl' => $this->postsPageUrl(),
        ]);
    }

    public function providers(): View
    {
        return $this->renderPage('aurix::rbac.partials.social-providers', [
            'setupPageUrl' => route('aurix.rbac.setup'),
            'appearancePageUrl' => route('aurix.rbac.appearance'),
            'rolesPageUrl' => route('aurix.rbac.roles'),
            'usersPageUrl' => route('aurix.rbac.users'),
            'menusPageUrl' => route('aurix.rbac.menus'),
            'postsPageUrl' => $this->postsPageUrl(),
        ], 'aurix::rbac.layouts.standalone');
    }

    private function renderPage(string $contentView, array $data = [], ?string $forcedLayoutView = null): View
    {
        $apiPrefix = '/' . trim((string) config('aurix.api.prefix', 'api/auth'), '/');
        $layoutView = $forcedLayoutView ?? 'aurix::rbac.layouts.standalone';

        if ($forcedLayoutView === null && view()->exists('components.app-layout')) {
            $layoutView = 'aurix::rbac.layouts.app-layout';
        } elseif ($forcedLayoutView === null && view()->exists('layouts.app') && $this->layoutUsesSlot()) {
            $layoutView = 'aurix::rbac.layouts.layouts-app-slot-component';
        } elseif ($forcedLayoutView === null && view()->exists('layouts.app')) {
            $layoutView = 'aurix::rbac.layouts.classic-layout';
        }

        return view('aurix::rbac.index', [
            'apiPrefix' => $apiPrefix,
            'title' => (string) config('aurix.ui.title', 'Aurix RBAC Manager'),
            'layoutView' => $layoutView,
            'contentView' => $contentView,
            ...$data,
        ]);
    }

    private function layoutUsesSlot(): bool
    {
        $layoutPath = resource_path('views/layouts/app.blade.php');

        if (! is_file($layoutPath)) {
            return false;
        }

        $contents = file_get_contents($layoutPath);

        return $contents !== false && str_contains($contents, '$slot');
    }

    private function postsPageUrl(): ?string
    {
        return Route::has('aurix.posts.index') ? route('aurix.posts.index') : null;
    }

    private function resolveAuthPreviewViewName(string $screen): ?string
    {
        $map = [
            'login' => 'auth.login',
            'register' => 'auth.register',
            'forgot' => 'auth.forgot-password',
        ];

        $view = $map[$screen] ?? null;
        if ($view === null || ! view()->exists($view)) {
            return null;
        }

        return $view;
    }
}
