<?php

declare(strict_types=1);

namespace Aurix\Tests\Unit;

use Aurix\Models\SocialAccount;
use Aurix\Models\SocialProvider;
use Aurix\Services\SocialAuthService;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;

class SocialAuthServiceTest extends TestCase
{
    protected SocialAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SocialAuthService();
    }

    public function test_is_provider_enabled_returns_true_when_configured(): void
    {
        SocialProvider::create([
            'slug' => 'facebook',
            'name' => 'Facebook',
            'is_active' => true,
            'enabled' => true,
            'client_id' => 'test-id',
            'client_secret' => encrypt('test-secret'),
        ]);

        $this->assertTrue($this->service->isProviderEnabled('facebook'));
    }

    public function test_is_provider_enabled_returns_false_when_not_enabled(): void
    {
        SocialProvider::create([
            'slug' => 'google',
            'name' => 'Google',
            'is_active' => true,
            'enabled' => false,
            'client_id' => 'test-id',
            'client_secret' => encrypt('test-secret'),
        ]);

        $this->assertFalse($this->service->isProviderEnabled('google'));
    }

    public function test_is_provider_enabled_returns_false_when_missing_credentials(): void
    {
        SocialProvider::create([
            'slug' => 'github',
            'name' => 'GitHub',
            'is_active' => true,
            'enabled' => true,
            'client_id' => null,
            'client_secret' => null,
        ]);

        $this->assertFalse($this->service->isProviderEnabled('github'));
    }

    public function test_handle_callback_creates_new_user(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('new-user-123');
        $socialiteUser->shouldReceive('getEmail')->andReturn('newuser@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('New User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
        $socialiteUser->token = 'access-token';
        $socialiteUser->refreshToken = 'refresh-token';
        $socialiteUser->expiresIn = 3600;

        $user = $this->service->handleCallback('facebook', $socialiteUser);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('newuser@example.com', $user->email);
        $this->assertEquals('New User', $user->name);
        $this->assertNotNull($user->email_verified_at);

        $socialAccount = SocialAccount::where('provider', 'facebook')
            ->where('provider_id', 'new-user-123')
            ->first();

        $this->assertNotNull($socialAccount);
        $this->assertEquals($user->id, $socialAccount->user_id);
    }

    public function test_handle_callback_links_to_existing_user_by_email(): void
    {
        $existingUser = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('existing-123');
        $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn(null);
        $socialiteUser->token = 'token';

        $user = $this->service->handleCallback('google', $socialiteUser);

        $this->assertEquals($existingUser->id, $user->id);
        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());

        $this->assertTrue($user->hasSocialAccount('google'));
    }

    public function test_handle_callback_updates_existing_social_account(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => 'update-123',
            'name' => 'Old Name',
            'avatar' => 'old-avatar.jpg',
            'token' => 'old-token',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('update-123');
        $socialiteUser->shouldReceive('getEmail')->andReturn('user@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('New Name');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('new-avatar.jpg');
        $socialiteUser->token = 'new-token';

        $returnedUser = $this->service->handleCallback('facebook', $socialiteUser);

        $this->assertEquals($user->id, $returnedUser->id);

        $socialAccount = SocialAccount::where('provider', 'facebook')
            ->where('provider_id', 'update-123')
            ->first();

        $this->assertEquals('New Name', $socialAccount->name);
        $this->assertEquals('new-avatar.jpg', $socialAccount->avatar);
    }

    public function test_handle_callback_creates_user_without_email(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('no-email-123');
        $socialiteUser->shouldReceive('getEmail')->andReturn(null);
        $socialiteUser->shouldReceive('getName')->andReturn('No Email User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn(null);
        $socialiteUser->token = 'token';

        $user = $this->service->handleCallback('twitter', $socialiteUser);

        $this->assertInstanceOf(User::class, $user);
        $this->assertStringContainsString('@social.local', $user->email);
        $this->assertEquals('No Email User', $user->name);
    }

    public function test_handle_callback_stores_token_expiration(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('token-exp-123');
        $socialiteUser->shouldReceive('getEmail')->andReturn('token@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Token User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn(null);
        $socialiteUser->token = 'access-token';
        $socialiteUser->refreshToken = 'refresh-token';
        $socialiteUser->expiresIn = 7200; // 2 hours

        $user = $this->service->handleCallback('google', $socialiteUser);

        $socialAccount = $user->getSocialAccount('google');

        $this->assertNotNull($socialAccount->expires_at);
        $this->assertTrue($socialAccount->expires_at->isFuture());
        $this->assertEqualsWithDelta(
            now()->addSeconds(7200)->timestamp,
            $socialAccount->expires_at->timestamp,
            5
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
