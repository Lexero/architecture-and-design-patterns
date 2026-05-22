<?php

declare(strict_types=1);

namespace App\Auth\DTO;

use DateTimeImmutable;

final readonly class JwtPayload
{
    public function __construct(
        public string $userId,
        public string $gameId,
        public string $organizerId,
        public DateTimeImmutable $issuedAt,
        public DateTimeImmutable $expiresAt,
    ) {
    }
}
