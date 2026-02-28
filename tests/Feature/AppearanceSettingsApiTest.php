<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class AppearanceSettingsApiTest extends TestCase
{
    public function test_admin_can_get_and_update_appearance_settings(): void
    {
        $admin = User::query()->create([
            'name' => 'Appearance Admin',
            'email' => 'appearance-admin@example.com',
            'password' => bcrypt('secret'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $this->get('/api/auth/settings/appearance')
            ->assertOk()
            ->assertJsonStructure(['data' => ['text_color', 'button_color', 'heading_alignment']]);

        $this->putJson('/api/auth/settings/appearance', [
            'text_color' => '#123456',
            'button_color' => '#111111',
            'button_text_color' => '#ffffff',
            'heading_alignment' => 'center',
            'custom_css' => '.la-card{border-radius:18px;}',
        ])->assertOk()
            ->assertJsonPath('data.text_color', '#123456')
            ->assertJsonPath('data.heading_alignment', 'center');

        $this->assertDatabaseHas(config('aurix.tables.settings', 'aurix_settings'), [
            'key' => 'text_color',
            'value' => '#123456',
        ]);
    }
}

