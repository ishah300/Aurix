<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Aurix\Models\Menu;
use Aurix\Tests\TestCase;

class RouteAuditCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth'])->group(function (): void {
            Route::get('/audit-covered', fn () => 'ok')->name('audit.covered');
            Route::get('/audit-uncovered', fn () => 'ok')->name('audit.uncovered');
        });
    }

    public function test_audit_routes_reports_issues_in_json_output(): void
    {
        Menu::query()->create([
            'name' => 'Audit Covered',
            'slug' => 'audit-covered',
            'route' => '/audit-covered',
            'sort_order' => 1001,
            'is_active' => true,
        ]);

        Menu::query()->create([
            'name' => 'Audit Missing',
            'slug' => 'audit-missing',
            'route' => '/audit-missing',
            'sort_order' => 1002,
            'is_active' => true,
        ]);

        $exitCode = Artisan::call('aurix:audit-routes', ['--json' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('"menus_without_matching_route"', $output);
        $this->assertStringContainsString('"uncovered_authenticated_web_routes"', $output);
        $this->assertStringContainsString('/audit-uncovered', $output);
    }

    public function test_audit_routes_can_fail_when_issues_are_found(): void
    {
        Menu::query()->create([
            'name' => 'Audit Missing',
            'slug' => 'audit-missing-2',
            'route' => '/audit-missing-2',
            'sort_order' => 1003,
            'is_active' => true,
        ]);

        $this->artisan('aurix:audit-routes --fail-on-issues')
            ->assertExitCode(1);
    }
}
