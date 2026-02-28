<?php

declare(strict_types=1);

namespace Aurix\Tests\Feature;

use Aurix\Models\SocialProvider;
use Aurix\Tests\Fixtures\User;
use Aurix\Tests\TestCase;

class SocialProviderApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed social providers
        SocialProvider::create([
            'slug' => 'facebook',
            'name' => 'Facebook',
            'is_active' => true,
            'enabled' => false,
            'requires_package' => false,
        ]);

        SocialProvider::create([
            'slug' => 'google',
            'name' => 'Google',
            'is_active' => true,
            'enabled' => false,
            'requires_package' => false,
        ]);

        SocialProvider::create([
            'slug' => 'github',
            'name' => 'GitHub',
            'is_active' => true,
            'enabled' => false,
            'requires_package' => false,
        ]);
    }

    public function test_non_admin_cannot_access_provider_endpoints(): void
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $provider = SocialProvider::where('slug', 'facebook')->first();

        $this->getJson('/api/auth/providers')->assertOk(); // List is public
        $this->putJson('/api/auth/providers/' . $provider->id, [])->assertForbidden();
        $this->postJson('/api/auth/providers/' . $provider->id . '/toggle')->assertForbidden();
        $this->postJson('/api/auth/providers/seed')->assertForbidden();
    }

    public function test_admin_can_list_providers(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->getJson('/api/auth/providers');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'slug', 'name', 'enabled', 'is_active', 'coming_soon'],
                ],
            ]);

        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
        $this->assertTrue((bool) collect($response->json('data'))->firstWhere('slug', 'facebook')['coming_soon']);
        $this->assertFalse((bool) collect($response->json('data'))->firstWhere('slug', 'google')['coming_soon']);
        $this->assertFalse((bool) collect($response->json('data'))->firstWhere('slug', 'github')['coming_soon']);
    }

    public function test_admin_can_update_provider_configuration(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $provider = SocialProvider::where('slug', 'github')->first();

        $response = $this->putJson('/api/auth/providers/' . $provider->id, [
            'enabled' => true,
            'client_id' => 'new-github-app-id',
            'client_secret' => 'new-github-secret',
            'redirect' => 'http://localhost/auth/github/callback',
            'scopes' => 'read:user,user:email',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.enabled', true)
            ->assertJsonPath('data.client_id', 'new-github-app-id')
            ->assertJsonPath('data.client_secret', '********'); // Should be masked

        $provider->refresh();
        $this->assertTrue($provider->enabled);
        $this->assertEquals('new-github-app-id', $provider->client_id);
        $this->assertEquals('http://localhost/auth/github/callback', $provider->redirect);
        $this->assertEquals('read:user,user:email', $provider->scopes);
    }

    public function test_client_secret_is_encrypted_in_database(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $provider = SocialProvider::where('slug', 'google')->first();

        $this->putJson('/api/auth/providers/' . $provider->id, [
            'client_secret' => 'super-secret-key',
        ]);

        $provider->refresh();

        // Raw attribute should be encrypted
        $rawSecret = $provider->getAttributes()['client_secret'];
        $this->assertNotEquals('super-secret-key', $rawSecret);

        // Should be able to decrypt
        $decrypted = $provider->revealSecret();
        $this->assertEquals('super-secret-key', $decrypted);
    }

    public function test_empty_client_secret_is_not_updated(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $provider = SocialProvider::where('slug', 'github')->first();
        
        // Set original secret using the model's setter (which encrypts it)
        $provider->update(['client_secret' => 'original-secret']);
        $provider->refresh();
        
        // Verify it was set
        $this->assertEquals('original-secret', $provider->revealSecret());

        // Try to update with empty string
        $response = $this->putJson('/api/auth/providers/' . $provider->id, [
            'client_id' => 'updated-id',
            'client_secret' => '', // Empty string should not update
        ]);

        $response->assertOk();
        
        $provider->refresh();
        
        // Secret should still be the original
        $this->assertEquals('original-secret', $provider->revealSecret());
        // But client_id should be updated
        $this->assertEquals('updated-id', $provider->client_id);
    }

    public function test_admin_can_toggle_provider(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $provider = SocialProvider::where('slug', 'github')->first();
        $this->assertFalse($provider->enabled);

        $response = $this->postJson('/api/auth/providers/' . $provider->id . '/toggle');

        $response->assertOk()
            ->assertJsonPath('data.enabled', true);

        $provider->refresh();
        $this->assertTrue($provider->enabled);

        // Toggle again
        $this->postJson('/api/auth/providers/' . $provider->id . '/toggle');
        $provider->refresh();
        $this->assertFalse($provider->enabled);
    }

    public function test_admin_cannot_enable_coming_soon_provider(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $provider = SocialProvider::where('slug', 'facebook')->first();
        $response = $this->postJson('/api/auth/providers/' . $provider->id . '/toggle');
        $response->assertStatus(422)
            ->assertJsonPath('message', 'This provider is coming soon and cannot be enabled yet.');
    }

    public function test_admin_can_seed_default_providers(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        // Clear existing providers
        SocialProvider::query()->delete();

        $response = $this->postJson('/api/auth/providers/seed');

        $response->assertOk()
            ->assertJsonPath('message', 'Default providers seeded.');

        $this->assertDatabaseHas('aurix_social_providers', ['slug' => 'facebook']);
        $this->assertDatabaseHas('aurix_social_providers', ['slug' => 'google']);
        $this->assertDatabaseHas('aurix_social_providers', ['slug' => 'github']);
    }

    public function test_provider_show_endpoint_works(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $provider = SocialProvider::where('slug', 'google')->first();

        $response = $this->getJson('/api/auth/providers/' . $provider->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $provider->id)
            ->assertJsonPath('data.slug', 'google')
            ->assertJsonPath('data.name', 'Google');
    }

    public function test_provider_validation_works(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $provider = SocialProvider::where('slug', 'github')->first();

        // Test invalid data types
        $response = $this->putJson('/api/auth/providers/' . $provider->id, [
            'enabled' => 'not-a-boolean',
        ]);

        $response->assertStatus(422);
    }
}
