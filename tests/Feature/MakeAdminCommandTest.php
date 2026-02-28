<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Aurix\Models\Role;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class MakeAdminCommandTest extends TestCase
{
    public function test_it_assigns_admin_role_to_existing_user(): void
    {
        $user = User::query()->create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('secret-123'),
        ]);

        $this->artisan('aurix:make-admin', ['email' => $user->email])
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->hasRole('admin'));
    }

    public function test_it_creates_user_and_assigns_admin_when_create_is_set(): void
    {
        $this->artisan('aurix:make-admin', [
            'email' => 'newadmin@example.com',
            '--create' => true,
            '--name' => 'New Admin',
            '--password' => 'password-123',
        ])->assertExitCode(0);

        $created = User::query()->where('email', 'newadmin@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('admin'));
        $this->assertNotNull(Role::query()->where('slug', 'admin')->first());
    }
}

