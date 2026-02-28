<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Aurix\Http\Controllers\AppearanceSettingsController;
use Aurix\Http\Controllers\MenuCrudController;
use Aurix\Http\Controllers\MyMenuAccessController;
use Aurix\Http\Controllers\RoleCrudController;
use Aurix\Http\Controllers\RolePermissionMatrixController;
use Aurix\Http\Controllers\RolePermissionMatrixTransferController;
use Aurix\Http\Controllers\UserCrudController;

Route::get('/me/menu-access', [MyMenuAccessController::class, 'index']);

Route::middleware(['can:aurix.manage-rbac'])->group(function (): void {
    Route::get('/settings/appearance', [AppearanceSettingsController::class, 'show']);
    Route::put('/settings/appearance', [AppearanceSettingsController::class, 'update']);

    Route::get('/roles', [RoleCrudController::class, 'index']);
    Route::post('/roles', [RoleCrudController::class, 'store']);
    Route::get('/roles/{role}', [RoleCrudController::class, 'show']);
    Route::put('/roles/{role}', [RoleCrudController::class, 'update']);
    Route::delete('/roles/{role}', [RoleCrudController::class, 'destroy']);

    Route::get('/menus', [MenuCrudController::class, 'index']);
    Route::post('/menus', [MenuCrudController::class, 'store']);
    Route::put('/menus/reorder', [MenuCrudController::class, 'reorder']);
    Route::get('/menus/{menu}', [MenuCrudController::class, 'show']);
    Route::put('/menus/{menu}', [MenuCrudController::class, 'update']);
    Route::delete('/menus/{menu}', [MenuCrudController::class, 'destroy']);

    Route::get('/users', [UserCrudController::class, 'index']);
    Route::post('/users', [UserCrudController::class, 'store']);
    Route::get('/users/{user}', [UserCrudController::class, 'show']);
    Route::put('/users/{user}', [UserCrudController::class, 'update']);
    Route::delete('/users/{user}', [UserCrudController::class, 'destroy']);

    Route::get('/roles/{role}/permissions-matrix', [RolePermissionMatrixController::class, 'show']);
    Route::put('/roles/{role}/permissions-matrix', [RolePermissionMatrixController::class, 'update']);
    Route::get('/roles/{role}/permissions-matrix/export', [RolePermissionMatrixTransferController::class, 'export']);
    Route::post('/roles/{role}/permissions-matrix/import', [RolePermissionMatrixTransferController::class, 'import']);
});
