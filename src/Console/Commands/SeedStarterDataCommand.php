<?php

declare(strict_types=1);

namespace Aurix\Console\Commands;

use Illuminate\Console\Command;
use Aurix\Database\Seeders\DemoRbacSeeder;

class SeedStarterDataCommand extends Command
{
    protected $signature = 'aurix:seed-starter {--force : Force seeding in production}';

    protected $description = 'Seed starter roles, menus, and role-menu permissions for Aurix.';

    public function handle(): int
    {
        $this->call('db:seed', [
            '--class' => DemoRbacSeeder::class,
            '--force' => (bool) $this->option('force'),
        ]);

        $this->components->info('Starter RBAC data seeded.');

        return self::SUCCESS;
    }
}
