<?php

namespace App\Integration\DTO;

/**
 * DTO para representação de Episódios da API TVMaze.
 * Equivalente ao EpisodeRequestDTO.java do projeto Spring Boot.
 */
class EpisodeRequestDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?int $season = null,
        public readonly ?int $number = null,
        public readonly ?string $type = null,
        public readonly ?string $airdate = null,
        public readonly ?string $airtime = null,
        public readonly ?string $airstamp = null,
        public readonly ?int $runtime = null,
        public readonly ?RatingDTO $rating = null,
        public readonly ?string $summary = null,
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null) {
            return null;
        }

        $id = $data['id'] ?? null;
        if (!is_numeric($id)) {
            return null;
        }

        $season = $data['season'] ?? null;
        $number = $data['number'] ?? null;
        $runtime = $data['runtime'] ?? null;

        return new self(
            id: (int) $id,
            name: isset($data['name']) && is_string($data['name']) ? $data['name'] : null,
            season: is_numeric($season) ? (int) $season : null,
            number: is_numeric($number) ? (int) $number : null,
            type: isset($data['type']) && is_string($data['type']) ? $data['type'] : null,
            airdate: isset($data['airdate']) && is_string($data['airdate']) ? $data['airdate'] : null,
            airtime: isset($data['airtime']) && is_string($data['airtime']) ? $data['airtime'] : null,
            airstamp: isset($data['airstamp']) && is_string($data['airstamp']) ? $data['airstamp'] : null,
            runtime: is_numeric($runtime) ? (int) $runtime : null,
            rating: RatingDTO::fromArray($data['rating'] ?? null),
            summary: isset($data['summary']) && is_string($data['summary']) ? $data['summary'] : null,
        );
    }
}
