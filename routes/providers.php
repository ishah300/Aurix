<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Aurix\Http\Controllers\ProviderCrudController;

Route::middleware(config('aurix.api.middleware', ['web', 'auth']))
    ->group(function (): void {
        Route::get('/providers', [ProviderCrudController::class, 'index'])->name('aurix.api.providers.index');
        Route::get('/providers/{provider}', [ProviderCrudController::class, 'show'])->name('aurix.api.providers.show');
        Route::put('/providers/{provider}', [ProviderCrudController::class, 'update'])->name('aurix.api.providers.update');
        Route::post('/providers/{provider}/toggle', [ProviderCrudController::class, 'toggle'])->name('aurix.api.providers.toggle');
        Route::post('/providers/seed', [ProviderCrudController::class, 'seed'])->name('aurix.api.providers.seed');
    });
