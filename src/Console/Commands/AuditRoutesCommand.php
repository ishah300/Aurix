<?php

declare(strict_types=1);

namespace Aurix\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Aurix\Models\Menu;

class AuditRoutesCommand extends Command
{
    protected $signature = 'aurix:audit-routes {--json : Emit machine-readable JSON output} {--fail-on-issues : Exit with code 1 when issues are found}';

    protected $description = 'Audit menu-route coverage and highlight mismatches between Aurix menus and application routes.';

    public function handle(): int
    {
        if (! Schema::hasTable(config('aurix.tables.menus', 'menus'))) {
            $this->error('Menus table not found. Run migrations first.');

            return self::FAILURE;
        }

        $activeMenus = Menu::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'route']);

        $menuRoutes = $activeMenus
            ->map(function (Menu $menu): ?array {
                $normalized = $this->normalizePath((string) ($menu->route ?? ''));
                if ($normalized === null) {
                    return null;
                }

                return [
                    'menu_id' => (int) $menu->id,
                    'menu_name' => (string) $menu->name,
                    'menu_slug' => (string) $menu->slug,
                    'menu_route' => (string) $menu->route,
                    'normalized_route' => $normalized,
                ];
            })
            ->filter()
            ->values();

        $frameworkRoutes = collect(Route::getRoutes()->getRoutes())
            ->map(function (IlluminateRoute $route): array {
                $uri = '/' . ltrim($route->uri(), '/');
                $uri = $uri === '//' ? '/' : $uri;

                return [
                    'uri' => $uri,
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'middleware' => $route->gatherMiddleware(),
                    'methods' => array_values(array_filter($route->methods(), static fn (string $m): bool => $m !== 'HEAD')),
                ];
            })
            ->values();

        $appAuthWebRoutes = $frameworkRoutes
            ->filter(function (array $route): bool {
                if (str_starts_with($route['uri'], '/api/')) {
                    return false;
                }

                if (str_starts_with((string) $route['action'], 'Aurix\\')) {
                    return false;
                }

                return in_array('auth', (array) $route['middleware'], true);
            })
            ->reject(fn (array $route): bool => $this->shouldIgnoreRoute($route))
            ->values();

        $menusWithoutRouteMatch = $menuRoutes
            ->filter(fn (array $menu): bool => ! $this->routeIsCoveredByRoutes($menu['normalized_route'], $frameworkRoutes))
            ->values()
            ->all();

        $uncoveredAppRoutes = $appAuthWebRoutes
            ->filter(fn (array $route): bool => ! $this->routeIsCoveredByMenus($route['uri'], $menuRoutes))
            ->values()
            ->all();

        $duplicateMenuRoutes = $menuRoutes
            ->groupBy('normalized_route')
            ->filter(static fn ($group): bool => $group->count() > 1)
            ->map(static fn ($group, $route): array => [
                'normalized_route' => (string) $route,
                'menus' => $group->map(static fn (array $item): array => [
                    'menu_id' => $item['menu_id'],
                    'menu_slug' => $item['menu_slug'],
                    'menu_name' => $item['menu_name'],
                ])->values()->all(),
            ])
            ->values()
            ->all();

        $report = [
            'summary' => [
                'active_menus' => $activeMenus->count(),
                'active_menus_with_real_routes' => $menuRoutes->count(),
                'auth_web_routes_checked' => $appAuthWebRoutes->count(),
                'issues_total' => count($menusWithoutRouteMatch) + count($uncoveredAppRoutes) + count($duplicateMenuRoutes),
            ],
            'issues' => [
                'menus_without_matching_route' => $menusWithoutRouteMatch,
                'uncovered_authenticated_web_routes' => $uncoveredAppRoutes,
                'duplicate_menu_routes' => $duplicateMenuRoutes,
            ],
        ];

        if ((bool) $this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->displayReport($report);
        }

        $hasIssues = $report['summary']['issues_total'] > 0;
        if ($hasIssues && (bool) $this->option('fail-on-issues')) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $report
     */
    private function displayReport(array $report): void
    {
        $summary = $report['summary'];
        $this->info('Aurix Route Audit');
        $this->line('Active menus: ' . $summary['active_menus']);
        $this->line('Active menus with real routes: ' . $summary['active_menus_with_real_routes']);
        $this->line('Authenticated web routes checked: ' . $summary['auth_web_routes_checked']);
        $this->line('Issues found: ' . $summary['issues_total']);

        $this->newLine();

        $menuIssues = $report['issues']['menus_without_matching_route'];
        if ($menuIssues === []) {
            $this->info('No menu route mismatches found.');
        } else {
            $this->warn('Menus with route values that do not match any registered route:');
            $this->table(
                ['Menu ID', 'Slug', 'Route'],
                array_map(static fn (array $row): array => [
                    (string) $row['menu_id'],
                    (string) $row['menu_slug'],
                    (string) $row['menu_route'],
                ], $menuIssues)
            );
        }

        $routeIssues = $report['issues']['uncovered_authenticated_web_routes'];
        if ($routeIssues === []) {
            $this->info('No uncovered authenticated web routes found.');
        } else {
            $this->warn('Authenticated web routes not covered by any active menu route:');
            $this->table(
                ['URI', 'Name', 'Methods'],
                array_map(static fn (array $row): array => [
                    (string) $row['uri'],
                    (string) ($row['name'] ?? '-'),
                    implode(',', (array) $row['methods']),
                ], $routeIssues)
            );
        }

        $duplicateIssues = $report['issues']['duplicate_menu_routes'];
        if ($duplicateIssues === []) {
            $this->info('No duplicate menu routes found.');
        } else {
            $this->warn('Duplicate active menu routes detected:');
            foreach ($duplicateIssues as $dup) {
                $menuList = collect($dup['menus'])
                    ->map(static fn (array $item): string => $item['menu_slug'] . ' (#' . $item['menu_id'] . ')')
                    ->implode(', ');
                $this->line(' - ' . $dup['normalized_route'] . ': ' . $menuList);
            }
        }
    }

    private function routeIsCoveredByMenus(string $routePath, \Illuminate\Support\Collection $menuRoutes): bool
    {
        foreach ($menuRoutes as $menu) {
            if ($this->matchScore((string) $menu['normalized_route'], $routePath) !== null) {
                return true;
            }
        }

        return false;
    }

    private function routeIsCoveredByRoutes(string $menuRoute, \Illuminate\Support\Collection $routes): bool
    {
        foreach ($routes as $route) {
            if ($this->matchScore($menuRoute, (string) $route['uri']) !== null) {
                return true;
            }
        }

        return false;
    }

    private function normalizePath(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || $value === '#') {
            return null;
        }

        $parsed = parse_url($value, PHP_URL_PATH);
        if (is_string($parsed) && $parsed !== '') {
            $value = $parsed;
        }

        $normalized = '/' . ltrim($value, '/');
        if ($normalized !== '/') {
            $normalized = rtrim($normalized, '/');
        }

        return $normalized;
    }

    private function matchScore(string $route, string $path): ?int
    {
        if ($route === $path) {
            return 3000 + strlen($route);
        }

        if (str_contains($route, '*')) {
            if (Str::is(ltrim($route, '/'), ltrim($path, '/'))) {
                return 2000 + strlen(str_replace('*', '', $route));
            }

            return null;
        }

        if ($route !== '/' && str_starts_with($path, $route . '/')) {
            return 1000 + strlen($route);
        }

        return null;
    }

    /**
     * @param array{uri:string,name:mixed,action:mixed,middleware:array<int,string>,methods:array<int,string>} $route
     */
    private function shouldIgnoreRoute(array $route): bool
    {
        $name = (string) ($route['name'] ?? '');
        $uri = (string) ($route['uri'] ?? '');

        $namePatterns = (array) config('aurix.audit.ignore_route_names', []);
        foreach ($namePatterns as $pattern) {
            if ($pattern === null || trim((string) $pattern) === '') {
                continue;
            }
            if ($name !== '' && Str::is((string) $pattern, $name)) {
                return true;
            }
        }

        $uriPatterns = (array) config('aurix.audit.ignore_uri_patterns', []);
        foreach ($uriPatterns as $pattern) {
            if ($pattern === null || trim((string) $pattern) === '') {
                continue;
            }
            if (Str::is(ltrim((string) $pattern, '/'), ltrim($uri, '/'))) {
                return true;
            }
        }

        return false;
    }
}
