<?php

declare(strict_types=1);

namespace App\Game\Model;

use DateTimeImmutable;

final readonly class GameRecord
{
    public function __construct(
        public string $gameId,
        public string $organizerId,
        public array $participants,
        public DateTimeImmutable $createdAt,
    ) {
    }

    public function isParticipant(string $userId): bool
    {
        return in_array($userId, $this->participants, true);
    }
}
