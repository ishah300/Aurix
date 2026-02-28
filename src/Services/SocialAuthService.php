<?php

declare(strict_types=1);

namespace Aurix\Services;

use Aurix\Exceptions\SocialEmailAlreadyExistsException;
use Aurix\Models\SocialAccount;
use Aurix\Models\SocialProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use RuntimeException;

class SocialAuthService
{
    public function handleCallback(string $provider, SocialiteUser $socialiteUser): Authenticatable
    {
        return DB::transaction(function () use ($provider, $socialiteUser) {
            $socialAccount = SocialAccount::query()
                ->where('provider', $provider)
                ->where('provider_id', $socialiteUser->getId())
                ->first();

            if ($socialAccount) {
                $this->updateSocialAccount($socialAccount, $socialiteUser);
                return $socialAccount->user;
            }

            $userModel = $this->getUserModelClass();
            $email = $socialiteUser->getEmail();

            if ($email) {
                $user = $userModel::query()->where('email', $email)->first();
                
                if ($user) {
                    $this->createSocialAccount($user, $provider, $socialiteUser);
                    return $user;
                }
            }

            $user = $this->createUser($socialiteUser);
            $this->createSocialAccount($user, $provider, $socialiteUser);

            return $user;
        });
    }

    protected function createUser(SocialiteUser $socialiteUser): Authenticatable
    {
        $userModel = $this->getUserModelClass();
        
        return $userModel::create([
            'name' => $socialiteUser->getName() ?? 'User',
            'email' => $socialiteUser->getEmail() ?? $socialiteUser->getId() . '@social.local',
            'password' => Hash::make(Str::random(32)),
            'email_verified_at' => now(),
        ]);
    }

    protected function getUserModelClass(): string
    {
        $userModel = config('auth.providers.users.model');
        
        if (!$userModel) {
            // Fallback to common User model locations
            if (class_exists('App\\Models\\User')) {
                return 'App\\Models\\User';
            } elseif (class_exists('App\\User')) {
                return 'App\\User';
            }
            
            throw new \RuntimeException(
                'User model not found. Please ensure your User model exists and is configured in config/auth.php'
            );
        }
        
        if (!class_exists($userModel)) {
            throw new \RuntimeException(
                "User model class '{$userModel}' does not exist. Please check your config/auth.php configuration."
            );
        }
        
        return $userModel;
    }

    protected function createSocialAccount(
        Authenticatable $user,
        string $provider,
        SocialiteUser $socialiteUser
    ): SocialAccount {
        return SocialAccount::create([
            'user_id' => $user->getAuthIdentifier(),
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'avatar' => $socialiteUser->getAvatar(),
            'token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken ?? null,
            'expires_at' => isset($socialiteUser->expiresIn) 
                ? now()->addSeconds($socialiteUser->expiresIn) 
                : null,
        ]);
    }

    protected function updateSocialAccount(
        SocialAccount $socialAccount,
        SocialiteUser $socialiteUser
    ): void {
        $socialAccount->update([
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'avatar' => $socialiteUser->getAvatar(),
            'token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken ?? null,
            'expires_at' => isset($socialiteUser->expiresIn) 
                ? now()->addSeconds($socialiteUser->expiresIn) 
                : null,
        ]);
    }

    public function isProviderEnabled(string $provider): bool
    {
        $providerConfig = SocialProvider::query()
            ->where('slug', $provider)
            ->where('is_active', true)
            ->where('enabled', true)
            ->first();

        return $providerConfig !== null 
            && !empty($providerConfig->client_id) 
            && !empty($providerConfig->getAttributes()['client_secret']);
    }

    public function linkSocialAccount(Authenticatable $user, string $provider, SocialiteUser $socialiteUser): SocialAccount
    {
        $providerId = (string) $socialiteUser->getId();
        $userId = (int) $user->getAuthIdentifier();

        if ($providerId === '') {
            throw new RuntimeException('Provider did not return a valid account id.');
        }

        $existingForProvider = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if ($existingForProvider && (int) $existingForProvider->user_id !== $userId) {
            throw new RuntimeException('This social account is already linked to another user.');
        }

        $alreadyLinkedByUser = SocialAccount::query()
            ->where('user_id', $userId)
            ->where('provider', $provider)
            ->first();

        if ($alreadyLinkedByUser && (string) $alreadyLinkedByUser->provider_id !== $providerId) {
            throw new RuntimeException('A different ' . ucfirst($provider) . ' account is already linked.');
        }

        if ($existingForProvider && (int) $existingForProvider->user_id === $userId) {
            $this->updateSocialAccount($existingForProvider, $socialiteUser);
            return $existingForProvider->fresh();
        }

        return $this->createSocialAccount($user, $provider, $socialiteUser);
    }

    public function unlinkSocialAccount(Authenticatable $user, string $provider): void
    {
        $userId = (int) $user->getAuthIdentifier();
        SocialAccount::query()
            ->where('user_id', $userId)
            ->where('provider', $provider)
            ->delete();
    }
}
