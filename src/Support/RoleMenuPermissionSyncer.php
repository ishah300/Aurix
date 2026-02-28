<?php

declare(strict_types=1);

namespace Aurix\Support;

use Aurix\Models\Menu;
use Aurix\Models\Role;
use Aurix\Models\RoleMenuPermission;

class RoleMenuPermissionSyncer
{
    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function sync(Role $role, array $items): void
    {
        $menuIds = [];

        foreach ($items as $item) {
            $menuId = $this->resolveMenuId($item);
            if ($menuId === null) {
                continue;
            }

            $menuIds[] = $menuId;

            RoleMenuPermission::query()->updateOrCreate(
                [
                    'role_id' => $role->getKey(),
                    'menu_id' => $menuId,
                ],
                [
                    'can_create' => (bool) ($item['create'] ?? false),
                    'can_update' => (bool) ($item['update'] ?? false),
                    'can_delete' => (bool) ($item['delete'] ?? false),
                    'can_edit' => (bool) ($item['edit'] ?? false),
                ]
            );
        }

        if ($menuIds !== []) {
            RoleMenuPermission::query()
                ->where('role_id', $role->getKey())
                ->whereNotIn('menu_id', array_unique($menuIds))
                ->delete();
        }
    }

    /**
     * @param array<string, mixed> $item
     */
    private function resolveMenuId(array $item): ?int
    {
        if (isset($item['menu_id'])) {
            return (int) $item['menu_id'];
        }

        if (isset($item['menu_slug'])) {
            $menu = Menu::query()->where('slug', (string) $item['menu_slug'])->first();

            return $menu?->id;
        }

        return null;
    }
}
