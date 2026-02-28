<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Aurix\Models\SocialAccount;
use Aurix\Models\SocialProvider;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;

class SocialAuthFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed social providers
        SocialProvider::create([
            'slug' => 'facebook',
            'name' => 'Facebook',
            'is_active' => true,
            'enabled' => true,
            'client_id' => 'test-facebook-id',
            'client_secret' => encrypt('test-facebook-secret'),
            'redirect' => 'http://localhost/auth/facebook/callback',
        ]);

        SocialProvider::create([
            'slug' => 'google',
            'name' => 'Google',
            'is_active' => true,
            'enabled' => true,
            'client_id' => 'test-google-id',
            'client_secret' => encrypt('test-google-secret'),
            'redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    protected function mockSocialiteProvider(string $provider, SocialiteUser $socialiteUser)
    {
        $providerMock = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $providerMock->shouldReceive('user')->andReturn($socialiteUser);
        $providerMock->shouldReceive('scopes')->andReturnSelf();

        Socialite::shouldReceive('driver')->with($provider)->andReturn($providerMock);
    }

    public function test_redirect_to_provider_works(): void
    {
        $response = $this->get('/auth/facebook/redirect');

        $response->assertRedirect();
        $this->assertStringContainsString('facebook.com', $response->headers->get('Location'));
    }

    public function test_redirect_fails_when_provider_not_enabled(): void
    {
        SocialProvider::where('slug', 'facebook')->update(['enabled' => false]);

        $response = $this->get('/auth/facebook/redirect');

        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
    }

    public function test_redirect_fails_when_provider_missing_credentials(): void
    {
        SocialProvider::where('slug', 'facebook')->update(['client_id' => null]);

        $response = $this->get('/auth/facebook/redirect');

        $response->assertRedirect('/login');
    }

    public function test_callback_creates_new_user_from_social_profile(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456789');
        $socialiteUser->shouldReceive('getEmail')->andReturn('newuser@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('New User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
        $socialiteUser->token = 'mock-access-token';
        $socialiteUser->refreshToken = null;
        $socialiteUser->expiresIn = 3600;

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);
        $provider->shouldReceive('scopes')->andReturnSelf();

        Socialite::shouldReceive('driver')->with('facebook')->andReturn($provider);

        $response = $this->get('/auth/facebook/callback');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('New User', $user->name);

        $socialAccount = SocialAccount::where('provider', 'facebook')
            ->where('provider_id', '123456789')
            ->first();

        $this->assertNotNull($socialAccount);
        $this->assertEquals($user->id, $socialAccount->user_id);
        $this->assertEquals('newuser@example.com', $socialAccount->email);
    }

    public function test_callback_links_to_existing_user_with_same_email(): void
    {
        $existingUser = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('987654321');
        $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
        $socialiteUser->token = 'mock-access-token';

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($existingUser);

        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());

        $socialAccount = SocialAccount::where('provider', 'google')
            ->where('user_id', $existingUser->id)
            ->first();

        $this->assertNotNull($socialAccount);
    }

    public function test_callback_logs_in_existing_social_account(): void
    {
        $user = User::create([
            'name' => 'Social User',
            'email' => 'social@example.com',
            'password' => bcrypt('password'),
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => '111222333',
            'name' => 'Social User',
            'email' => 'social@example.com',
            'token' => 'old-token',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('111222333');
        $socialiteUser->shouldReceive('getEmail')->andReturn('social@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Social User Updated');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/new-avatar.jpg');
        $socialiteUser->token = 'new-access-token';

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('facebook')->andReturn($provider);

        $response = $this->get('/auth/facebook/callback');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $socialAccount = SocialAccount::where('provider', 'facebook')
            ->where('provider_id', '111222333')
            ->first();

        $this->assertEquals('Social User Updated', $socialAccount->name);
        $this->assertEquals('https://example.com/new-avatar.jpg', $socialAccount->avatar);
    }

    public function test_callback_handles_user_without_email(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('no-email-user');
        $socialiteUser->shouldReceive('getEmail')->andReturn(null);
        $socialiteUser->shouldReceive('getName')->andReturn('No Email User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn(null);
        $socialiteUser->token = 'mock-token';

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('facebook')->andReturn($provider);

        $response = $this->get('/auth/facebook/callback');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'like', '%@social.local')->first();
        $this->assertNotNull($user);
    }

    public function test_callback_redirects_to_custom_path(): void
    {
        Config::set('aurix.social.redirect_after_login', '/custom-dashboard');

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('custom-redirect-user');
        $socialiteUser->shouldReceive('getEmail')->andReturn('custom@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Custom User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn(null);
        $socialiteUser->token = 'mock-token';

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('facebook')->andReturn($provider);

        $response = $this->get('/auth/facebook/callback');

        $response->assertRedirect('/custom-dashboard');
    }

    public function test_callback_handles_oauth_errors_gracefully(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andThrow(new \Exception('OAuth error'));

        Socialite::shouldReceive('driver')->with('facebook')->andReturn($provider);

        $response = $this->get('/auth/facebook/callback');

        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_multiple_social_accounts_can_link_to_same_user(): void
    {
        $user = User::create([
            'name' => 'Multi Social User',
            'email' => 'multi@example.com',
            'password' => bcrypt('password'),
        ]);

        // Link Facebook
        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => 'fb-123',
            'email' => 'multi@example.com',
        ]);

        // Link Google
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-456');
        $socialiteUser->shouldReceive('getEmail')->andReturn('multi@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Multi Social User');
        $socialiteUser->shouldReceive('getAvatar')->andReturn(null);
        $socialiteUser->token = 'google-token';

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');

        $this->assertEquals(2, SocialAccount::where('user_id', $user->id)->count());
        $this->assertNotNull(SocialAccount::where('user_id', $user->id)->where('provider', 'facebook')->first());
        $this->assertNotNull(SocialAccount::where('user_id', $user->id)->where('provider', 'google')->first());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
