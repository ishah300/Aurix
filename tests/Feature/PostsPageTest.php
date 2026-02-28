<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Aurix\Models\Menu;
use Aurix\Models\Role;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class PostsPageTest extends TestCase
{
    public function test_user_with_posts_view_permission_can_open_posts_page(): void
    {
        $user = User::query()->create([
            'name' => 'Editor Viewer',
            'email' => 'editor-viewer@example.com',
            'password' => bcrypt('secret'),
        ]);

        $editor = Role::query()->where('slug', 'editor')->firstOrFail();
        $postsMenu = Menu::query()->where('slug', 'posts')->firstOrFail();

        $editor->syncMenuPermissions([
            ['menu_id' => $postsMenu->id, 'create' => false, 'update' => false, 'delete' => false, 'edit' => true],
        ]);

        $user->assignRole('editor');
        $this->actingAs($user);

        $this->get('/posts')
            ->assertOk()
            ->assertSee('Posts');
    }

    public function test_user_without_posts_view_permission_gets_forbidden(): void
    {
        $user = User::query()->create([
            'name' => 'Editor No View',
            'email' => 'editor-noview@example.com',
            'password' => bcrypt('secret'),
        ]);

        $editor = Role::query()->where('slug', 'editor')->firstOrFail();
        $postsMenu = Menu::query()->where('slug', 'posts')->firstOrFail();

        $editor->syncMenuPermissions([
            ['menu_id' => $postsMenu->id, 'create' => false, 'update' => false, 'delete' => false, 'edit' => false],
        ]);

        $user->assignRole('editor');
        $this->actingAs($user);

        $this->get('/posts')->assertForbidden();
    }
}
