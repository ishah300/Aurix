<?php

declare(strict_types=1);

namespace Aurix;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Aurix\Models\SocialProvider;
use Aurix\Contracts\PermissionResolver as PermissionResolverContract;
use Aurix\Console\Commands\AuditRoutesCommand;
use Aurix\Console\Commands\InstallCommand;
use Aurix\Console\Commands\MakeAdminCommand;
use Aurix\Console\Commands\SeedStarterDataCommand;
use Aurix\Console\Commands\SetupCommand;
use Aurix\Console\Commands\SetupSocialAuthCommand;
use Aurix\Http\Middleware\EnsurePermission;
use Aurix\Http\Middleware\EnsureRole;
use Aurix\Http\Middleware\EnsureMenuRouteAccess;
use Aurix\Services\AppearanceSettingsService;
use Aurix\Services\MenuAccessService;
use Aurix\Services\SocialLinkVerificationService;
use Aurix\Support\DatabasePermissionResolver;

class AurixServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aurix.php', 'aurix');

        $this->app->singleton(PermissionResolverContract::class, function ($app) {
            return new DatabasePermissionResolver($app['config']);
        });

        $this->app->singleton(MenuAccessService::class, function () {
            return new MenuAccessService();
        });
        $this->app->singleton(AppearanceSettingsService::class, function () {
            return new AppearanceSettingsService();
        });
        $this->app->singleton(\Aurix\Services\SocialAuthService::class, function () {
            return new \Aurix\Services\SocialAuthService();
        });
        $this->app->singleton(SocialLinkVerificationService::class, function () {
            return new SocialLinkVerificationService();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/aurix.php' => config_path('aurix.php'),
        ], 'aurix-config');
        // Backward-compatible publish tag
        $this->publishes([
            __DIR__ . '/../config/aurix.php' => config_path('aurix.php'),
        ], 'laraauth-config');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/aurix'),
        ], 'aurix-views');
        $this->publishes([
            __DIR__ . '/../resources/publish/auth' => resource_path('views/auth'),
            __DIR__ . '/../resources/publish/components' => resource_path('views/components'),
        ], 'aurix-auth-views');
        $this->publishes([
            __DIR__ . '/../resources/publish/public' => public_path('vendor/aurix'),
        ], 'aurix-assets');
        // Backward-compatible publish tag + path for existing apps
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/laraauth'),
        ], 'laraauth-views');
        $this->publishes([
            __DIR__ . '/../resources/publish/auth' => resource_path('views/auth'),
            __DIR__ . '/../resources/publish/components' => resource_path('views/components'),
        ], 'laraauth-auth-views');
        $this->publishes([
            __DIR__ . '/../resources/publish/public' => public_path('vendor/aurix'),
        ], 'laraauth-assets');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'aurix');
        // Backward-compatible view namespace
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laraauth');
        $this->registerApiRoutes();
        $this->registerWebRoutes();
        $this->registerPostsRoutes();
        $this->registerSocialRoutes();
        $this->registerCommands();

        $router = $this->app['router'];
        $router->aliasMiddleware('role', EnsureRole::class);
        $router->aliasMiddleware('permission', EnsurePermission::class);
        $router->aliasMiddleware('menu', \Aurix\Http\Middleware\EnsureMenuAccess::class);
        if ((bool) config('aurix.menus.auto_enforce_web_routes', true)) {
            $router->pushMiddlewareToGroup('web', EnsureMenuRouteAccess::class);
            Event::listen(RouteMatched::class, function (RouteMatched $event): void {
                /** @var EnsureMenuRouteAccess $guard */
                $guard = $this->app->make(EnsureMenuRouteAccess::class);
                $guard->authorize($event->request, false, false);
            });
        }

        Gate::before(function (Authenticatable $user, string $ability, array $arguments = []) {
            /** @var PermissionResolverContract $resolver */
            $resolver = $this->app->make(PermissionResolverContract::class);

            if ($resolver->isSuperAdmin($user)) {
                return true;
            }

            if ($resolver->hasPermission($user, $ability, $arguments)) {
                return true;
            }

            return null;
        });

        Gate::define('aurix.manage-rbac', function (Authenticatable $user): bool {
            /** @var PermissionResolverContract $resolver */
            $resolver = $this->app->make(PermissionResolverContract::class);
            $roles = (array) config('aurix.rbac.manage_roles', ['admin', 'super-admin']);

            foreach ($roles as $role) {
                if ($resolver->hasRole($user, (string) $role)) {
                    return true;
                }
            }

            return false;
        });

        $this->registerViewContext();
        $this->registerBladeHelpers();
    }

    private function registerApiRoutes(): void
    {
        $router = $this->app['router'];

        $router->group([
            'prefix' => config('aurix.api.prefix', 'api/auth'),
            'middleware' => config('aurix.api.middleware', ['web', 'auth']),
        ], function (): void {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
            // Social providers admin API
            $this->loadRoutesFrom(__DIR__ . '/../routes/providers.php');
        });
    }

    private function registerWebRoutes(): void
    {
        if (! (bool) config('aurix.ui.enabled', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    private function registerPostsRoutes(): void
    {
        if (! (bool) config('aurix.posts.enabled', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/posts.php');
    }

    private function registerSocialRoutes(): void
    {
        if (! (bool) config('aurix.social.enabled', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/social.php');
    }

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            SeedStarterDataCommand::class,
            MakeAdminCommand::class,
            SetupCommand::class,
            AuditRoutesCommand::class,
            SetupSocialAuthCommand::class,
        ]);
    }

    private function registerViewContext(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        View::composer('*', function ($view): void {
            /** @var AppearanceSettingsService $appearance */
            $appearance = $this->app->make(AppearanceSettingsService::class);
            $view->with('aurixAppearance', $appearance->all());

            $enabledSocialProviders = collect();
            $authRouteNames = [
                'login',
                'register',
                'password.request',
                'password.reset',
                'password.confirm',
                'verification.notice',
                'aurix.rbac.appearance.preview',
            ];
            $routeName = request()->route()?->getName();
            $providersTable = (string) config(
                'aurix.tables.social_providers',
                (string) config('aurix.social.providers_table', 'aurix_social_providers')
            );

            if (is_string($routeName) && in_array($routeName, $authRouteNames, true) && Schema::hasTable($providersTable)) {
                $order = [
                    'facebook', 'google', 'github', 'linkedin',
                    'twitter', 'gitlab', 'bitbucket',
                ];

                $enabledSocialProviders = SocialProvider::query()
                    ->where('is_active', true)
                    ->where('enabled', true)
                    ->where('requires_package', false)
                    ->whereIn('slug', $order)
                    ->get(['slug', 'name'])
                    ->sortBy(static function (SocialProvider $provider) use ($order): int {
                        $idx = array_search((string) $provider->slug, $order, true);
                        return $idx === false ? 999 : (int) $idx;
                    })
                    ->values()
                    ->map(static fn (SocialProvider $provider): array => [
                        'slug' => (string) $provider->slug,
                        'name' => (string) $provider->name,
                    ]);
            }
            $view->with('aurixEnabledSocialProviders', $enabledSocialProviders);

            $user = Auth::user();
            if (! $user instanceof Authenticatable) {
                $view->with('aurixMenuTree', collect());
                $view->with('aurixMenuAccess', collect());

                return;
            }

            /** @var MenuAccessService $service */
            $service = $this->app->make(MenuAccessService::class);
            $view->with('aurixMenuTree', $service->treeForUser($user));
            $view->with('aurixMenuAccess', $service->forUser($user));
        });
    }

    private function registerBladeHelpers(): void
    {
        Blade::if('aurixCan', function (string $menuSlug, string $action = 'view'): bool {
            $user = Auth::user();
            if (! $user instanceof Authenticatable) {
                return false;
            }

            /** @var MenuAccessService $service */
            $service = $this->app->make(MenuAccessService::class);

            return $service->can($user, $menuSlug, $action);
        });
    }
}
