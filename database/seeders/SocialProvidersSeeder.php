<?php

declare(strict_types=1);

namespace Aurix\Database\Seeders;

use Illuminate\Database\Seeder;
use Aurix\Models\SocialProvider;

class SocialProvidersSeeder extends Seeder
{
    public function run(): void
    {
        $available = collect((array) config('aurix.social.available_providers', ['google', 'github']))
            ->map(static fn ($slug): string => strtolower((string) $slug))
            ->all();

        $providers = [
            ['slug' => 'facebook', 'name' => 'Facebook', 'requires_package' => false],
            ['slug' => 'google', 'name' => 'Google', 'requires_package' => false],
            ['slug' => 'twitter', 'name' => 'Twitter', 'requires_package' => false],
            ['slug' => 'github', 'name' => 'GitHub', 'requires_package' => false],
            ['slug' => 'gitlab', 'name' => 'GitLab', 'requires_package' => false],
            ['slug' => 'linkedin', 'name' => 'LinkedIn', 'requires_package' => false],
            ['slug' => 'bitbucket', 'name' => 'Bitbucket', 'requires_package' => false],
        ];

        foreach ($providers as $p) {
            $slug = strtolower((string) $p['slug']);
            $isAvailable = in_array($slug, $available, true);

            SocialProvider::query()->updateOrCreate(
                ['slug' => $p['slug']],
                array_merge($p, [
                    'is_active' => $isAvailable,
                    'enabled' => false,
                    'description' => $isAvailable ? null : 'Coming Soon',
                ])
            );
        }
    }
}
