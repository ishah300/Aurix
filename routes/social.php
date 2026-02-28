<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Aurix\Http\Controllers\SocialAuthController;

Route::middleware(['web', 'guest'])
    ->group(function (): void {
        foreach (['socialite.redirect', 'social.redirect', 'oauth.redirect', 'auth.social.redirect'] as $name) {
            Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
                ->name($name);
        }

        foreach (['socialite.callback', 'social.callback'] as $name) {
            Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
                ->name($name);
        }

        Route::get('/auth/link/confirm/{token}', [SocialAuthController::class, 'confirmEmailLink'])
            ->middleware('signed')
            ->name('social.link.confirm');
    });

Route::middleware(['web', 'auth'])
    ->group(function (): void {
        Route::get('/auth/{provider}/link', [SocialAuthController::class, 'linkRedirect'])
            ->name('social.link.redirect');

        Route::get('/auth/{provider}/link/callback', [SocialAuthController::class, 'linkCallback'])
            ->name('social.link.callback');

        Route::post('/auth/{provider}/unlink', [SocialAuthController::class, 'unlink'])
            ->name('social.unlink');
    });
