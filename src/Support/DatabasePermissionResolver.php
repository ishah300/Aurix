<?php

declare(strict_types=1);

namespace Aurix\Support;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Aurix\Contracts\PermissionResolver;

class DatabasePermissionResolver implements PermissionResolver
{
    public function __construct(private readonly Repository $config)
    {
    }

    public function hasRole(Authenticatable $user, string $role): bool
    {
        $roleSlugs = $this->resolveRoleSlugs($user);

        return $roleSlugs->contains($role);
    }

    public function hasPermission(Authenticatable $user, string $permission, array $arguments = []): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $cacheKey = $this->cacheKey($user, 'permissions');
        $permissionSlugs = cache()->remember(
            $cacheKey,
            now()->addSeconds((int) $this->config->get('aurix.rbac.cache_ttl_seconds', 300)),
            fn () => $this->resolvePermissionSlugs($user)
        );

        return $this->matchesPermission($permissionSlugs, $permission);
    }

    public function isSuperAdmin(Authenticatable $user): bool
    {
        $configured = (array) $this->config->get('aurix.rbac.super_admin_roles', []);
        $superAdminRoles = collect(array_merge(['admin', 'super-admin'], $configured))
            ->map(static fn ($role): string => strtolower((string) $role))
            ->unique()
            ->values();

        $roleSlugs = $this->resolveRoleSlugs($user);
        $roleSlugsNormalized = $roleSlugs
            ->map(static fn ($role): string => strtolower((string) $role))
            ->values();

        return $roleSlugsNormalized->intersect($superAdminRoles)->isNotEmpty();
    }

    private function resolveRoleSlugs(Authenticatable $user): Collection
    {
        $cacheKey = $this->cacheKey($user, 'roles');

        return cache()->remember(
            $cacheKey,
            now()->addSeconds((int) $this->config->get('aurix.rbac.cache_ttl_seconds', 300)),
            function () use ($user): Collection {
                $tables = $this->config->get('aurix.tables');

                return $this->db()
                    ->table($tables['user_roles'])
                    ->join($tables['roles'], $tables['roles'] . '.id', '=', $tables['user_roles'] . '.role_id')
                    ->where($tables['user_roles'] . '.user_id', $user->getAuthIdentifier())
                    ->pluck($tables['roles'] . '.slug')
                    ->map(static fn ($value): string => (string) $value)
                    ->values();
            }
        );
    }

    private function resolvePermissionSlugs(Authenticatable $user): Collection
    {
        $tables = $this->config->get('aurix.tables');

        $rolePermissions = $this->db()
            ->table($tables['user_roles'])
            ->join($tables['role_permissions'], $tables['role_permissions'] . '.role_id', '=', $tables['user_roles'] . '.role_id')
            ->join($tables['permissions'], $tables['permissions'] . '.id', '=', $tables['role_permissions'] . '.permission_id')
            ->where($tables['user_roles'] . '.user_id', $user->getAuthIdentifier())
            ->pluck($tables['permissions'] . '.slug')
            ->map(static fn ($value): string => (string) $value);

        $menuActionPermissions = $this->db()
            ->table($tables['user_roles'])
            ->join($tables['role_menu_permissions'], $tables['role_menu_permissions'] . '.role_id', '=', $tables['user_roles'] . '.role_id')
            ->join($tables['menus'], $tables['menus'] . '.id', '=', $tables['role_menu_permissions'] . '.menu_id')
            ->where($tables['user_roles'] . '.user_id', $user->getAuthIdentifier())
            ->get([
                $tables['menus'] . '.slug as menu_slug',
                $tables['role_menu_permissions'] . '.can_create',
                $tables['role_menu_permissions'] . '.can_update',
                $tables['role_menu_permissions'] . '.can_delete',
                $tables['role_menu_permissions'] . '.can_edit',
            ])
            ->flatMap(static function (object $row): array {
                $permissions = [];
                $menuSlug = (string) $row->menu_slug;

                if ((bool) $row->can_create) {
                    $permissions[] = $menuSlug . '.create';
                    $permissions[] = $menuSlug . '.insert';
                }
                if ((bool) $row->can_update) {
                    $permissions[] = $menuSlug . '.update';
                }
                if ((bool) $row->can_delete) {
                    $permissions[] = $menuSlug . '.delete';
                }
                if ((bool) $row->can_edit) {
                    $permissions[] = $menuSlug . '.edit';
                    $permissions[] = $menuSlug . '.view';
                }

                return $permissions;
            });

        return $rolePermissions
            ->merge($menuActionPermissions)
            ->unique()
            ->values();
    }

    private function matchesPermission(Collection $grantedPermissions, string $requested): bool
    {
        if ($grantedPermissions->contains($requested)) {
            return true;
        }

        foreach ($grantedPermissions as $permission) {
            if (! str_contains($permission, '*')) {
                continue;
            }

            $pattern = '/^' . str_replace(['\\*', '\\.'], ['.*', '\\.'], preg_quote($permission, '/')) . '$/';
            if (preg_match($pattern, $requested) === 1) {
                return true;
            }
        }

        return false;
    }

    private function cacheKey(Authenticatable $user, string $suffix): string
    {
        return 'aurix:' . $user->getAuthIdentifier() . ':' . $suffix;
    }

    private function db(): ConnectionInterface
    {
        return app('db')->connection((string) $this->config->get('aurix.database.connection'));
    }
}
