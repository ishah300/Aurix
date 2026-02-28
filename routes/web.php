<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Aurix\Http\Controllers\RbacAdminPageController;

/** @var string $uiPath */
$uiPath = trim((string) config('aurix.ui.path', 'auth/rbac'), '/');

Route::middleware(config('aurix.ui.middleware', ['web', 'auth']))
    ->group(function () use ($uiPath): void {
        Route::get($uiPath, [RbacAdminPageController::class, 'index'])
            ->name('aurix.rbac.index')
            ->middleware('can:aurix.manage-rbac');

        Route::get($uiPath . '/setup', [RbacAdminPageController::class, 'setup'])
            ->name('aurix.rbac.setup')
            ->middleware('can:aurix.manage-rbac');

        Route::get($uiPath . '/appearance', [RbacAdminPageController::class, 'appearance'])
            ->name('aurix.rbac.appearance')
            ->middleware('can:aurix.manage-rbac');

        Route::get($uiPath . '/appearance/preview/{screen}', [RbacAdminPageController::class, 'appearancePreview'])
            ->name('aurix.rbac.appearance.preview')
            ->middleware('can:aurix.manage-rbac');
            
        Route::get($uiPath . '/roles', [RbacAdminPageController::class, 'roles'])
            ->name('aurix.rbac.roles')
            ->middleware('menu:roles,view');
            
        Route::get($uiPath . '/roles/{role}/rights', [RbacAdminPageController::class, 'rights'])
            ->name('aurix.rbac.rights')
            ->middleware('menu:roles,view');
            
        Route::get($uiPath . '/users', [RbacAdminPageController::class, 'users'])
            ->name('aurix.rbac.users')
            ->middleware('menu:users,view');
            
        Route::get($uiPath . '/menus', [RbacAdminPageController::class, 'menus'])
            ->name('aurix.rbac.menus')
            ->middleware('can:aurix.manage-rbac');

        Route::get($uiPath . '/providers', [RbacAdminPageController::class, 'providers'])
            ->name('aurix.rbac.providers')
            ->middleware('can:aurix.manage-rbac');
    });
