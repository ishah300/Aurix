<?php

declare(strict_types=1);

namespace Aurix\Http\Controllers;

use Aurix\Exceptions\SocialEmailAlreadyExistsException;
use Aurix\Models\SocialAccount;
use Aurix\Models\SocialProvider;
use Aurix\Services\SocialAuthService;
use Aurix\Services\SocialLinkVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        protected SocialAuthService $service,
        protected SocialLinkVerificationService $linkVerification
    ) {}

    public function redirect(string $provider): RedirectResponse
    {
        if (!$this->service->isProviderEnabled($provider)) {
            return redirect()->route('login')
                ->with('error', 'Social login with ' . ucfirst($provider) . ' is not available.');
        }

        try {
            $driver = $this->configureSocialite($provider, 'socialite.callback');
            return $driver->redirect();
        } catch (\Throwable $e) {
            \Log::error('Social redirect failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Unable to connect to ' . ucfirst($provider) . '.');
        }
    }

    public function callback(string $provider): RedirectResponse
    {
        if (!$this->service->isProviderEnabled($provider)) {
            return redirect()->route('login')
                ->with('error', 'Social login with ' . ucfirst($provider) . ' is not available.');
        }

        $socialiteUser = null;
        try {
            $driver = $this->configureSocialite($provider, 'socialite.callback');
            $socialiteUser = $driver->user();
            
            \Log::info('Social login callback', [
                'provider' => $provider,
                'provider_id' => $socialiteUser->getId(),
                'email' => $socialiteUser->getEmail(),
                'name' => $socialiteUser->getName(),
            ]);
            
            $user = $this->service->handleCallback($provider, $socialiteUser);
            
            \Log::info('User created/found', [
                'user_id' => $user->getAuthIdentifier(),
                'email' => $user->email ?? 'no email',
            ]);

            Auth::login($user, true);
            
            \Log::info('User logged in successfully');

            return redirect()->intended(config('aurix.social.redirect_after_login', '/dashboard'));
        } catch (SocialEmailAlreadyExistsException $e) {
            try {
                if ($socialiteUser === null) {
                    throw new \RuntimeException('Provider user profile was not returned.');
                }

                $userModelClass = $this->resolveUserModelClass();
                $user = $userModelClass::query()->where('email', $e->email)->first();

                if (! $user) {
                    throw new \RuntimeException('Existing user for this email was not found.');
                }

                $confirmUrl = $this->linkVerification->storePendingLink(
                    $user,
                    $provider,
                    $socialiteUser
                );

                if ($this->linkVerification->sendConfirmationEmail($e->email, $provider, $confirmUrl)) {
                    return redirect()->route('login')
                        ->with('status', 'Email already exists. We sent a confirmation link to verify and link this provider.');
                }

                return redirect()->route('login')
                    ->with('status', 'Email already exists. Mail is not configured, so use the confirmation link below (valid for 15 minutes).')
                    ->with('social_link_url', $confirmUrl);
            } catch (\Throwable $linkError) {
                \Log::error('Social link verification email failed', [
                    'provider' => $provider,
                    'email' => $e->email,
                    'error' => $linkError->getMessage(),
                ]);

                return redirect()->route('login')
                    ->with('error', 'Unable to verify social account ownership right now.');
            }
        } catch (\Throwable $e) {
            \Log::error('Social login failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Unable to login with ' . ucfirst($provider) . '. Please try again.');
        }
    }

    public function linkRedirect(Request $request, string $provider): RedirectResponse
    {
        if (!$this->service->isProviderEnabled($provider)) {
            return back()->with('error', 'Social linking with ' . ucfirst($provider) . ' is not available.');
        }

        $returnTo = $this->sanitizeReturnTo(
            $request,
            (string) $request->query('return_to', url()->previous()),
            '/'
        );
        $request->session()->put('aurix.social.link_return_to', $returnTo);

        try {
            $driver = $this->configureSocialite($provider, 'social.link.callback');
            return $driver->redirect();
        } catch (\Throwable $e) {
            \Log::error('Social link redirect failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to connect to ' . ucfirst($provider) . ' for linking.');
        }
    }

    public function linkCallback(Request $request, string $provider): RedirectResponse
    {
        $returnTo = $this->sanitizeReturnTo(
            $request,
            (string) $request->session()->pull('aurix.social.link_return_to', '/'),
            '/'
        );

        try {
            $driver = $this->configureSocialite($provider, 'social.link.callback');
            $socialiteUser = $driver->user();

            $user = $request->user();
            if (! $user) {
                return redirect()->route('login')->with('error', 'Please sign in to link social accounts.');
            }

            $this->service->linkSocialAccount($user, $provider, $socialiteUser);

            return redirect($returnTo)->with('status', ucfirst($provider) . ' account linked successfully.');
        } catch (\Throwable $e) {
            \Log::error('Social link callback failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return redirect($returnTo)->with('error', 'Unable to link social account right now.');
        }
    }

    public function unlink(Request $request, string $provider): RedirectResponse
    {
        try {
            $user = $request->user();
            if (! $user) {
                return redirect()->route('login')->with('error', 'Please sign in first.');
            }

            $this->service->unlinkSocialAccount($user, $provider);

            return back()->with('status', ucfirst($provider) . ' account unlinked successfully.');
        } catch (\Throwable $e) {
            \Log::error('Social unlink failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to unlink social account right now.');
        }
    }

    public function confirmEmailLink(Request $request, string $token): RedirectResponse
    {
        $payload = Cache::pull("aurix.social.pending_link.{$token}");
        if (!is_array($payload)) {
            return redirect()->route('login')
                ->with('error', 'This link is invalid or has expired. Please try social sign-in again.');
        }

        try {
            $userModelClass = $this->resolveUserModelClass();
            $user = $userModelClass::query()->find($payload['user_id'] ?? null);
            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Account not found for linking.');
            }

            $provider = (string) ($payload['provider'] ?? '');
            $providerId = (string) ($payload['provider_id'] ?? '');
            if ($provider === '' || $providerId === '') {
                return redirect()->route('login')
                    ->with('error', 'Invalid social account payload.');
            }

            $existingProviderId = SocialAccount::query()
                ->where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();
            if ($existingProviderId && (int) $existingProviderId->user_id !== (int) $user->getAuthIdentifier()) {
                return redirect()->route('login')
                    ->with('error', 'This social account is already linked to another user.');
            }

            $existingProviderForUser = SocialAccount::query()
                ->where('user_id', (int) $user->getAuthIdentifier())
                ->where('provider', $provider)
                ->first();
            if ($existingProviderForUser && (string) $existingProviderForUser->provider_id !== $providerId) {
                return redirect()->route('login')
                    ->with('error', 'A different account for this provider is already linked.');
            }

            SocialAccount::query()->updateOrCreate(
                [
                    'user_id' => (int) $user->getAuthIdentifier(),
                    'provider' => $provider,
                ],
                [
                    'provider_id' => $providerId,
                    'name' => $payload['name'] ?? null,
                    'email' => $payload['email'] ?? null,
                    'avatar' => $payload['avatar'] ?? null,
                    'token' => $payload['token'] ?? null,
                    'refresh_token' => $payload['refresh_token'] ?? null,
                    'expires_at' => $payload['expires_at'] ?? null,
                ]
            );

            Auth::login($user, true);

            return redirect()->intended(config('aurix.social.redirect_after_login', '/dashboard'))
                ->with('status', ucfirst($provider) . ' linked and signed in successfully.');
        } catch (\Throwable $e) {
            \Log::error('Confirm social email link failed', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Unable to confirm social link. Please try again.');
        }
    }

    protected function resolveUserModelClass(): string
    {
        $model = (string) config('auth.providers.users.model', 'App\\Models\\User');
        if ($model && class_exists($model)) {
            return $model;
        }

        foreach (['App\\Models\\User', 'App\\User'] as $fallback) {
            if (class_exists($fallback)) {
                return $fallback;
            }
        }

        throw new \RuntimeException('User model not found.');
    }

    protected function configureSocialite(string $provider, string $callbackRouteName = 'socialite.callback')
    {
        $providerConfig = SocialProvider::query()
            ->where('slug', $provider)
            ->where('is_active', true)
            ->where('enabled', true)
            ->first();

        if (!$providerConfig) {
            throw new \RuntimeException("Provider {$provider} not found or not enabled");
        }

        $config = [
            'client_id' => $providerConfig->client_id,
            'client_secret' => $providerConfig->revealSecret(),
            'redirect' => $providerConfig->redirect ?: route($callbackRouteName, ['provider' => $provider]),
        ];

        \Log::info('Configuring Socialite', [
            'provider' => $provider,
            'has_client_id' => !empty($config['client_id']),
            'has_client_secret' => !empty($config['client_secret']),
            'redirect' => $config['redirect'],
        ]);

        // Set config for this request
        config(["services.{$provider}" => $config]);

        // Get the driver with the config
        $driver = Socialite::driver($provider);

        // Apply scopes if configured (supports both comma and space separated formats).
        if ($providerConfig->scopes) {
            $scopes = preg_split('/[\s,]+/', (string) $providerConfig->scopes, -1, PREG_SPLIT_NO_EMPTY);
            if (!empty($scopes)) {
                $driver->scopes(array_values(array_unique($scopes)));
            }
        } elseif ($provider === 'github') {
            // Ensure GitHub can return email for sign up/sign in linking.
            $driver->scopes(['read:user', 'user:email']);
        }

        return $driver;
    }

    private function sanitizeReturnTo(Request $request, string $value, string $fallback = '/'): string
    {
        $fallbackPath = $this->normalizePath($fallback) ?? '/';
        $candidate = trim($value);

        if ($candidate === '') {
            return $fallbackPath;
        }

        if (str_starts_with($candidate, '/')) {
            return $this->normalizePath($candidate) ?? $fallbackPath;
        }

        $parts = parse_url($candidate);
        if (! is_array($parts)) {
            return $fallbackPath;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if ($scheme !== '' && ! in_array($scheme, ['http', 'https'], true)) {
            return $fallbackPath;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host !== '' && $host !== strtolower($request->getHost())) {
            return $fallbackPath;
        }

        $path = (string) ($parts['path'] ?? '/');
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        return $this->normalizePath($path . $query) ?? $fallbackPath;
    }

    private function normalizePath(string $path): ?string
    {
        $value = trim($path);
        if ($value === '' || str_starts_with($value, '//')) {
            return null;
        }

        if (! str_starts_with($value, '/')) {
            $value = '/' . ltrim($value, '/');
        }

        return $value;
    }
}
