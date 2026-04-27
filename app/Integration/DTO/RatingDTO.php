<?php

namespace App\Integration\DTO;

/**
 * DTO para representação de Rating da API TVMaze.
 * Equivalente ao RatingDTO.java do projeto Spring Boot.
 */
class RatingDTO
{
    public function __construct(
        public readonly ?float $average = null,
    ) {}

    public static function fromArray(?array $data): self
    {
        $average = $data['average'] ?? null;

        if ($average !== null && !is_numeric($average)) {
            $average = null;
        }

        return new self(
            average: $average !== null ? (float) $average : null,
        );
    }
}
