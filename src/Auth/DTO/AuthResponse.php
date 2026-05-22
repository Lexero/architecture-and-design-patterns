<?php

declare(strict_types=1);

namespace App\Auth\DTO;

use DateTimeImmutable;

final readonly class AuthResponse
{
    public function __construct(
        public string $token,
        public DateTimeImmutable $expiresAt,
        public string $gameId,
        public string $userId,
    ) {
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'expiresAt' => $this->expiresAt->format(DateTimeImmutable::ATOM),
            'gameId' => $this->gameId,
            'userId' => $this->userId,
        ];
    }
}
