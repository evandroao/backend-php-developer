<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="EpisodeDTO",
 *     description="Objeto da representação de Episódios",
 *     @OA\Property(property="id", type="string", description="Id"),
 *     @OA\Property(property="name", type="string", description="Nome"),
 *     @OA\Property(property="season", type="integer", description="Temporada"),
 *     @OA\Property(property="number", type="integer", description="Episódio"),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="airdate", type="string"),
 *     @OA\Property(property="airtime", type="string"),
 *     @OA\Property(property="airstamp", type="string"),
 *     @OA\Property(property="runtime", type="integer"),
 *     @OA\Property(property="rating", type="number", format="float"),
 *     @OA\Property(property="summary", type="string")
 * )
 */
class EpisodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'season' => $this->season,
            'number' => $this->number,
            'type' => $this->type,
            'airdate' => $this->airdate,
            'airtime' => $this->airtime,
            'airstamp' => $this->airstamp,
            'runtime' => $this->runtime,
            'rating' => $this->rating !== null ? (float) $this->rating : null,
            'summary' => $this->summary,
        ];
    }
}
