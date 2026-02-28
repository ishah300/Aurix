<?php

declare(strict_types=1);

namespace Aurix\Tests\Unit;

use Aurix\Models\SocialAccount;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class SocialAccountModelTest extends TestCase
{
    public function test_social_account_belongs_to_user(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => '123456',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(User::class, $socialAccount->user);
        $this->assertEquals($user->id, $socialAccount->user->id);
    }

    public function test_social_account_hides_sensitive_fields(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '789012',
            'token' => 'secret-access-token',
            'refresh_token' => 'secret-refresh-token',
        ]);

        $array = $socialAccount->toArray();

        $this->assertArrayNotHasKey('token', $array);
        $this->assertArrayNotHasKey('refresh_token', $array);
    }

    public function test_social_account_casts_expires_at_to_datetime(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $expiresAt = now()->addHours(2);

        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => '345678',
            'expires_at' => $expiresAt,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $socialAccount->expires_at);
        $this->assertEquals($expiresAt->timestamp, $socialAccount->expires_at->timestamp);
    }

    public function test_unique_constraint_on_provider_and_provider_id(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => 'unique-123',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => 'unique-123', // Duplicate
        ]);
    }

    public function test_cascade_delete_when_user_is_deleted(): void
    {
        // Enable foreign key constraints for SQLite
        \DB::statement('PRAGMA foreign_keys = ON');
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'twitter',
            'provider_id' => '999888',
        ]);

        $socialAccountId = $socialAccount->id;

        $user->delete();

        $this->assertNull(SocialAccount::find($socialAccountId));
    }
}
