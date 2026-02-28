<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $usersTable = config('aurix.tables.users', 'users');
        $rolesTable = config('aurix.tables.roles', 'roles');
        $pivotTable = config('aurix.tables.user_roles', 'user_roles');

        Schema::create($pivotTable, function (Blueprint $table) use ($usersTable, $rolesTable): void {
            $table->id();
            $table->foreignId('user_id')->constrained($usersTable)->cascadeOnDelete();
            $table->foreignId('role_id')->constrained($rolesTable)->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('aurix.tables.user_roles', 'user_roles'));
    }
};
