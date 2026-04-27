<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ShowDTO",
 *     description="Response da sincronização de TV shows",
 *     @OA\Property(property="id", type="string", description="Id do tv show"),
 *     @OA\Property(property="name", type="string", description="Nome do tv show"),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="language", type="string"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="runtime", type="integer"),
 *     @OA\Property(property="averageRuntime", type="integer"),
 *     @OA\Property(property="officialSite", type="string"),
 *     @OA\Property(property="rating", type="number", format="float"),
 *     @OA\Property(property="summary", type="string")
 * )
 */
class ShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'language' => $this->language,
            'status' => $this->status,
            'runtime' => $this->runtime,
            'averageRuntime' => $this->average_runtime,
            'officialSite' => $this->official_site,
            'rating' => $this->rating !== null ? (float) $this->rating : null,
            'summary' => $this->summary,
        ];
    }
}
