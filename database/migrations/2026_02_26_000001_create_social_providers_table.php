<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $table = config(
            'aurix.tables.social_providers',
            (string) config('aurix.social.providers_table', 'aurix_social_providers')
        );

        Schema::create($table, function (Blueprint $tableBlueprint): void {
            $tableBlueprint->id();
            $tableBlueprint->string('slug')->unique();
            $tableBlueprint->string('name');
            $tableBlueprint->text('description')->nullable();
            $tableBlueprint->boolean('is_active')->default(true);
            $tableBlueprint->boolean('enabled')->default(false);
            $tableBlueprint->string('client_id')->nullable();
            $tableBlueprint->text('client_secret')->nullable();
            $tableBlueprint->string('redirect')->nullable();
            $tableBlueprint->text('scopes')->nullable();
            $tableBlueprint->boolean('requires_package')->default(false);
            $tableBlueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            (string) config(
                'aurix.tables.social_providers',
                (string) config('aurix.social.providers_table', 'aurix_social_providers')
            )
        );
    }
};
