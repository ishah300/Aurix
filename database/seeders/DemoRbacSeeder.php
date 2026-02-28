<?php

declare(strict_types=1);

namespace Aurix\Database\Seeders;

use Illuminate\Database\Seeder;
use Aurix\Models\Menu;
use Aurix\Models\Post;
use Aurix\Models\Role;

class DemoRbacSeeder extends Seeder
{
    public function run(): void
    {
        $postsPath = '/' . trim((string) config('aurix.posts.path', 'posts'), '/');
        $rbacPath = '/' . trim((string) config('aurix.ui.path', 'auth/rbac'), '/');

        $admin = Role::query()->firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin', 'description' => 'Full admin role']
        );

        Role::query()->firstOrCreate(
            ['slug' => 'editor'],
            ['name' => 'Editor', 'description' => 'Content editor role']
        );

        $usersMenu = Menu::query()->firstOrCreate(
            ['slug' => 'users'],
            ['name' => 'Users', 'route' => $rbacPath . '/users', 'sort_order' => 10, 'is_active' => true]
        );

        $setupMenu = Menu::query()->firstOrCreate(
            ['slug' => 'setup'],
            ['name' => 'Setup', 'route' => $rbacPath . '/setup', 'sort_order' => 15, 'is_active' => true]
        );

        $appearanceMenu = Menu::query()->firstOrCreate(
            ['slug' => 'appearance'],
            ['name' => 'Appearance', 'route' => $rbacPath . '/appearance', 'sort_order' => 16, 'is_active' => true]
        );

        $rolesMenu = Menu::query()->firstOrCreate(
            ['slug' => 'roles'],
            ['name' => 'Roles', 'route' => $rbacPath . '/roles', 'sort_order' => 20, 'is_active' => true]
        );

        $menusMenu = Menu::query()->firstOrCreate(
            ['slug' => 'menus'],
            ['name' => 'Menus', 'route' => $rbacPath . '/menus', 'sort_order' => 30, 'is_active' => true]
        );

        $postsMenu = Menu::query()->firstOrCreate(
            ['slug' => 'posts'],
            ['name' => 'Posts', 'route' => $postsPath, 'sort_order' => 40, 'is_active' => true]
        );

        $admin->syncMenuPermissions([
            ['menu_id' => $usersMenu->id, 'create' => true, 'update' => true, 'delete' => true, 'edit' => true],
            ['menu_id' => $setupMenu->id, 'create' => true, 'update' => true, 'delete' => true, 'edit' => true],
            ['menu_id' => $appearanceMenu->id, 'create' => true, 'update' => true, 'delete' => true, 'edit' => true],
            ['menu_id' => $rolesMenu->id, 'create' => true, 'update' => true, 'delete' => true, 'edit' => true],
            ['menu_id' => $menusMenu->id, 'create' => true, 'update' => true, 'delete' => true, 'edit' => true],
            ['menu_id' => $postsMenu->id, 'create' => true, 'update' => true, 'delete' => true, 'edit' => true],
        ]);

        if (Post::query()->count() === 0) {
            Post::query()->create([
                'title' => 'Welcome to Aurix Posts',
                'slug' => 'welcome-to-aurix-posts',
                'content' => 'This is a seeded sample post. Assign posts permissions to roles to control access.',
                'is_published' => true,
                'published_at' => now(),
            ]);
            Post::query()->create([
                'title' => 'Editor Workflow',
                'slug' => 'editor-workflow',
                'content' => 'Editors can view and optionally insert/update posts depending on assigned menu actions.',
                'is_published' => true,
                'published_at' => now(),
            ]);
        }
    }
}
