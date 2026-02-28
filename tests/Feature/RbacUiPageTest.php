<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Aurix\Models\Role;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class RbacUiPageTest extends TestCase
{
    public function test_admin_can_open_rbac_ui_pages(): void
    {
        $user = User::query()->create([
            'name' => 'Admin UI User',
            'email' => 'admin-ui@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('admin');

        $this->actingAs($user);

        $this->get('/auth/rbac')
            ->assertRedirect('/auth/rbac/setup');

        $this->get('/auth/rbac/setup')
            ->assertOk()
            ->assertSee('Aurix Setup')
            ->assertSee('Setup Health')
            ->assertSee('CLI Quick Start');

        $this->get('/auth/rbac/appearance')
            ->assertOk()
            ->assertSee('Appearance')
            ->assertSee('Logo')
            ->assertSee('Background');

        $this->get('/auth/rbac/roles')
            ->assertOk()
            ->assertSee('Roles')
            ->assertSee('Role Name')
            ->assertSee('Access Rights');

        $role = Role::query()->where('slug', 'admin')->firstOrFail();

        $this->get('/auth/rbac/roles/' . $role->id . '/rights')
            ->assertOk()
            ->assertSee('Assign rights to a role')
            ->assertSee('View')
            ->assertSee('Insert')
            ->assertSee('Update')
            ->assertSee('Delete');

        $this->get('/auth/rbac/users')
            ->assertOk()
            ->assertSee('Users')
            ->assertSee('Name');

        $this->get('/auth/rbac/menus')
            ->assertOk()
            ->assertSee('Menus')
            ->assertSee('Slug');
    }

    public function test_non_admin_cannot_open_rbac_ui_pages(): void
    {
        $user = User::query()->create([
            'name' => 'Regular UI User',
            'email' => 'regular-ui@example.com',
            'password' => bcrypt('secret'),
        ]);

        $this->actingAs($user);

        $this->get('/auth/rbac')->assertForbidden();
        $this->get('/auth/rbac/setup')->assertForbidden();
        $this->get('/auth/rbac/appearance')->assertForbidden();
        $this->get('/auth/rbac/roles')->assertForbidden();
        $this->get('/auth/rbac/roles/1/rights')->assertForbidden();
        $this->get('/auth/rbac/users')->assertForbidden();
        $this->get('/auth/rbac/menus')->assertForbidden();
    }
}
