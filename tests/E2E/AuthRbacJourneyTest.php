<?php

declare(strict_types=1);

namespace Aurix\Tests\E2E;

use Aurix\Models\Menu;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class AuthRbacJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/reports', fn () => 'reports-ok');
    }

    public function test_admin_configures_role_and_user_can_access_menu_protected_route(): void
    {
        $admin = User::query()->create([
            'name' => 'E2E Admin',
            'email' => 'e2e-admin@example.com',
            'password' => bcrypt('secret'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $menuCreate = $this->postJson('/api/auth/menus', [
            'name' => 'Reports',
            'slug' => 'reports',
            'route' => '/reports',
            'sort_order' => 10,
            'is_active' => true,
        ]);
        $menuCreate->assertCreated();

        $roleCreate = $this->postJson('/api/auth/roles', [
            'name' => 'Analyst',
            'slug' => 'analyst',
            'description' => 'Reports consumer',
        ]);
        $roleCreate->assertCreated();
        $roleId = (int) $roleCreate->json('data.id');

        $menu = Menu::query()->where('slug', 'reports')->firstOrFail();
        $this->putJson('/api/auth/roles/' . $roleId . '/permissions-matrix', [
            'items' => [
                [
                    'menu_id' => $menu->id,
                    'create' => false,
                    'update' => false,
                    'delete' => false,
                    'edit' => true,
                ],
            ],
        ])->assertOk();

        $userCreate = $this->postJson('/api/auth/users', [
            'name' => 'E2E Analyst',
            'email' => 'e2e-analyst@example.com',
            'password' => 'password123',
            'roles' => ['analyst'],
        ]);
        $userCreate->assertCreated();

        $analyst = User::query()->where('email', 'e2e-analyst@example.com')->firstOrFail();
        $this->actingAs($analyst)
            ->get('/reports')
            ->assertOk()
            ->assertSee('reports-ok');
    }
}
