<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Aurix\Models\Menu;
use Aurix\Models\Role;
use Aurix\Services\MenuAccessService;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class MenuTreeServiceTest extends TestCase
{
    public function test_tree_hides_child_when_parent_menu_is_not_in_access_list(): void
    {
        $parent = Menu::query()->create([
            'name' => 'Posts',
            'slug' => 'posts-parent-test',
            'route' => '#',
            'sort_order' => 100,
            'is_active' => true,
        ]);

        $child = Menu::query()->create([
            'name' => 'Category',
            'slug' => 'category-child-test',
            'route' => null,
            'sort_order' => 1,
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $role = Role::query()->where('slug', 'editor')->firstOrFail();
        $role->syncMenuPermissions([
            [
                'menu_id' => $child->id,
                'create' => false,
                'update' => false,
                'delete' => false,
                'edit' => true,
            ],
        ]);

        $user = User::query()->create([
            'name' => 'Tree User',
            'email' => 'tree-user@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('editor');

        /** @var MenuAccessService $service */
        $service = app(MenuAccessService::class);
        $tree = $service->treeForUser($user)->values();

        $posts = $tree->firstWhere('menu_slug', 'posts-parent-test');
        $childNode = $tree->firstWhere('menu_slug', 'category-child-test');

        $this->assertNull($posts, json_encode($tree->toArray(), JSON_PRETTY_PRINT));
        $this->assertNull($childNode, json_encode($tree->toArray(), JSON_PRETTY_PRINT));
    }
}
