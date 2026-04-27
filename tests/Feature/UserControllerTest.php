<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $existingId;
    private string $seededUsername;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seededUsername = 'seed_' . uniqid();
        $admin = User::create([
            'username' => $this->seededUsername,
            'password' => Hash::Make('seedpass'),
            'role' => Role::ADMIN->value,
            'enabled' => true,
        ]);
        $this->existingId = $admin->id;
        $this->adminToken = JWTAuth::fromUser($admin);

        foreach (['alberto', 'alex', 'bob'] as $name) {
            User::create([
                'username' => $name,
                'password' => bcrypt('x'),
                'role' => Role::USER->value,
                'enabled' => true,
            ]);
        }
    }

    public function test_list_as_admin_returns_paginated_users(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/users?username=al&page=0&size=2&sortField=username&sortOrder=ASC');

        $response->assertStatus(200)
            ->assertJsonStructure(['items', 'total', 'page', 'size'])
            ->assertJsonPath('total', 2)
            ->assertJsonPath('page', 0)
            ->assertJsonPath('size', 2)
            ->assertJsonPath('items.0.username', 'alberto')
            ->assertJsonPath('items.1.username', 'alex');
    }

    public function test_get_user_by_id(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/users/' . $this->existingId);

        $response->assertStatus(200)
            ->assertJsonPath('id', $this->existingId)
            ->assertJsonPath('username', $this->seededUsername)
            ->assertJsonPath('role', 'ADMIN')
            ->assertJsonPath('enabled', true);
    }

    public function test_create_user_returns_201(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/users', [
                'username' => 'newuser',
                'password' => 'pwd123456',
                'role' => 'USER',
                'enabled' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('username', 'newuser');
    }

    public function test_create_user_conflict_409(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/users', [
                'username' => $this->seededUsername,
                'password' => 'pwd123456',
                'role' => 'USER',
                'enabled' => true,
            ]);

        $response->assertStatus(409);
    }

    public function test_update_user(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson('/api/users/' . $this->existingId, [
                'username' => 'seed-updated',
                'password' => 'newpass',
                'role' => 'USER',
                'enabled' => false,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('username', 'seed-updated')
            ->assertJsonPath('role', 'USER')
            ->assertJsonPath('enabled', false);
    }

    public function test_delete_user_returns_204(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson('/api/users/' . $this->existingId);

        $response->assertStatus(204);
        $this->assertNull(User::find($this->existingId));
    }
}
