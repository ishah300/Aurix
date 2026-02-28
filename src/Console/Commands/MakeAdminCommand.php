<?php

declare(strict_types=1);

namespace Aurix\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Aurix\Models\Role;

class MakeAdminCommand extends Command
{
    protected $signature = 'aurix:make-admin
        {email? : User email to grant admin role}
        {--create : Create the user if they do not exist}
        {--name= : Name used only when creating a missing user}
        {--password= : Password used only when creating a missing user}';

    protected $description = 'Assign admin role to an existing user, or create a new user and assign admin.';

    public function handle(): int
    {
        $email = trim((string) ($this->argument('email') ?? ''));
        if ($email === '') {
            $email = trim((string) $this->ask('User email'));
        }

        if ($email === '') {
            $this->components->error('Email is required.');

            return self::FAILURE;
        }

        $userModel = (string) config('auth.providers.users.model');
        if ($userModel === '' || ! class_exists($userModel)) {
            $this->components->error('Unable to resolve user model from auth.providers.users.model.');

            return self::FAILURE;
        }

        /** @var class-string<Model> $userModel */
        $user = $userModel::query()->where('email', $email)->first();
        if (! $user) {
            if (! (bool) $this->option('create')) {
                $this->components->error('User not found. Re-run with --create to create and assign admin.');

                return self::FAILURE;
            }

            $name = trim((string) ($this->option('name') ?: $this->ask('Name')));
            if ($name === '') {
                $this->components->error('Name is required when using --create.');

                return self::FAILURE;
            }

            $password = (string) ($this->option('password') ?: $this->secret('Password'));
            if ($password === '') {
                $this->components->error('Password is required when using --create.');

                return self::FAILURE;
            }

            $user = $userModel::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $this->components->info('User created.');
        }

        $adminRole = Role::query()->firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin']
        );

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('admin');
        } else {
            $tables = config('aurix.tables');
            $pivot = (string) ($tables['user_roles'] ?? 'user_roles');
            $connection = (string) config('aurix.database.connection');
            $query = DB::connection($connection)->table($pivot);

            $exists = $query
                ->where('user_id', $user->getKey())
                ->where('role_id', $adminRole->getKey())
                ->exists();

            if (! $exists) {
                $query->insert([
                    'user_id' => $user->getKey(),
                    'role_id' => $adminRole->getKey(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->components->info('Admin role assigned to: ' . (string) $user->email);

        return self::SUCCESS;
    }
}

