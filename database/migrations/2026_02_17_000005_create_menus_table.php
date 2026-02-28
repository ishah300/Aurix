<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $permissionsTable = config('aurix.tables.permissions', 'permissions');
        $menusTable = config('aurix.tables.menus', 'menus');

        Schema::create($menusTable, function (Blueprint $table) use ($permissionsTable, $menusTable): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('route')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained($menusTable)->nullOnDelete();
            $table->foreignId('permission_id')->nullable()->constrained($permissionsTable)->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('aurix.tables.menus', 'menus'));
    }
};
