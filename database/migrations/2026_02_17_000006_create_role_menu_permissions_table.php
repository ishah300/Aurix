<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $rolesTable = config('aurix.tables.roles', 'roles');
        $menusTable = config('aurix.tables.menus', 'menus');
        $pivotTable = config('aurix.tables.role_menu_permissions', 'role_menu_permissions');

        Schema::create($pivotTable, function (Blueprint $table) use ($rolesTable, $menusTable): void {
            $table->id();
            $table->foreignId('role_id')->constrained($rolesTable)->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained($menusTable)->cascadeOnDelete();
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->timestamps();
            $table->unique(['role_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('aurix.tables.role_menu_permissions', 'role_menu_permissions'));
    }
};
