<?php

declare(strict_types=1);

namespace App\Game\DTO;

final readonly class GameState
{
    public function __construct(
        public string $gameId,
        public array $objects,
        public int $timestamp,
    ) {
    }

    public function toArray(): array
    {
        return [
            'gameId' => $this->gameId,
            'objects' => $this->objects,
            'timestamp' => $this->timestamp,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
