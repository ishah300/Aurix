<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Aurix\Tests\TestCase;

class CommandsTest extends TestCase
{
    public function test_install_command_runs_successfully(): void
    {
        $this->artisan('aurix:install')
            ->assertExitCode(0);
    }

    public function test_seed_starter_command_runs_successfully(): void
    {
        $this->artisan('aurix:seed-starter')
            ->assertExitCode(0);
    }
}
