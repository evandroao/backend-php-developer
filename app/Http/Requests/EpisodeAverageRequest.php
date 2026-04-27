<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="EpisodeAverageRequest",
 *     description="Query params para cálculo de média por temporada",
 *     @OA\Property(property="show_id", type="string", description="ID do show (UUID)")
 * )
 */
class EpisodeAverageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'show_id' => 'required|string|uuid',
        ];
    }
}
