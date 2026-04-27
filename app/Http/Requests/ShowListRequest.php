<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ShowListRequest",
 *     description="Query params para listagem de shows",
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="page", type="integer", minimum=0),
 *     @OA\Property(property="size", type="integer", minimum=1, maximum=100)
 * )
 */
class ShowListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:265',
            'page' => 'sometimes|integer|min:0',
            'size' => 'sometimes|integer|min:1|max:100',
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
