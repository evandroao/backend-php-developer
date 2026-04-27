<?php

namespace App\Services;

use App\Exceptions\AlreadyExistsException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Lista todos os usuários.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function list()
    {
        return User::all();
    }

    /**
     * Busca um usuário pelo ID.
     *
     * @param string $id
     * @return User
     * @throws ModelNotFoundException
     */
    public function findById(string $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Cria um novo usuário.
     *
     * @param array $data
     * @return User
     * @throws AlreadyExistsException
     */
    public function create(array $data): User
    {
        if (User::where('username', $data['username'])->exists()) {
            throw new AlreadyExistsException('User', $data['username']);
        }

        return User::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'enabled' => $data['enabled'],
        ]);
    }

    /**
     * Atualiza um usuário existente.
     *
     * @param string $id
     * @param array $data
     * @return User
     * @throws ModelNotFoundException
     * @throws AlreadyExistsException
     */
    public function update(string $id, array $data): User
    {
        $user = User::findOrFail($id);

        if (!empty($data['username'])) {
            $existing = User::where('username', $data['username'])
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                throw new AlreadyExistsException('User', $data['username']);
            }
            $user->username = $data['username'];
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if (isset($data['role'])) {
            $user->role = $data['role'];
        }

        if (isset($data['enabled'])) {
            $user->enabled = $data['enabled'];
        }

        $user->save();
        return $user;
    }

    /**
     * Remove um usuário.
     *
     * @param string $id
     * @return void
     */
    public function delete(string $id): void
    {
        User::findOrFail($id)->delete();
    }

    /**
     * Busca usuários por username com paginação e ordenação.
     *
     * @param string $username Filtro por username (LIKE)
     * @param int $page Número da página (zero-indexed)
     * @param int $size Tamanho da página
     * @param string $sortField Campo de ordenação
     * @param string $sortOrder Direção (ASC ou DESC)
     * @return LengthAwarePaginator
     */
    public function findByUsernameContaining(
        string $username,
        int $page,
        int $size,
        string $sortField = 'id',
        string $sortOrder = 'ASC'
    ): LengthAwarePaginator {
        $allowedFields = ['id', 'username', 'role', 'enabled', 'created_at', 'updated_at'];
        $allowedOrders = ['ASC', 'DESC'];

        if (!in_array(strtolower($sortField), array_map('strtolower', $allowedFields), true)) {
            $sortField = 'id';
        }

        $sortOrder = in_array(strtoupper($sortOrder), $allowedOrders, true)
            ? strtoupper($sortOrder)
            : 'ASC';

        return User::where('username', 'ILIKE', "%{$username}%")
            ->orderBy($sortField, $sortOrder)
            ->paginate($size, ['*'], 'page', $page + 1);
    }
}
