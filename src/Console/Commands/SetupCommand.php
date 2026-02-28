<?php

declare(strict_types=1);

namespace Aurix\Console\Commands;

use Illuminate\Console\Command;

class SetupCommand extends Command
{
    protected $signature = 'aurix:setup
        {--force : Force actions in production}
        {--no-seed : Skip starter data seeding}
        {--skip-auth-views : Skip publishing Aurix auth view overrides}
        {--skip-admin : Skip first admin bootstrap}
        {--admin-email= : Email for first admin user}
        {--admin-name= : Name when creating admin user}
        {--admin-password= : Password when creating admin user}
        {--create-admin : Create admin user if not found}';

    protected $description = 'Guided Aurix setup: install package, optionally seed data, and bootstrap first admin.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $seed = ! (bool) $this->option('no-seed');
        $skipAdmin = (bool) $this->option('skip-admin');

        $this->components->info('Running Aurix install...');
        $installCode = $this->call('aurix:install', [
            '--force' => $force,
            '--seed' => $seed,
            '--skip-auth-views' => (bool) $this->option('skip-auth-views'),
        ]);

        if ($installCode !== self::SUCCESS) {
            return $installCode;
        }

        if ($skipAdmin) {
            $this->components->warn('Admin bootstrap skipped by option.');

            return self::SUCCESS;
        }

        $email = trim((string) $this->option('admin-email'));

        if ($email === '' && $this->input->isInteractive()) {
            if (! $this->confirm('Bootstrap first admin now?', true)) {
                $this->components->warn('Admin bootstrap skipped.');

                return self::SUCCESS;
            }

            $email = trim((string) $this->ask('Admin email'));
        }

        if ($email === '') {
            $this->components->warn('No admin email provided. Skipping admin bootstrap.');
            $this->line('Use: php artisan aurix:make-admin your-email@example.com');

            return self::SUCCESS;
        }

        $name = (string) $this->option('admin-name');
        $password = (string) $this->option('admin-password');
        $createAdmin = (bool) $this->option('create-admin') || $name !== '' || $password !== '';

        $makeAdminArgs = [
            'email' => $email,
            '--create' => $createAdmin,
        ];

        if ($name !== '') {
            $makeAdminArgs['--name'] = $name;
        }

        if ($password !== '') {
            $makeAdminArgs['--password'] = $password;
        }

        $this->components->info('Bootstrapping first admin...');
        $makeAdminCode = $this->call('aurix:make-admin', $makeAdminArgs);
        if ($makeAdminCode !== self::SUCCESS) {
            return $makeAdminCode;
        }

        $this->components->info('Aurix setup completed.');

        return self::SUCCESS;
    }
}
