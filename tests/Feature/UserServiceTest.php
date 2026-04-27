<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Exceptions\AlreadyExistsException;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;
    private string $existingId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserService::class);

        $user = User::create([
            'username' => 'seed',
            'password' => bcrypt('seedpass'),
            'role' => Role::ADMIN->value,
            'enabled' => true,
        ]);
        $this->existingId = $user->id;
    }

    public function test_create_saves_user_and_hashes_password(): void
    {
        $user = $this->service->create([
            'username' => 'alice',
            'password' => 'pwd123',
            'role' => Role::USER->value,
            'enabled' => true,
        ]);

        $this->assertNotNull($user->id);
        $this->assertEquals('alice', $user->username);
        $this->assertTrue($user->enabled);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('pwd123', $user->password));
    }

    public function test_create_throws_when_username_already_exists(): void
    {
        $this->expectException(AlreadyExistsException::class);

        $this->service->create([
            'username' => 'seed',
            'password' => 'pwd123',
            'role' => Role::USER->value,
            'enabled' => true,
        ]);
    }

    public function test_update_changes_fields(): void
    {
        $updated = $this->service->update($this->existingId, [
            'username' => 'seed-upd',
            'password' => 'newpass',
            'role' => Role::USER->value,
            'enabled' => false,
        ]);

        $this->assertEquals('seed-upd', $updated->username);
        $this->assertEquals(Role::USER->value, $updated->role);
        $this->assertFalse($updated->enabled);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpass', $updated->password));
    }

    public function test_find_by_id_returns_existing_user(): void
    {
        $found = $this->service->findById($this->existingId);
        $this->assertEquals('seed', $found->username);
    }

    public function test_find_by_id_throws_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->service->findById(Str::uuid());
    }

    public function test_find_by_username_containing_with_pagination(): void
    {
        $this->service->create(['username' => 'alex', 'password' => 'x12345', 'role' => Role::USER->value, 'enabled' => true]);
        $this->service->create(['username' => 'alberto', 'password' => 'x12345', 'role' => Role::USER->value, 'enabled' => true]);
        $this->service->create(['username' => 'bob', 'password' => 'x12345', 'role' => Role::USER->value, 'enabled' => true]);

        $page = $this->service->findByUsernameContaining('al', 0, 2, 'username', 'ASC');

        $this->assertEquals(2, $page->total());
        $usernames = collect($page->items())->pluck('username')->toArray();
        $this->assertEquals(['alberto', 'alex'], $usernames);
    }

    public function test_delete_removes_user(): void
    {
        $this->service->delete($this->existingId);
        $this->assertNull(User::find($this->existingId));
    }

    public function test_update_throws_when_changing_to_existing_username(): void
    {
        $this->service->create(['username' => 'bob', 'password' => 'x12345', 'role' => Role::USER->value, 'enabled' => true]);

        $this->expectException(AlreadyExistsException::class);
        $this->service->update($this->existingId, [
            'username' => 'bob',
        ]);
    }
}
