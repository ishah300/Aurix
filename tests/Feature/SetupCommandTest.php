<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class SetupCommandTest extends TestCase
{
    public function test_setup_assigns_admin_to_existing_user(): void
    {
        $user = User::query()->create([
            'name' => 'Setup Existing',
            'email' => 'setup-existing@example.com',
            'password' => bcrypt('secret-123'),
        ]);

        $this->artisan('aurix:setup', [
            '--no-seed' => true,
            '--admin-email' => 'setup-existing@example.com',
        ])->assertExitCode(0);

        $this->assertTrue($user->fresh()->hasRole('admin'));
    }

    public function test_setup_can_create_admin_user(): void
    {
        $this->artisan('aurix:setup', [
            '--no-seed' => true,
            '--admin-email' => 'setup-new@example.com',
            '--create-admin' => true,
            '--admin-name' => 'Setup New Admin',
            '--admin-password' => 'password-123',
        ])->assertExitCode(0);

        $created = User::query()->where('email', 'setup-new@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('admin'));
    }
}

