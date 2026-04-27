<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Episode;
use App\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShowControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::create([
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role' => Role::ADMIN->value,
            'enabled' => true,
        ]);

        $this->adminToken = JWTAuth::fromUser($admin);
    }

    public function test_list_shows_returns_paginated(): void
    {
        Show::create([
            'id_integration' => 1,
            'name' => 'Game of Thrones',
            'type' => 'Scripted',
            'language' => 'English',
            'status' => 'Ended',
            'runtime' => 60,
            'average_runtime' => 60,
            'official_site' => 'https://example.com',
            'rating' => 9.3,
            'summary' => 'A story of fire and ice.',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/shows?page=0&size=10');

        $response->assertStatus(200)
            ->assertJsonStructure(['items', 'total', 'page', 'size'])
            ->assertJsonPath('total', 1);
    }

    public function test_get_show_by_id(): void
    {
        $show = Show::create([
            'id_integration' => 2,
            'name' => 'Breaking Bad',
            'type' => 'Scripted',
            'language' => 'English',
            'status' => 'Ended',
            'runtime' => 45,
            'average_runtime' => 45,
            'official_site' => 'https://example.com',
            'rating' => 9.5,
            'summary' => 'A chemistry teacher turns to cooking meth.',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/shows/' . $show->id);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Breaking Bad')
            ->assertJsonPath('rating', 9.5);
    }

    public function test_sync_show_creates_show_and_episodes(): void
    {
        Http::fake([
            'api.tvmaze.com/*' => Http::response([
                'id' => 123,
                'name' => 'Mocked Show',
                'type' => 'Scripted',
                'language' => 'English',
                'status' => 'Running',
                'runtime' => 45,
                'averageRuntime' => 45,
                'officialSite' => 'https://mocked.com',
                'rating' => ['average' => 8.5],
                'summary' => 'A mocked show for testing.',
                '_embedded' => [
                    'episodes' => [
                        [
                            'id' => 1001,
                            'name' => 'Pilot',
                            'season' => 1,
                            'number' => 1,
                            'type' => 'regular',
                            'airdate' => '2024-01-01',
                            'airtime' => '20:00',
                            'airstamp' => '2024-01-01T20:00:00+00:00',
                            'runtime' => 45,
                            'rating' => ['average' => 8.0],
                            'summary' => 'First episode.',
                        ],
                        [
                            'id' => 1002,
                            'name' => 'Episode 2',
                            'season' => 1,
                            'number' => 2,
                            'type' => 'regular',
                            'airdate' => '2024-01-08',
                            'airtime' => '20:00',
                            'airstamp' => '2024-01-08T20:00:00+00:00',
                            'runtime' => 45,
                            'rating' => ['average' => 8.5],
                            'summary' => 'Second episode.',
                        ],
                    ],
                ],
            ]),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows', [
                'name' => 'Mocked Show',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Mocked Show')
            ->assertJsonPath('rating', 8.5);

        $this->assertDatabaseHas('shows', [
            'id_integration' => 123,
            'name' => 'Mocked Show',
        ]);

        $this->assertDatabaseHas('episodes', [
            'id_integration' => 1001,
            'name' => 'Pilot',
        ]);

        $this->assertDatabaseHas('episodes', [
            'id_integration' => 1002,
            'name' => 'Episode 2',
        ]);

        $this->assertEquals(2, Episode::count());
    }

    public function test_sync_show_already_exists_updates_show_and_episodes(): void
    {
        $show = Show::create([
            'id_integration' => 123,
            'name' => 'Existing Show',
            'type' => 'Scripted',
            'language' => 'English',
            'status' => 'Running',
            'runtime' => 45,
            'average_runtime' => 45,
            'official_site' => null,
            'rating' => 8.0,
            'summary' => 'Already in DB.',
        ]);

        Episode::create([
            'id_integration' => 9999,
            'show_id' => $show->id,
            'name' => 'Old Orphan Episode',
            'season' => 1,
            'number' => 99,
            'type' => 'regular',
            'airdate' => '2023-01-01',
            'airtime' => '20:00',
            'airstamp' => '2023-01-01T20:00:00+00:00',
            'runtime' => 30,
            'rating' => 5.0,
            'summary' => 'Should be removed.',
        ]);

        Http::fake([
            'api.tvmaze.com/*' => Http::response([
                'id' => 123,
                'name' => 'Updated Show Name',
                'type' => 'Scripted',
                'language' => 'English',
                'status' => 'Ended',
                'runtime' => 50,
                'averageRuntime' => 50,
                'officialSite' => 'https://updated.com',
                'rating' => ['average' => 9.0],
                'summary' => 'Updated summary.',
                '_embedded' => [
                    'episodes' => [
                        [
                            'id' => 1001,
                            'name' => 'Pilot',
                            'season' => 1,
                            'number' => 1,
                            'type' => 'regular',
                            'airdate' => '2024-01-01',
                            'airtime' => '20:00',
                            'airstamp' => '2024-01-01T20:00:00+00:00',
                            'runtime' => 50,
                            'rating' => ['average' => 9.0],
                            'summary' => 'Updated episode summary.',
                        ],
                    ],
                ],
            ]),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows', [
                'name' => 'Existing Show',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Updated Show Name')
            ->assertJsonPath('status', 'Ended')
            ->assertJsonPath('rating', 9);

        $this->assertDatabaseHas('shows', [
            'id_integration' => 123,
            'name' => 'Updated Show Name',
            'status' => 'Ended',
        ]);

        $this->assertDatabaseHas('episodes', [
            'id_integration' => 1001,
            'name' => 'Pilot',
        ]);

        $this->assertDatabaseMissing('episodes', [
            'id_integration' => 9999,
            'name' => 'Old Orphan Episode',
        ]);

        $this->assertEquals(1, Episode::count());
    }

    public function test_sync_show_not_found_returns_404(): void
    {
        Http::fake([
            'api.tvmaze.com/*' => Http::response(null, 404),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows', [
                'name' => 'ShowQueNaoExiste12345',
            ]);

        $response->assertStatus(404);
    }
}
