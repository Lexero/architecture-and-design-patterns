<?php

declare(strict_types=1);

namespace App\Game\DTO;

use DateTimeImmutable;

final readonly class CreateGameResponse
{
    public function __construct(
        public string $gameId,
        public DateTimeImmutable $createdAt,
        public array $participants,
    ) {
    }

    public function toArray(): array
    {
        return [
            'gameId' => $this->gameId,
            'createdAt' => $this->createdAt->format(DateTimeImmutable::ATOM),
            'participants' => $this->participants,
        ];
    }
}
