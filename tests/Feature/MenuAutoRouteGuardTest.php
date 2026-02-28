<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Aurix\Models\Menu;
use Aurix\Models\Role;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class MenuAutoRouteGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/categories-test', fn () => 'categories-ok');
        Route::middleware('web')->get('/categories-test/123/edit', fn () => 'categories-edit-ok');
        Route::middleware('web')->get('/categories-testimonials', fn () => 'categories-testimonials-ok');
    }

    public function test_auto_guard_blocks_route_without_menu_permission_even_if_route_has_no_permission_middleware(): void
    {
        Menu::query()->firstOrCreate(
            ['slug' => 'category-test'],
            ['name' => 'Category Test', 'route' => '/categories-test', 'sort_order' => 99, 'is_active' => true]
        );

        $user = User::query()->create([
            'name' => 'Editor No Category',
            'email' => 'editor-no-category@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('editor');

        $this->actingAs($user)
            ->get('/categories-test')
            ->assertForbidden();
    }

    public function test_auto_guard_allows_route_when_user_has_menu_view_permission(): void
    {
        $menu = Menu::query()->firstOrCreate(
            ['slug' => 'category-test'],
            ['name' => 'Category Test', 'route' => '/categories-test', 'sort_order' => 99, 'is_active' => true]
        );

        $role = Role::query()->where('slug', 'editor')->firstOrFail();
        $role->syncMenuPermissions([
            ['menu_id' => $menu->id, 'create' => false, 'update' => false, 'delete' => false, 'edit' => true],
        ]);

        $user = User::query()->create([
            'name' => 'Editor With Category',
            'email' => 'editor-with-category@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('editor');

        $this->actingAs($user)
            ->get('/categories-test')
            ->assertOk()
            ->assertSee('categories-ok');
    }

    public function test_auto_guard_blocks_nested_urls_for_the_same_menu_route(): void
    {
        Menu::query()->firstOrCreate(
            ['slug' => 'category-test'],
            ['name' => 'Category Test', 'route' => '/categories-test', 'sort_order' => 99, 'is_active' => true]
        );

        $user = User::query()->create([
            'name' => 'Editor Nested No Category',
            'email' => 'editor-nested-no-category@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('editor');

        $this->actingAs($user)
            ->get('/categories-test/123/edit')
            ->assertForbidden();
    }

    public function test_auto_guard_does_not_block_similar_prefix_routes(): void
    {
        Menu::query()->firstOrCreate(
            ['slug' => 'category-test'],
            ['name' => 'Category Test', 'route' => '/categories-test', 'sort_order' => 99, 'is_active' => true]
        );

        $user = User::query()->create([
            'name' => 'Editor Similar Prefix',
            'email' => 'editor-similar-prefix@example.com',
            'password' => bcrypt('secret'),
        ]);
        $user->assignRole('editor');

        $this->actingAs($user)
            ->get('/categories-testimonials')
            ->assertOk()
            ->assertSee('categories-testimonials-ok');
    }
}
