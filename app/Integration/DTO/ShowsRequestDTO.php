<?php

namespace App\Integration\DTO;

/**
 * DTO para representação de Shows da API TVMaze.
 * Equivalente ao ShowsRequestDTO.java do projeto Spring Boot.
 */
class ShowsRequestDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $type = null,
        public readonly ?string $language = null,
        public readonly ?string $status = null,
        public readonly ?int $runtime = null,
        public readonly ?int $averageRuntime = null,
        public readonly ?string $officialSite = null,
        public readonly ?RatingDTO $rating = null,
        public readonly ?string $summary = null,
        /** @var EpisodeRequestDTO[] */
        public readonly array $episodes = [],
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

        $name = $data['name'] ?? null;
        if (!is_string($name) || trim($name) === '') {
            return null;
        }

        $runtime = $data['runtime'] ?? null;
        $averageRuntime = $data['averageRuntime'] ?? null;

        $episodes = [];
        if (isset($data['_embedded']['episodes']) && is_array($data['_embedded']['episodes'])) {
            foreach ($data['_embedded']['episodes'] as $ep) {
                $episodeDto = EpisodeRequestDTO::fromArray(is_array($ep) ? $ep : null);
                if ($episodeDto !== null) {
                    $episodes[] = $episodeDto;
                }
            }
        }

        return new self(
            id: (int) $id,
            name: $name,
            type: isset($data['type']) && is_string($data['type']) ? $data['type'] : null,
            language: isset($data['language']) && is_string($data['language']) ? $data['language'] : null,
            status: isset($data['status']) && is_string($data['status']) ? $data['status'] : null,
            runtime: is_numeric($runtime) ? (int) $runtime : null,
            averageRuntime: is_numeric($averageRuntime) ? (int) $averageRuntime : null,
            officialSite: isset($data['officialSite']) && is_string($data['officialSite']) ? $data['officialSite'] : null,
            rating: RatingDTO::fromArray($data['rating'] ?? null),
            summary: isset($data['summary']) && is_string($data['summary']) ? $data['summary'] : null,
            episodes: $episodes,
        );
    }
}
