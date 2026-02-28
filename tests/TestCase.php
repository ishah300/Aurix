<?php

declare(strict_types=1);

namespace Aurix\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Aurix\Database\Seeders\DemoRbacSeeder;
use Aurix\AurixServiceProvider;
use Aurix\Tests\Fixtures\User;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AurixServiceProvider::class,
            \Laravel\Socialite\SocialiteServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('app.cipher', 'AES-256-CBC');

        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('aurix.api.middleware', ['api']);
        $app['config']->set('aurix.ui.middleware', ['web', 'auth', 'can:aurix.manage-rbac']);
        
        // Configure Socialite for testing
        $app['config']->set('services.facebook', [
            'client_id' => 'test-facebook-id',
            'client_secret' => 'test-facebook-secret',
            'redirect' => 'http://localhost/auth/facebook/callback',
        ]);
        
        $app['config']->set('services.google', [
            'client_id' => 'test-google-id',
            'client_secret' => 'test-google-secret',
            'redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });

        Artisan::call('migrate', ['--database' => 'testing']);
        $this->seed(DemoRbacSeeder::class);
        
        // Register a login route for tests
        Route::get('/login', function () {
            return 'login';
        })->name('login');
        
        Route::get('/dashboard', function () {
            return 'dashboard';
        })->name('dashboard');
    }
}
