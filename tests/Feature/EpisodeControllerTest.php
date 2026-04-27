<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Episode;
use App\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class EpisodeControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $adminToken;
    private string $userToken;
    private Show $show;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::create([
            'username' => 'admin_test',
            'password' => Hash::Make('password'),
            'role' => Role::ADMIN->value,
            'enabled' => true,
        ]);

        $this->adminToken = JWTAuth::fromUser($admin);

        $user = User::create([
            'username' => 'user_test',
            'password' => Hash::Make('password'),
            'role' => Role::USER->value,
            'enabled' => true,
        ]);

        $this->userToken = JWTAuth::fromUser($user);

        $this->show = Show::create([
            'id_integration' => 1,
            'name' => 'Test Show',
            'type' => 'Scripted',
            'language' => 'English',
            'status' => 'Running',
            'runtime' => 45,
            'average_runtime' => 45,
            'official_site' => null,
            'rating' => 8.0,
            'summary' => 'Test summary.',
        ]);
    }

    public function test_average_by_season(): void
    {
        Episode::create([
            'id_integration' => 101,
            'show_id' => $this->show->id,
            'name' => 'Episode 1',
            'season' => 1,
            'number' => 1,
            'type' => 'regular',
            'airdate' => '2024-01-01',
            'airtime' => '20:00',
            'airstamp' => '2024-01-01T20:00:00+00:00',
            'runtime' => 45,
            'rating' => 8.5,
            'summary' => 'First episode.',
        ]);

        Episode::create([
            'id_integration' => 102,
            'show_id' => $this->show->id,
            'name' => 'Episode 2',
            'season' => 1,
            'number' => 2,
            'type' => 'regular',
            'airdate' => '2024-01-08',
            'airtime' => '20:00',
            'airstamp' => '2024-01-08T20:00:00+00:00',
            'runtime' => 45,
            'rating' => 7.5,
            'summary' => 'Second episode.',
        ]);

        Episode::create([
            'id_integration' => 201,
            'show_id' => $this->show->id,
            'name' => 'Episode 1 - S2',
            'season' => 2,
            'number' => 1,
            'type' => 'regular',
            'airdate' => '2024-06-01',
            'airtime' => '20:00',
            'airstamp' => '2024-06-01T20:00:00+00:00',
            'runtime' => 45,
            'rating' => 9.0,
            'summary' => 'Season 2 premiere.',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/episodes/average?show_id=' . $this->show->id);

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonPath('0.season', 1)
            ->assertJsonPath('0.average', 8)
            ->assertJsonPath('1.season', 2)
            ->assertJsonPath('1.average', 9);
    }

    public function test_average_no_episodes_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/episodes/average?show_id=' . $this->show->id);

        $response->assertStatus(404);
    }

    public function test_user_can_access_average(): void
    {
        Episode::create([
            'id_integration' => 301,
            'show_id' => $this->show->id,
            'name' => 'User Episode',
            'season' => 1,
            'number' => 1,
            'type' => 'regular',
            'airdate' => '2024-03-01',
            'airtime' => '21:00',
            'airstamp' => '2024-03-01T21:00:00+00:00',
            'runtime' => 45,
            'rating' => 8.0,
            'summary' => 'Accessible by user.',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->getJson('/api/episodes/average?show_id=' . $this->show->id);

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.season', 1)
            ->assertJsonPath('0.average', 8);
    }
}
