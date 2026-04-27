<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            "username" => "alice",
            "password" => Hash::Make("password"),
            "role" => Role::ADMIN->value,
            "enabled" => true,
        ]);

        User::create([
            "username" => "bob",
            "password" => Hash::Make("password"),
            "role" => Role::USER->value,
            "enabled" => true,
        ]);
    }

    public function test_login_returns_jwt_token(): void
    {
        $response = $this->postJson("/api/auth/login", [
            "username" => "alice",
            "password" => "password",
        ]);

        $response->assertStatus(200)->assertJsonStructure(["token"]);

        $token = $response->json("token");
        $this->assertNotEmpty($token);
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        $response = $this->postJson("/api/auth/login", [
            "username" => "alice",
            "password" => "wrong",
        ]);

        $response->assertStatus(401);
    }
}
