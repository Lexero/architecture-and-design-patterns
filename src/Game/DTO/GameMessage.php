<?php

declare(strict_types=1);

namespace App\Game\DTO;

final readonly class GameMessage
{
    public function __construct(
        public string $gameId,
        public string $objectId,
        public string $operationId,
        public array $args = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['gameId'],
            $data['objectId'],
            $data['operationId'],
            $data['args'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'gameId' => $this->gameId,
            'objectId' => $this->objectId,
            'operationId' => $this->operationId,
            'args' => $this->args,
        ];
    }
}
