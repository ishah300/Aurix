<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Aurix\Models\Menu;
use Aurix\Models\Role;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class PermissionMatrixApiTest extends TestCase
{
    public function test_it_returns_permission_matrix_for_role(): void
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user);

        $role = Role::query()->where('slug', 'admin')->firstOrFail();

        $response = $this->getJson('/api/auth/roles/' . $role->id . '/permissions-matrix');

        $response->assertOk()
            ->assertJsonPath('role.slug', 'admin')
            ->assertJsonStructure([
                'role' => ['id', 'name', 'slug'],
                'matrix' => [
                    ['menu_id', 'menu_name', 'menu_slug', 'route', 'actions' => ['create', 'update', 'delete', 'edit']],
                ],
            ]);
    }

    public function test_it_updates_permission_matrix_and_returns_my_menu_access(): void
    {
        $admin = User::query()->create([
            'name' => 'Matrix Admin',
            'email' => 'matrix-admin@example.com',
            'password' => bcrypt('secret'),
        ]);

        $admin->assignRole('admin');
        $role = Role::query()->where('slug', 'editor')->firstOrFail();

        $menu = Menu::query()
            ->where(function ($q): void {
                $q->whereNull('route')->orWhere('route', 'not like', '/auth/rbac%');
            })
            ->orderBy('id')
            ->firstOrFail();

        $this->actingAs($admin);

        $updateResponse = $this->putJson('/api/auth/roles/' . $role->id . '/permissions-matrix', [
            'items' => [
                ['menu_id' => $menu->id, 'create' => true, 'update' => false, 'delete' => false, 'edit' => true],
            ],
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('message', 'Role permission matrix updated successfully.');

        $editor = User::query()->create([
            'name' => 'Matrix Editor',
            'email' => 'matrix-editor@example.com',
            'password' => bcrypt('secret'),
        ]);
        $editor->assignRole('editor');
        $this->actingAs($editor);

        $accessResponse = $this->getJson('/api/auth/me/menu-access');

        $accessResponse->assertOk();
        $this->assertSame($menu->slug, $accessResponse->json('data.0.menu_slug'));
        $this->assertTrue((bool) $accessResponse->json('data.0.actions.create'));
        $this->assertTrue((bool) $accessResponse->json('data.0.actions.edit'));
        $this->assertFalse((bool) $accessResponse->json('data.0.actions.update'));
    }

    public function test_it_exports_and_imports_permission_matrix(): void
    {
        $user = User::query()->create([
            'name' => 'Import Export User',
            'email' => 'impex@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('admin');
        $this->actingAs($user);

        $role = Role::query()->where('slug', 'editor')->firstOrFail();

        $exportJson = $this->getJson('/api/auth/roles/' . $role->id . '/permissions-matrix/export?format=json');
        $exportJson->assertOk()->assertJsonStructure(['role', 'matrix']);

        $exportCsv = $this->get('/api/auth/roles/' . $role->id . '/permissions-matrix/export?format=csv');
        $exportCsv->assertOk();
        $this->assertStringContainsString('menu_id,menu_slug,menu_name,route,create,update,delete,edit', (string) $exportCsv->getContent());

        $menu = Menu::query()
            ->where(function ($q): void {
                $q->whereNull('route')->orWhere('route', 'not like', '/auth/rbac%');
            })
            ->orderBy('id')
            ->firstOrFail();
        $import = $this->postJson('/api/auth/roles/' . $role->id . '/permissions-matrix/import', [
            'format' => 'json',
            'items' => [
                ['menu_id' => $menu->id, 'create' => false, 'update' => true, 'delete' => false, 'edit' => true],
            ],
        ]);

        $import->assertOk()->assertJsonPath('message', 'Role permission matrix imported successfully.');
    }

    public function test_it_rejects_updating_admin_role_matrix(): void
    {
        $user = User::query()->create([
            'name' => 'Matrix Guard User',
            'email' => 'matrix-guard@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('admin');
        $this->actingAs($user);

        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
        $menu = Menu::query()->where('slug', 'users')->firstOrFail();

        $this->putJson('/api/auth/roles/' . $adminRole->id . '/permissions-matrix', [
            'items' => [
                ['menu_id' => $menu->id, 'create' => false, 'update' => false, 'delete' => false, 'edit' => false],
            ],
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'This role has full access by default and cannot be edited here.');
    }

    public function test_it_rejects_assigning_rbac_system_menus_to_non_admin_roles(): void
    {
        $user = User::query()->create([
            'name' => 'Matrix System Guard',
            'email' => 'matrix-system-guard@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('admin');
        $this->actingAs($user);

        $editorRole = Role::query()->where('slug', 'editor')->firstOrFail();
        $systemMenu = Menu::query()->where('route', 'like', '/auth/rbac%')->orderBy('id')->firstOrFail();

        $this->putJson('/api/auth/roles/' . $editorRole->id . '/permissions-matrix', [
            'items' => [
                ['menu_id' => $systemMenu->id, 'create' => false, 'update' => false, 'delete' => false, 'edit' => true],
            ],
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'RBAC/system menus cannot be assigned to non-admin roles.');
    }
}
