<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UserListRequest",
 *     description="Query params para listagem de usuários",
 *     @OA\Property(property="username", type="string"),
 *     @OA\Property(property="page", type="integer", minimum=0),
 *     @OA\Property(property="size", type="integer", minimum=1, maximum=100),
 *     @OA\Property(property="sortField", type="string", enum={"id", "username", "role", "enabled", "created_at", "updated_at"}),
 *     @OA\Property(property="sortOrder", type="string", enum={"ASC", "DESC"})
 * )
 */
class UserListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'sometimes|string|max:255',
            'page' => 'sometimes|integer|min:0',
            'size' => 'sometimes|integer|min:1|max:100',
            'sortField' => 'sometimes|string|in:id,username,role,enabled,created_at,updated_at',
            'sortOrder' => 'sometimes|string|in:ASC,DESC,asc,desc',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('page') && is_string($this->page)) {
            $this->merge(['page' => (int) $this->page]);
        }
        if ($this->has('size') && is_string($this->size)) {
            $this->merge(['size' => (int) $this->size]);
        }
    }
}
