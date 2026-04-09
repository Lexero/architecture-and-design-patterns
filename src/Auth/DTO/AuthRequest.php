<?php

declare(strict_types=1);

namespace App\Auth\DTO;

final readonly class AuthRequest
{
    public function __construct(
        public string $gameId,
        public string $userId,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['gameId'],
            $data['userId'],
        );
    }
}
