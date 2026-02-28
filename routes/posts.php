<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Aurix\Http\Controllers\PostPageController;

Route::middleware(config('aurix.posts.middleware', ['web', 'auth', 'permission:posts.view']))
    ->group(function (): void {
        Route::get(config('aurix.posts.path', 'posts'), [PostPageController::class, 'index'])->name('aurix.posts.index');
        Route::post(config('aurix.posts.path', 'posts'), [PostPageController::class, 'store'])
            ->middleware(['permission:posts.insert'])
            ->name('aurix.posts.store');
        Route::put(config('aurix.posts.path', 'posts') . '/{post}', [PostPageController::class, 'update'])
            ->middleware(['permission:posts.update'])
            ->name('aurix.posts.update');
        Route::delete(config('aurix.posts.path', 'posts') . '/{post}', [PostPageController::class, 'destroy'])
            ->middleware(['permission:posts.delete'])
            ->name('aurix.posts.destroy');
    });
