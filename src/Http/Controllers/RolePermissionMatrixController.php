<?php

declare(strict_types=1);

namespace Aurix\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Aurix\Models\Menu;
use Aurix\Models\Role;

class RolePermissionMatrixController extends Controller
{
    public function show(Role $role): JsonResponse
    {
        $isFullAccessRole = $this->isFullAccessRole($role);
        $uiPath = $this->normalizedUiPath();
        $menus = Menu::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $assigned = $role->menuPermissions()
            ->get()
            ->keyBy('menu_id');

        $matrix = $menus->map(function (Menu $menu) use ($assigned, $isFullAccessRole, $uiPath): array {
            $pivot = $assigned->get($menu->id);
            $isSystemMenu = $this->isSystemMenu($menu->route, $uiPath);

            return [
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'menu_slug' => $menu->slug,
                'route' => $menu->route,
                'is_system_menu' => $isSystemMenu,
                'actions' => [
                    'create' => $isFullAccessRole ? true : (bool) ($pivot?->can_create ?? false),
                    'update' => $isFullAccessRole ? true : (bool) ($pivot?->can_update ?? false),
                    'delete' => $isFullAccessRole ? true : (bool) ($pivot?->can_delete ?? false),
                    'edit' => $isFullAccessRole ? true : (bool) ($pivot?->can_edit ?? false),
                ],
            ];
        })->values();

        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
            ],
            'matrix' => $matrix,
        ]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        if ($this->isFullAccessRole($role)) {
            return response()->json([
                'message' => 'This role has full access by default and cannot be edited here.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'exists:' . config('aurix.tables.menus', 'menus') . ',id'],
            'items.*.create' => ['nullable', 'boolean'],
            'items.*.update' => ['nullable', 'boolean'],
            'items.*.delete' => ['nullable', 'boolean'],
            'items.*.edit' => ['nullable', 'boolean'],
        ]);

        $validator->validate();

        /** @var array<int, array<string, mixed>> $items */
        $items = collect($request->input('items', []))
            ->map(static fn (array $row): array => [
                'menu_id' => $row['menu_id'],
                'create' => (bool) ($row['create'] ?? false),
                'update' => (bool) ($row['update'] ?? false),
                'delete' => (bool) ($row['delete'] ?? false),
                'edit' => (bool) ($row['edit'] ?? false),
            ])
            ->values()
            ->all();

        if ($this->containsSystemMenuAssignments($items)) {
            return response()->json([
                'message' => 'RBAC/system menus cannot be assigned to non-admin roles.',
            ], 422);
        }

        $role->syncMenuPermissions($items);

        return response()->json([
            'message' => 'Role permission matrix updated successfully.',
        ]);
    }

    private function isFullAccessRole(Role $role): bool
    {
        $fullAccessRoles = collect(array_merge(
            ['admin', 'super-admin'],
            (array) config('aurix.rbac.super_admin_roles', [])
        ))
            ->map(static fn ($slug): string => strtolower((string) $slug))
            ->unique()
            ->values()
            ->all();

        return in_array(strtolower((string) $role->slug), $fullAccessRoles, true);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function containsSystemMenuAssignments(array $items): bool
    {
        $itemsByMenu = collect($items)->keyBy(fn (array $row): int => (int) $row['menu_id']);
        $menuIds = $itemsByMenu->keys()->map(fn ($id): int => (int) $id)->all();

        if ($menuIds === []) {
            return false;
        }

        $uiPath = $this->normalizedUiPath();
        $menus = Menu::query()->whereIn('id', $menuIds)->get(['id', 'route']);

        foreach ($menus as $menu) {
            if (! $this->isSystemMenu($menu->route, $uiPath)) {
                continue;
            }

            $item = $itemsByMenu->get((int) $menu->id);
            if (! is_array($item)) {
                continue;
            }

            if ((bool) ($item['create'] ?? false) || (bool) ($item['update'] ?? false) || (bool) ($item['delete'] ?? false) || (bool) ($item['edit'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    private function normalizedUiPath(): string
    {
        return '/' . trim((string) config('aurix.ui.path', 'auth/rbac'), '/');
    }

    private function isSystemMenu(?string $route, string $uiPath): bool
    {
        $value = trim((string) ($route ?? ''));
        if ($value === '') {
            return false;
        }

        if (! str_starts_with($value, '/')) {
            $value = '/' . ltrim($value, '/');
        }

        return $value === $uiPath || str_starts_with($value, $uiPath . '/');
    }
}
