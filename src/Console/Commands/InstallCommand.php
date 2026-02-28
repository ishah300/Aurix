<?php

declare(strict_types=1);

namespace Aurix\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'aurix:install
        {--seed : Seed starter roles and menus after migrations}
        {--force : Force publish and migrations in production}
        {--skip-auth-views : Skip publishing Aurix auth view overrides}';

    protected $description = 'Install Aurix: publish config, run migrations, and optionally seed starter RBAC data.';

    public function handle(): int
    {
        $this->components->info('Publishing Aurix configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'aurix-config',
            '--force' => (bool) $this->option('force'),
        ]);

        if (! (bool) $this->option('skip-auth-views')) {
            $this->components->info('Publishing Aurix auth views (overrides /resources/views/auth)...');
            $this->call('vendor:publish', [
                '--tag' => 'aurix-auth-views',
                '--force' => true,
            ]);
        } else {
            $this->components->warn('Skipped publishing Aurix auth view overrides.');
        }

        $this->components->info('Publishing Aurix brand assets...');
        $this->call('vendor:publish', [
            '--tag' => 'aurix-assets',
            '--force' => true,
        ]);

        $this->components->info('Running migrations...');
        $this->call('migrate', [
            '--force' => (bool) $this->option('force'),
        ]);

        if ((bool) $this->option('seed')) {
            $this->components->info('Seeding starter RBAC data...');
            $this->call('aurix:seed-starter', [
                '--force' => (bool) $this->option('force'),
            ]);
        }

        $this->components->info('Aurix installation completed.');

        return self::SUCCESS;
    }
}
