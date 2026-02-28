<?php

declare(strict_types=1);

namespace Aurix\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Aurix\Models\SocialProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Artisan;

class ProviderCrudController extends Controller
{
    /**
     * @return array<int, string>
     */
    private function availableProviderSlugs(): array
    {
        return collect((array) config('aurix.social.available_providers', ['google', 'github']))
            ->map(static fn ($slug): string => strtolower((string) $slug))
            ->unique()
            ->values()
            ->all();
    }

    private function isComingSoon(SocialProvider $provider): bool
    {
        return ! in_array(strtolower((string) $provider->slug), $this->availableProviderSlugs(), true);
    }

    public function index(Request $request): JsonResponse
    {
        // Return providers in a fixed order matching the UI: left column top->bottom, then right column top->bottom
        $order = [
            'facebook','linkedin','github','bitbucket',
            'twitter','google','gitlab',
        ];

        $providers = SocialProvider::query()
            ->where('requires_package', false)
            ->whereIn('slug', $order)
            ->get()
            ->keyBy('slug');

        $out = [];
        foreach ($order as $slug) {
            if (isset($providers[$slug])) {
                $provider = $providers[$slug];
                $comingSoon = $this->isComingSoon($provider);
                $row = $provider->toArray();
                $row['coming_soon'] = $comingSoon;
                if ($comingSoon) {
                    $row['enabled'] = false;
                }
                $out[] = $row;
            }
        }

        return response()->json(['data' => $out]);
    }

    public function show(SocialProvider $provider): JsonResponse
    {
        $row = $provider->toArray();
        $row['coming_soon'] = $this->isComingSoon($provider);
        if ($row['coming_soon']) {
            $row['enabled'] = false;
        }

        return response()->json(['data' => $row]);
    }

    public function update(Request $request, SocialProvider $provider): JsonResponse
    {
        Gate::authorize('aurix.manage-rbac');

        if ($this->isComingSoon($provider)) {
            return response()->json(['message' => 'This provider is coming soon and cannot be configured yet.'], 422);
        }

        $rules = [
            'enabled' => ['nullable', 'boolean'],
            'client_id' => ['nullable', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:2000'],
            'redirect' => ['nullable', 'string', 'max:2000'],
            'scopes' => ['nullable', 'string', 'max:2000'],
        ];

        $payload = $request->all();
        Validator::make($payload, $rules)->validate();

        if (
            array_key_exists('client_secret', $payload)
            && ($payload['client_secret'] === null || trim((string) $payload['client_secret']) === '')
        ) {
            unset($payload['client_secret']);
        }

        $provider->fill(array_intersect_key($payload, array_flip($provider->getFillable())))->save();

        return response()->json(['message' => 'Provider updated.', 'data' => $provider->fresh()]);
    }

    public function toggle(Request $request, SocialProvider $provider): JsonResponse
    {
        Gate::authorize('aurix.manage-rbac');

        if ($this->isComingSoon($provider)) {
            return response()->json(['message' => 'This provider is coming soon and cannot be enabled yet.'], 422);
        }

        $provider->enabled = ! $provider->enabled;
        $provider->save();

        return response()->json(['message' => 'Provider toggled.', 'data' => $provider]);
    }

    public function seed(Request $request): JsonResponse
    {
        Gate::authorize('aurix.manage-rbac');

        // Ensure migrations exist
        $providersTable = (string) config(
            'aurix.tables.social_providers',
            (string) config('aurix.social.providers_table', 'aurix_social_providers')
        );

        if (!\Schema::hasTable($providersTable)) {
            return response()->json(['message' => 'Providers table not found. Run migrations first.'], 422);
        }

        try {
            // run seeder class
            Artisan::call('db:seed', ['--class' => 'Aurix\\Database\\Seeders\\SocialProvidersSeeder', '--force' => true]);

            return response()->json(['message' => 'Default providers seeded.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Seeding failed: ' . $e->getMessage()], 500);
        }
    }
}
