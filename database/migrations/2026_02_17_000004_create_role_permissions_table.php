<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $rolesTable = config('aurix.tables.roles', 'roles');
        $permissionsTable = config('aurix.tables.permissions', 'permissions');
        $pivotTable = config('aurix.tables.role_permissions', 'role_permissions');

        Schema::create($pivotTable, function (Blueprint $table) use ($rolesTable, $permissionsTable): void {
            $table->id();
            $table->foreignId('role_id')->constrained($rolesTable)->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained($permissionsTable)->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('aurix.tables.role_permissions', 'role_permissions'));
    }
};
