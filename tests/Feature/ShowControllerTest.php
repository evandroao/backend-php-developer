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
    private string $userToken;

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

        $user = User::create([
            'username' => 'user_test',
            'password' => bcrypt('password'),
            'role' => Role::USER->value,
            'enabled' => true,
        ]);

        $this->userToken = JWTAuth::fromUser($user);
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

    public function test_user_can_list_shows(): void
    {
        Show::create([
            'id_integration' => 5,
            'name' => 'User Accessible Show',
            'type' => 'Scripted',
            'language' => 'English',
            'status' => 'Running',
            'runtime' => 45,
            'average_runtime' => 45,
            'official_site' => null,
            'rating' => 7.0,
            'summary' => 'A show for user test.',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->getJson('/api/shows?page=0&size=10');

        $response->assertStatus(200)
            ->assertJsonPath('total', 1);
    }

    public function test_user_can_get_show_by_id(): void
    {
        $show = Show::create([
            'id_integration' => 6,
            'name' => 'Dexter',
            'type' => 'Scripted',
            'language' => 'English',
            'status' => 'Ended',
            'runtime' => 50,
            'average_runtime' => 50,
            'official_site' => null,
            'rating' => 8.5,
            'summary' => 'A show about a blood spatter analyst.',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->getJson('/api/shows/' . $show->id);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Dexter');
    }

    public function test_user_cannot_sync_show_returns_403(): void
    {
        Http::fake([
            'api.tvmaze.com/*' => Http::response([
                'id' => 777,
                'name' => 'Blocked Show',
                'type' => 'Scripted',
                'language' => 'English',
                'status' => 'Running',
                'runtime' => 45,
                'averageRuntime' => 45,
                'officialSite' => null,
                'rating' => ['average' => 7.0],
                'summary' => 'Should not be saved.',
                '_embedded' => ['episodes' => []],
            ]),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->postJson('/api/shows', [
                'name' => 'Blocked Show',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('error', 'Forbidden');

        $this->assertDatabaseMissing('shows', [
            'id_integration' => 777,
        ]);
    }

    public function test_get_show_by_id_includes_episodes(): void
    {
        $show = Show::create([
            'id_integration' => 10,
            'name' => 'The Office',
            'type' => 'Scripted',
            'language' => 'English',
            'status' => 'Ended',
            'runtime' => 30,
            'average_runtime' => 30,
            'official_site' => null,
            'rating' => 8.5,
            'summary' => 'Mockumentary sitcom.',
        ]);

        Episode::create([
            'id_integration' => 5001,
            'show_id' => $show->id,
            'name' => 'Pilot',
            'season' => 1,
            'number' => 1,
            'type' => 'regular',
            'airdate' => '2005-03-24',
            'airtime' => '21:00',
            'airstamp' => '2005-03-24T21:00:00+00:00',
            'runtime' => 30,
            'rating' => 8.0,
            'summary' => 'First episode.',
        ]);

        Episode::create([
            'id_integration' => 5002,
            'show_id' => $show->id,
            'name' => 'Diversity Day',
            'season' => 1,
            'number' => 2,
            'type' => 'regular',
            'airdate' => '2005-03-29',
            'airtime' => '21:00',
            'airstamp' => '2005-03-29T21:00:00+00:00',
            'runtime' => 30,
            'rating' => 8.5,
            'summary' => 'Second episode.',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/shows/' . $show->id);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'The Office')
            ->assertJsonCount(2, 'episodes')
            ->assertJsonPath('episodes.0.name', 'Pilot')
            ->assertJsonPath('episodes.0.season', 1)
            ->assertJsonPath('episodes.0.number', 1)
            ->assertJsonPath('episodes.0.type', 'regular')
            ->assertJsonPath('episodes.0.airdate', '2005-03-24')
            ->assertJsonPath('episodes.0.rating', 8)
            ->assertJsonPath('episodes.0.summary', 'First episode.')
            ->assertJsonPath('episodes.1.name', 'Diversity Day');
    }
}
