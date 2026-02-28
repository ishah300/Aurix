<?php

declare(strict_types=1);

namespace Aurix\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MenuAccessService
{
    private const CACHE_VERSION_KEY = 'aurix:menu_access:version';

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function forUser(Authenticatable $user): Collection
    {
        $cacheKey = 'aurix:menu_access:v' . $this->cacheVersion() . ':' . $user->getAuthIdentifier();

        return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($user): Collection {
            $tables = config('aurix.tables');
            $fullAccessRoles = (array) config('aurix.rbac.super_admin_roles', ['admin', 'super-admin']);

            $userRoleSlugs = DB::connection(config('aurix.database.connection'))
                ->table($tables['user_roles'])
                ->join($tables['roles'], $tables['roles'] . '.id', '=', $tables['user_roles'] . '.role_id')
                ->where($tables['user_roles'] . '.user_id', $user->getAuthIdentifier())
                ->pluck($tables['roles'] . '.slug')
                ->map(static fn ($slug): string => (string) $slug);

            $isFullAccess = $userRoleSlugs->intersect($fullAccessRoles)->isNotEmpty();

            if ($isFullAccess) {
                $allMenus = DB::connection(config('aurix.database.connection'))
                    ->table($tables['menus'])
                    ->where($tables['menus'] . '.is_active', true)
                    ->orderBy($tables['menus'] . '.sort_order')
                    ->orderBy($tables['menus'] . '.id')
                    ->get([
                        $tables['menus'] . '.id as menu_id',
                        $tables['menus'] . '.name as menu_name',
                        $tables['menus'] . '.slug as menu_slug',
                        $tables['menus'] . '.route as route',
                        $tables['menus'] . '.icon as icon',
                        $tables['menus'] . '.sort_order as sort_order',
                        $tables['menus'] . '.parent_id as parent_id',
                    ]);

                return $allMenus->map(static function (object $row): array {
                    return [
                        'menu_id' => (int) $row->menu_id,
                        'menu_name' => (string) $row->menu_name,
                        'menu_slug' => (string) $row->menu_slug,
                        'route' => $row->route,
                        'icon' => $row->icon,
                        'sort_order' => (int) $row->sort_order,
                        'parent_id' => $row->parent_id !== null ? (int) $row->parent_id : null,
                        'actions' => [
                            'create' => true,
                            'update' => true,
                            'delete' => true,
                            'edit' => true,
                        ],
                    ];
                })->values();
            }

            $rows = DB::connection(config('aurix.database.connection'))
                ->table($tables['user_roles'])
                ->join($tables['role_menu_permissions'], $tables['role_menu_permissions'] . '.role_id', '=', $tables['user_roles'] . '.role_id')
                ->join($tables['menus'], $tables['menus'] . '.id', '=', $tables['role_menu_permissions'] . '.menu_id')
                ->where($tables['user_roles'] . '.user_id', $user->getAuthIdentifier())
                ->where($tables['menus'] . '.is_active', true)
                ->where(function ($q) use ($tables): void {
                    $q->where($tables['role_menu_permissions'] . '.can_create', true)
                        ->orWhere($tables['role_menu_permissions'] . '.can_update', true)
                        ->orWhere($tables['role_menu_permissions'] . '.can_delete', true)
                        ->orWhere($tables['role_menu_permissions'] . '.can_edit', true);
                })
                ->orderBy($tables['menus'] . '.sort_order')
                ->orderBy($tables['menus'] . '.id')
                ->get([
                    $tables['menus'] . '.id as menu_id',
                    $tables['menus'] . '.name as menu_name',
                    $tables['menus'] . '.slug as menu_slug',
                    $tables['menus'] . '.route as route',
                    $tables['menus'] . '.icon as icon',
                    $tables['menus'] . '.sort_order as sort_order',
                    $tables['menus'] . '.parent_id as parent_id',
                    $tables['role_menu_permissions'] . '.can_create',
                    $tables['role_menu_permissions'] . '.can_update',
                    $tables['role_menu_permissions'] . '.can_delete',
                    $tables['role_menu_permissions'] . '.can_edit',
                ]);

            return $rows
                ->groupBy('menu_id')
                ->map(function ($group): array {
                    $first = $group->first();

                    return [
                        'menu_id' => (int) $first->menu_id,
                        'menu_name' => (string) $first->menu_name,
                        'menu_slug' => (string) $first->menu_slug,
                        'route' => $first->route,
                        'icon' => $first->icon,
                        'sort_order' => (int) $first->sort_order,
                        'parent_id' => $first->parent_id !== null ? (int) $first->parent_id : null,
                        'actions' => [
                            'create' => $group->contains(fn ($row): bool => (bool) $row->can_create),
                            'update' => $group->contains(fn ($row): bool => (bool) $row->can_update),
                            'delete' => $group->contains(fn ($row): bool => (bool) $row->can_delete),
                            'edit' => $group->contains(fn ($row): bool => (bool) $row->can_edit),
                        ],
                    ];
                })
                ->values();
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function treeForUser(Authenticatable $user): Collection
    {
        $flat = $this->forUser($user)->map(function (array $item): array {
            $item['children'] = [];

            return $item;
        });

        /** @var array<int, array<string, mixed>> $byId */
        $byId = [];
        foreach ($flat as $item) {
            $byId[$item['menu_id']] = $item;
        }

        // Strict hierarchy: child menus are visible only when all ancestor menus are also present.
        $isVisibleNode = function (int $id, array &$visited = []) use (&$isVisibleNode, $byId): bool {
            if (! isset($byId[$id])) {
                return false;
            }

            if (isset($visited[$id])) {
                return false;
            }

            $visited[$id] = true;
            $parentId = $byId[$id]['parent_id'];

            if ($parentId === null) {
                return true;
            }

            if (! isset($byId[$parentId])) {
                return false;
            }

            return $isVisibleNode((int) $parentId, $visited);
        };

        /** @var array<int, array<string, mixed>> $visibleById */
        $visibleById = [];
        foreach (array_keys($byId) as $id) {
            $visited = [];
            if ($isVisibleNode((int) $id, $visited)) {
                $visibleById[(int) $id] = $byId[(int) $id];
            }
        }

        /** @var array<int, array<int, int>> $childrenByParent */
        $childrenByParent = [];
        foreach ($visibleById as $id => $item) {
            $parentKey = $item['parent_id'] !== null ? (int) $item['parent_id'] : 0;
            $childrenByParent[$parentKey] ??= [];
            $childrenByParent[$parentKey][] = (int) $id;
        }

        $buildNode = function (int $id) use (&$buildNode, $visibleById, $childrenByParent): array {
            $node = $visibleById[$id];
            $childIds = $childrenByParent[$id] ?? [];
            $node['children'] = array_map(
                static fn (int $childId): array => $buildNode($childId),
                $childIds
            );

            return $node;
        };

        $rootIds = [];
        foreach ($visibleById as $id => $item) {
            $parentId = $item['parent_id'];
            if ($parentId !== null && isset($visibleById[$parentId])) {
                continue;
            }
            $rootIds[] = (int) $id;
        }

        $roots = array_map(static fn (int $id): array => $buildNode($id), $rootIds);

        $sortFn = function (array $a, array $b): int {
            return ($a['sort_order'] <=> $b['sort_order']) ?: strcmp((string) $a['menu_name'], (string) $b['menu_name']);
        };

        $walkSort = function (array &$nodes) use (&$walkSort, $sortFn): void {
            usort($nodes, $sortFn);
            foreach ($nodes as &$node) {
                if (! empty($node['children'])) {
                    $walkSort($node['children']);
                }
            }
        };

        $walkSort($roots);

        return collect($roots);
    }

    public function can(Authenticatable $user, string $menuSlug, string $action): bool
    {
        $actions = ['view' => 'edit', 'insert' => 'create', 'update' => 'update', 'delete' => 'delete'];
        $mapped = $actions[strtolower($action)] ?? strtolower($action);

        $menu = $this->forUser($user)->firstWhere('menu_slug', $menuSlug);

        return is_array($menu) && (bool) ($menu['actions'][$mapped] ?? false);
    }

    public function bumpCacheVersion(): void
    {
        $store = cache()->getStore();

        if (method_exists($store, 'increment')) {
            $current = cache()->get(self::CACHE_VERSION_KEY);
            if ($current === null) {
                cache()->forever(self::CACHE_VERSION_KEY, 1);
            } else {
                cache()->increment(self::CACHE_VERSION_KEY);
            }

            return;
        }

        cache()->forever(self::CACHE_VERSION_KEY, (int) cache()->get(self::CACHE_VERSION_KEY, 1) + 1);
    }

    private function cacheVersion(): int
    {
        return (int) cache()->get(self::CACHE_VERSION_KEY, 1);
    }
}
