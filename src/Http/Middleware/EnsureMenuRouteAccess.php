<?php

declare(strict_types=1);

namespace Aurix\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Aurix\Services\MenuAccessService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class EnsureMenuRouteAccess
{
    public function __construct(private readonly MenuAccessService $menuAccess)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->authorize($request, false);

        return $next($request);
    }

    public function authorize(
        Request $request,
        bool $throwIfUnauthenticated = true,
        bool $markChecked = true
    ): void
    {
        if (! (bool) config('aurix.menus.auto_enforce_web_routes', true)) {
            return;
        }

        if ($markChecked && $request->attributes->get('_aurix_auto_guard_checked') === true) {
            return;
        }
        if ($markChecked) {
            $request->attributes->set('_aurix_auto_guard_checked', true);
        }

        if ($request->isMethod('OPTIONS')) {
            return;
        }

        if (str_starts_with('/' . trim($request->path(), '/'), '/api/')) {
            return;
        }

        $path = '/' . trim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }

        $menu = $this->resolveMenuForPath($path);

        if (! $menu) {
            return;
        }

        $user = $request->user();
        if (! $user) {
            if ($throwIfUnauthenticated) {
                throw new UnauthorizedHttpException('', 'Unauthenticated.');
            }

            return;
        }

        $action = match (strtoupper($request->method())) {
            'POST' => 'insert',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'view',
        };

        if (! $this->menuAccess->can($user, (string) $menu['slug'], $action)) {
            throw new AccessDeniedHttpException('Unauthorized access.');
        }
    }

    /**
     * @return array{id:int,slug:string,normalized_route:string}|null
     */
    private function resolveMenuForPath(string $path): ?array
    {
        $bestMatch = null;
        $bestScore = -1;

        foreach ($this->routeMatchers() as $item) {
            $score = $this->matchScore($item['normalized_route'], $path);
            if ($score === null || $score <= $bestScore) {
                continue;
            }

            $bestScore = $score;
            $bestMatch = $item;
        }

        return $bestMatch;
    }

    /**
     * @return array<int, array{id:int,slug:string,normalized_route:string}>
     */
    private function routeMatchers(): array
    {
        $version = (int) cache()->get('aurix:menu_access:version', 1);
        $cacheKey = 'aurix:menu_route_matchers:v' . $version;

        /** @var array<int, array{id:int,slug:string,normalized_route:string}> $matchers */
        $matchers = Cache::remember($cacheKey, now()->addMinutes(5), function (): array {
            /** @var \Illuminate\Support\Collection<int, object{id:mixed,slug:mixed,route:mixed}> $rows */
            $rows = \Aurix\Models\Menu::query()
                ->where('is_active', true)
                ->whereNotNull('route')
                ->where('route', '!=', '')
                ->where('route', '!=', '#')
                ->get(['id', 'slug', 'route']);

            return $rows
                ->map(function (object $row): ?array {
                    $route = $this->normalizeRoute((string) $row->route);
                    if ($route === null) {
                        return null;
                    }

                    return [
                        'id' => (int) $row->id,
                        'slug' => (string) $row->slug,
                        'normalized_route' => $route,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });

        return $matchers;
    }

    private function normalizeRoute(string $route): ?string
    {
        $value = trim($route);
        if ($value === '' || $value === '#') {
            return null;
        }

        $path = parse_url($value, PHP_URL_PATH);
        if (is_string($path) && $path !== '') {
            $value = $path;
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
}
