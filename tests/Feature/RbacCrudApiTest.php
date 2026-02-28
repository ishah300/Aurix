<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Aurix\Models\Menu;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class RbacCrudApiTest extends TestCase
{
    public function test_non_admin_cannot_access_rbac_management_endpoints(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => bcrypt('secret'),
        ]);

        $this->actingAs($user);

        $this->getJson('/api/auth/roles')->assertForbidden();
        $this->getJson('/api/auth/menus')->assertForbidden();
        $this->getJson('/api/auth/users')->assertForbidden();
    }

    public function test_role_crud_endpoints_work(): void
    {
        $user = User::query()->create([
            'name' => 'Crud User',
            'email' => 'crud@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user);

        $create = $this->postJson('/api/auth/roles', [
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => 'Manager role',
        ]);

        $create->assertCreated()->assertJsonPath('data.slug', 'manager');
        $roleId = (int) $create->json('data.id');

        $this->getJson('/api/auth/roles/' . $roleId)
            ->assertOk()
            ->assertJsonPath('data.id', $roleId);

        $this->putJson('/api/auth/roles/' . $roleId, [
            'name' => 'Manager Updated',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Manager Updated');

        $this->deleteJson('/api/auth/roles/' . $roleId)
            ->assertOk()
            ->assertJsonPath('message', 'Role deleted successfully.');

        $this->getJson('/api/auth/roles?q=adm&per_page=1&sort_by=name&sort_dir=asc')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_menu_crud_endpoints_work(): void
    {
        $user = User::query()->create([
            'name' => 'Menu User',
            'email' => 'menu@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user);

        $create = $this->postJson('/api/auth/menus', [
            'name' => 'Reports',
            'slug' => 'reports',
            'route' => '/reports',
            'sort_order' => 30,
            'is_active' => true,
        ]);

        $create->assertCreated()->assertJsonPath('data.slug', 'reports');
        $menuId = (int) $create->json('data.id');

        $this->putJson('/api/auth/menus/' . $menuId, [
            'icon' => 'chart',
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.icon', 'chart')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas(config('aurix.tables.menus', 'menus'), [
            'id' => $menuId,
            'slug' => 'reports',
        ]);

        $this->deleteJson('/api/auth/menus/' . $menuId)
            ->assertOk()
            ->assertJsonPath('message', 'Menu deleted successfully.');

        $this->assertDatabaseMissing(config('aurix.tables.menus', 'menus'), [
            'id' => $menuId,
        ]);

        $this->assertNotNull(Menu::query()->where('slug', 'users')->first());

        $this->getJson('/api/auth/menus?q=user&active=1&per_page=5')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_menu_reorder_endpoint_supports_parent_child_drag_payload(): void
    {
        $user = User::query()->create([
            'name' => 'Menu Drag User',
            'email' => 'menu-drag@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user);

        $parent = Menu::query()->create([
            'name' => 'Parent',
            'slug' => 'parent',
            'route' => '/parent',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $child = Menu::query()->create([
            'name' => 'Child',
            'slug' => 'child',
            'route' => '/child',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->putJson('/api/auth/menus/reorder', [
            'items' => [
                ['id' => $parent->id, 'parent_id' => null, 'sort_order' => 1],
                ['id' => $child->id, 'parent_id' => $parent->id, 'sort_order' => 1],
            ],
        ])->assertOk()->assertJsonPath('message', 'Menu order updated successfully.');

        $this->assertDatabaseHas(config('aurix.tables.menus', 'menus'), [
            'id' => $child->id,
            'parent_id' => $parent->id,
            'sort_order' => 1,
        ]);
    }

    public function test_user_crud_endpoints_work(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin-users@example.com',
            'password' => bcrypt('secret'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $create = $this->postJson('/api/auth/users', [
            'name' => 'Staff One',
            'email' => 'staff1@example.com',
            'password' => 'password123',
            'roles' => ['editor'],
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.email', 'staff1@example.com');
        $userId = (int) $create->json('data.id');

        $this->putJson('/api/auth/users/' . $userId, [
            'name' => 'Staff One Updated',
            'roles' => ['admin'],
        ])->assertOk()->assertJsonPath('data.name', 'Staff One Updated');

        $this->getJson('/api/auth/users?per_page=5')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $this->deleteJson('/api/auth/users/' . $userId)
            ->assertOk()
            ->assertJsonPath('message', 'User deleted successfully.');

        $this->deleteJson('/api/auth/users/' . $admin->id)
            ->assertStatus(422);
    }
}
