<?php

declare(strict_types=1);

namespace Aurix\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialLinkVerificationService
{
    public function storePendingLink(
        Authenticatable $user,
        string $provider,
        SocialiteUser $socialiteUser,
        int $ttlMinutes = 15
    ): string {
        $token = (string) Str::uuid();
        $expiresAt = now()->addMinutes($ttlMinutes);

        Cache::put("aurix.social.pending_link.{$token}", [
            'user_id' => (int) $user->getAuthIdentifier(),
            'provider' => $provider,
            'provider_id' => (string) $socialiteUser->getId(),
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'avatar' => $socialiteUser->getAvatar(),
            'token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken ?? null,
            'expires_at' => isset($socialiteUser->expiresIn) ? now()->addSeconds($socialiteUser->expiresIn) : null,
        ], $expiresAt);

        return URL::temporarySignedRoute(
            'social.link.confirm',
            $expiresAt,
            ['token' => $token]
        );
    }

    public function sendConfirmationEmail(string $email, string $provider, string $confirmUrl): bool
    {
        if (! $this->mailCanDeliverToInbox()) {
            return false;
        }

        try {
            Mail::raw(
                "We received a request to link your {$provider} account to your Aurix account.\n\n".
                "If this was you, confirm here (valid for 15 minutes):\n{$confirmUrl}\n\n".
                "If you did not request this, ignore this email.",
                static function ($message) use ($email): void {
                    $message->to($email)->subject('Confirm social account linking');
                }
            );

            return true;
        } catch (\Throwable $mailError) {
            \Log::warning('Social link email could not be delivered, falling back to in-app confirmation link.', [
                'provider' => $provider,
                'email' => $email,
                'error' => $mailError->getMessage(),
            ]);

            return false;
        }
    }

    private function mailCanDeliverToInbox(): bool
    {
        $defaultMailer = (string) config('mail.default', '');
        if ($defaultMailer === '') {
            return false;
        }

        $transport = (string) config("mail.mailers.{$defaultMailer}.transport", '');
        if ($transport === '') {
            return false;
        }

        return ! in_array(strtolower($transport), ['log', 'array', 'null'], true);
    }
}
