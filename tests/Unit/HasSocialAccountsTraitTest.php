<?php

declare(strict_types=1);

namespace Aurix\Tests\Unit;

use Aurix\Models\SocialAccount;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class HasSocialAccountsTraitTest extends TestCase
{
    public function test_user_has_social_accounts_relationship(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => '123',
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '456',
        ]);

        $this->assertCount(2, $user->socialAccounts);
        $this->assertInstanceOf(SocialAccount::class, $user->socialAccounts->first());
    }

    public function test_has_social_account_returns_true_when_account_exists(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => '123',
        ]);

        $this->assertTrue($user->hasSocialAccount('facebook'));
        $this->assertFalse($user->hasSocialAccount('google'));
    }

    public function test_get_social_account_returns_account_when_exists(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'provider_id' => '789',
            'email' => 'test@example.com',
        ]);

        $retrieved = $user->getSocialAccount('github');

        $this->assertNotNull($retrieved);
        $this->assertEquals($socialAccount->id, $retrieved->id);
        $this->assertEquals('github', $retrieved->provider);
    }

    public function test_get_social_account_returns_null_when_not_exists(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertNull($user->getSocialAccount('twitter'));
    }

    public function test_user_can_have_multiple_social_accounts(): void
    {
        $user = User::create([
            'name' => 'Multi Account User',
            'email' => 'multi@example.com',
            'password' => bcrypt('password'),
        ]);

        $providers = ['facebook', 'google', 'github', 'linkedin'];

        foreach ($providers as $index => $provider) {
            SocialAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => 'id-' . $index,
            ]);
        }

        $this->assertCount(4, $user->socialAccounts);

        foreach ($providers as $provider) {
            $this->assertTrue($user->hasSocialAccount($provider));
        }
    }
}
