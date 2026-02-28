<?php

declare(strict_types=1);

namespace Aurix\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class SetupSocialAuthCommand extends Command
{
    protected $signature = 'aurix:setup-social
                            {--seed : Seed default social providers}
                            {--force : Force the operation to run in production}';

    protected $description = 'Set up social authentication for Aurix';

    public function handle(): int
    {
        $this->info('Setting up Aurix Social Authentication...');
        $this->newLine();

        // Check if migrations are needed
        if (!Schema::hasTable('social_accounts')) {
            $this->info('Running migrations...');
            Artisan::call('migrate', ['--force' => $this->option('force')]);
            $this->info('✓ Migrations completed');
            $this->newLine();
        } else {
            $this->info('✓ Migrations already run');
            $this->newLine();
        }

        // Seed providers if requested
        if ($this->option('seed')) {
            $this->info('Seeding social providers...');
            Artisan::call('db:seed', [
                '--class' => 'Aurix\\Database\\Seeders\\SocialProvidersSeeder',
                '--force' => $this->option('force')
            ]);
            $this->info('✓ Providers seeded');
            $this->newLine();
        }

        // Display next steps
        $this->info('Social authentication setup complete!');
        $this->newLine();
        
        $this->comment('Next steps:');
        $this->line('1. Add HasSocialAccounts trait to your User model:');
        $this->line('   use Aurix\Models\Concerns\HasSocialAccounts;');
        $this->newLine();
        
        $this->line('2. Configure providers in admin panel:');
        $this->line('   Visit: /auth/rbac/setup (Social Providers tab)');
        $this->newLine();
        
        $this->line('3. Set up OAuth apps:');
        $this->line('   - Facebook: https://developers.facebook.com/');
        $this->line('   - Google: https://console.cloud.google.com/');
        $this->line('   - GitHub: https://github.com/settings/developers');
        $this->newLine();
        
        $this->line('4. Add credentials to each provider in admin panel');
        $this->newLine();
        
        $this->line('For detailed setup instructions, see:');
        $this->line('   docs/SOCIAL_AUTH_SETUP.md');
        $this->newLine();

        return self::SUCCESS;
    }
}
